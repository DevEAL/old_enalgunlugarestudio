<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/ 

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
 
class baformsModelForm extends JModelAdmin
{
    private $letter;
    private $condition = array();
    private $condMap = array();
    private $mailchimpMap = array();
    private $emailStyle;

    public function mollie()
    {
        $data = $_POST;
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $id = $_POST['form_id'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('mollie_api_key, return_url')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $mollie = $db->loadObject();
        $str = '';
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            if (!empty($sett[0])) {
                                $label = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label = $sett[1];
                            } else {
                                $label = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $label .= ' - ' .$value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $label = $row->str;
                                        break;
                                    }
                                }
                            }
                            $str .= $label. '; ';
                        }
                    }
                }                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $el = $value->str;
                                    break;
                                }
                            }
                        }
                        $str .= $el. '; ';                        
                    }
                } else {
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $element = $value->str;
                                break;
                            }
                        }
                    }
                    $str .= $element. '; ';
                }
                
            }
        }        
        $order_id = time();
        $array = array(
            "amount"       => $_POST['ba_total'],
            "description"  => $str,
            "redirectUrl"  => $mollie->return_url,            
            "metadata"     => array(
                "order_id" => $order_id,
            ),
        );
        $ch = curl_init();
        $url = 'https://api.mollie.nl/v1/payments';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $version = array();
        $curl_version = curl_version();
        $version[] = "Mollie/1.5.1";
        $version[] =  "PHP/" . phpversion();
        $version[] =  "cURL/" . $curl_version["version"];
        $version[] =  $curl_version["ssl_version"];
        $user_agent = join(' ', $version);
        $request_headers = array(
            "Accept: application/json",
            "Authorization: Bearer ".$mollie->mollie_api_key,
            "User-Agent: {$user_agent}",
            "X-Mollie-Client-Info: " . php_uname(),
        );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        $request_headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        $body = curl_exec($ch);
        if (strpos(curl_error($ch), "certificate subject name 'mollie.nl' does not match target host") !== FALSE)
        {
            $request_headers[] = "X-Mollie-Debug: old OpenSSL found";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $body = curl_exec($ch);
        }
        if (curl_errno($ch))
        {
            $message = "Unable to communicate with Mollie (".curl_errno($ch)."): " . curl_error($ch) . ".";
            curl_close($ch);
            echo '<input type="hidden" id="mollie-data" value="'.$message.'">';
        } else {
            curl_close($ch);
            $link = json_decode($body);
            if (isset($link->error)) {
                echo '<input type="hidden" id="mollie-data" value="'.$link->error->message.'">';
            } else {
                echo '<input type="hidden" id="mollie-data" value="'.$link->links->paymentUrl.'">';
                $this->save($_POST);
            }
        }
?>
            <script language="JavaScript">
                
                var intervalId = setInterval(sec,12);
                function sec()
                {
                    var msg = document.getElementById("mollie-data").value;
                    if (msg) {
                        clearInterval(intervalId);
                        window.parent.postMessage(msg, "*");
                    }
                    
                }
            </script>

<?php
        exit;
    }

    public function payu()
    {
        $data = $_POST;
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $id = $_POST['form_id'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('currency_code, payu_api_key, payu_merchant_id, return_url,
                        payu_account_id, payment_environment')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $payu = $db->loadObject();
        if ($payu->payment_environment == 'sandbox') {
            $url = 'https://stg.gateway.payulatam.com/ppp-web-gateway';
        } else {
            $url = 'https://gateway.payulatam.com/ppp-web-gateway/';
        }
        $str = '';
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            if (!empty($sett[0])) {
                                $label = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label = $sett[1];
                            } else {
                                $label = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $label .= ' - ' .$value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $label = $row->str;
                                        break;
                                    }
                                }
                            }
                            $str .= $label. '; ';
                        }
                    }
                    
                }
                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $el = $value->str;
                                    break;
                                }
                            }
                        }
                        $str .= $el. '; ';                        
                    }
                } else {
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $element = $value->str;
                                break;
                            }
                        }
                    }
                    $str .= $element. '; ';
                }
                
            }
        }
        $query = $db->getQuery(true);
        $query->select('id, settings')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $key => $item) {
            $settings = explode('_-_', $item->settings);
            if ($settings[2] == 'email') {
                $email = $_POST[$item->id];
                break;
            }
        }
        $column = array('value');
        $value = 'value';
        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__baforms_reference'))
            ->columns($db->quoteName($column))
            ->values($value);
        $db->setQuery($query)
            ->execute();
        $ref = JUri::root().$db->insertid();
        $sig = $payu->payu_api_key. "~".$payu->payu_merchant_id."~";
        $sig .= $ref."~".$_POST['ba_total']."~".$payu->currency_code;
        $signature = md5($sig);
        $this->save($_POST);
        ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post">
            <input name="merchantId" type="hidden" value="<?php echo $payu->payu_merchant_id; ?>">
            <input name="accountId" type="hidden" value="<?php echo $payu->payu_account_id; ?>">
            <input name="description" type="hidden" value="<?php echo $str; ?>">
            <input name="referenceCode" type="hidden" value="<?php echo $ref; ?>">
            <input name="amount" type="hidden" value="<?php echo $_POST['ba_total']; ?>">
            <input name="tax" type="hidden" value="0">
            <input name="taxReturnBase" type="hidden" value="0">
            <input name="currency" type="hidden" value="<?php echo $payu->currency_code; ?>">
            <input name="signature" type="hidden" value="<?php echo $signature ?>">
            <?php if (isset($email)) { ?>
                <input name="buyerEmail" type="hidden" value="<?php echo $email; ?>">
            <?php } ?>
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php 
        exit;
    }


    public function webmoney()
    {
        $data = $_POST;
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $id = $_POST['form_id'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('webmoney_purse')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $webmoney = $db->loadResult();
        $url = 'https://merchant.webmoney.ru/lmi/payment.asp';
        $str = '';
        $this->save($_POST);
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            if (!empty($sett[0])) {
                                $label = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label = $sett[1];
                            } else {
                                $label = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $label .= ' - ' .$value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $label = $row->str;
                                        break;
                                    }
                                }
                            }
                            $str .= $label. '; ';
                        }
                    }
                    
                }
                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $el = $value->str;
                                    break;
                                }
                            }
                        }
                        $str .= $el. '; ';                        
                    }
                } else {
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $element = $value->str;
                                break;
                            }
                        }
                    }
                    $str .= $element. '; ';
                }
                
            }
        }
        ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post" accept-charset="windows-1251">
            <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?php echo $_POST['ba_total']; ?>">
            <input type="hidden" name="LMI_PAYMENT_DESC" value="<?php echo $str; ?>">
            <input type="hidden" name="LMI_PAYEE_PURSE" value="<?php echo $webmoney; ?>">
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php 
        exit;
    }
    
    public function skrill()
    {
        $data = $_POST;
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $id = $_POST['form_id'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('return_url, cancel_url, currency_symbol, currency_code, skrill_email')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $skrill = $db->loadObject();
        $url = 'https://www.moneybookers.com/app/payment.pl';
        $this->save($_POST);
        $i = 1; ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post">
            <input type="hidden" name="pay_to_email" value="<?php echo $skrill->skrill_email; ?>">
            <input type="hidden" name="return_url" value="<?php echo $skrill->return_url; ?>">
            <input type="hidden" name="cancel_url" value="<?php echo $skrill->cancel_url; ?>">
            <input type="hidden" name="currency" value="<?php echo $skrill->currency_code; ?>">
            <input type="hidden" name="language" value="EN">
            <input type="hidden" name="amount" value="<?php echo $_POST['ba_total']; ?>">
        <?php
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            $label = array();
                            if (!empty($sett[0])) {
                                $label[0] = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label[0] = $sett[1];
                            } else {
                                $label[0] = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $price = $label[1] = $value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $label = explode(' - ', $row->str);
                                        break;
                                    }
                                }
                            }
                            ?>
            <input type="hidden" name="detail<?php echo $i; ?>_description" value="<?php echo $label[0]; ?>">
            <input type="hidden" name="detail<?php echo $i; ?>_text" value="<?php echo $label[1]; ?>">
            <input type="hidden" name="amount<?php echo $i; ?>" value="<?php echo $price; ?>">
                        <?php
                            $i++;
                        }
                    }
                    
                }
                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $el = $value->str;
                                    break;
                                }
                            }
                        }
                        $label = explode(' - ', $el);
                        $price = str_replace($skrill->currency_symbol, '', $label[1]);
                        
                        ?>
            <input type="hidden" name="detail<?php echo $i; ?>_description" value="<?php echo $label[0]; ?>">
            <input type="hidden" name="detail<?php echo $i; ?>_text" value="<?php echo $label[1]; ?>">
            <input type="hidden" name="amount<?php echo $i; ?>" value="<?php echo $price; ?>">
                        <?php
                        $i++;
                    }
                } else {
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $element = $value->str;
                                $label = explode(' - ', $element);
                                break;
                            }
                        }
                    }
                    $price = str_replace($skrill->currency_symbol, '', $label[1]);
                    ?>
            <input type="hidden" name="detail<?php echo $i; ?>_description" value="<?php echo $label[0]; ?>">
            <input type="hidden" name="detail<?php echo $i; ?>_text" value="<?php echo $label[1]; ?>">
            <input type="hidden" name="amount<?php echo $i; ?>" value="<?php echo $price; ?>">
                    <?php
                    $i++;
                }
                
            }
        }
        ?>
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php 
        exit;
    }
    
    public function twoCheckout()
    {
        $data = $_POST;
        $id = $_POST['form_id'];
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('return_url, currency_symbol, seller_id, payment_environment')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $checkout = $db->loadObject();
        if ($checkout->payment_environment == 'sandbox') {
            $url = 'https://sandbox.2checkout.com/checkout/purchase';
        } else {
            $url = 'https://www.2checkout.com/checkout/purchase';
        }
        $this->save($_POST);
        $i = 0; ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post">
            <input type="hidden" name="sid" value="<?php echo $checkout->seller_id; ?>">
            <input type="hidden" name="mode" value="2CO">
            <input type="hidden" name="pay_method" value="PPI">
            <input type="hidden" name="x_receipt_link_url" value="<?php echo $checkout->return_url; ?>">
        <?php
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            $quantity = 1;
                            if (!empty($sett[0])) {
                                $label = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label = $sett[1];
                            } else {
                                $label = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $price = $value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $quantity = $row->quantity;
                                        break;
                                    }
                                }
                            }
                            ?>
            <input type="hidden" name="li_<?php echo $i; ?>_name" value="<?php echo $label; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_price" value="<?php echo $price; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_quantity" value="<?php echo $quantity; ?>">
                        <?php
                            $i++;
                        }
                    }
                    
                }
                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        $label = explode(' - ', $el);
                        $price = str_replace($checkout->currency_symbol, '', $label[1]);
                        $quantity = 1;
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $quantity = $value->quantity;
                                    break;
                                }
                            }
                        }
                        ?>
            <input type="hidden" name="li_<?php echo $i; ?>_name" value="<?php echo $label[0]; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_price" value="<?php echo $price; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_quantity" value="<?php echo $quantity; ?>">
                        <?php
                        $i++;
                    }
                } else {
                    $price = str_replace($checkout->currency_symbol, '', $label[1]);
                    $quantity = 1;
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $quantity = $value->quantity;
                                break;
                            }
                        }
                    }
                    ?>
            <input type="hidden" name="li_<?php echo $i; ?>_name" value="<?php echo $label[0]; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_price" value="<?php echo $price; ?>">
            <input type="hidden" name="li_<?php echo $i; ?>_quantity" value="<?php echo $quantity; ?>">
                    <?php
                    $i++;
                }
                
            }
        }
        ?>
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php 
        exit;
    }
    
    public function paypal()
    {
        $data = $_POST;
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
            unset($data['baforms_cart']);
        } else {
            $cart = array();
        }
        $id = $_POST['form_id'];
        $total = $_POST['ba_total'];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $query = $db->getQuery(true);
        $query->select('paypal_email, payment_environment, return_url,
                        cancel_url, currency_symbol, currency_code')
            ->from('#__baforms_forms')
            ->where('id='. $id);
        $db->setQuery($query);
        $paypal = $db->loadObject();
        if ($paypal->payment_environment == 'sandbox') {
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $url = 'https://www.paypal.com/cgi-bin/webscr';
        }
        $i = 1;
        $array = array();
        foreach ($data as $key => $value) {
            foreach ($items as $item) {
                if ($key == $item->id) {
                    $settings = $item->settings;
                    $settings = explode('_-_', $settings);
                    if ($settings[2] == 'textInput') {
                        $sett = explode(';', $settings[3]);
                        if ($sett[4] == 'calculation') {
                            if (!empty($sett[0])) {
                                $label = $sett[0];
                            } else if (!empty($sett[1])) {
                                $label = $sett[1];
                            } else {
                                $label = '';
                            }
                            if (empty($value)) {
                                break;
                            }
                            $price = $value;
                            foreach ($cart as $row) {
                                if (!empty($row)) {
                                    $row = json_decode($row);
                                    if ($key == $row->id) {
                                        $price = $price * $row->quantity;
                                        break;
                                    }
                                }
                            }
                            $array['amount_'.$i] = $price;
                            $array['item_name_'.$i] = $label;
                            $i++;
                        }
                    }
                    
                }
                
            }
        }
        foreach ($data as $key => $element) {
            if (is_array($element)) {
                foreach ($element as $el) {
                    $label = explode(' - ', $el);
                }
            } else {
                $label = explode(' - ', $element);
            }
            if (isset($label[1])) {
                if (is_array($element)) {
                    foreach ($element as $el) {
                        $label = explode(' - ', $el);
                        $price = str_replace($paypal->currency_symbol, '', $label[1]);
                        foreach ($cart as $value) {
                            if (!empty($value)) {
                                $value = json_decode($value);
                                if ($key == $value->id && $el == $value->product) {
                                    $price = $price * $value->quantity;
                                    break;
                                }
                            }
                        }
                        $array['amount_'.$i] = $price;
                        $array['item_name_'.$i] = $label[0];
                        $i++;
                    }
                } else {
                    $price = str_replace($paypal->currency_symbol, '', $label[1]);
                    foreach ($cart as $value) {
                        if (!empty($value)) {
                            $value = json_decode($value);
                            if ($key == $value->id) {
                                $price = $price * $value->quantity;
                                break;
                            }
                        }
                    }
                    $array['amount_'.$i] = $price;
                    $array['item_name_'.$i] = $label[0];
                    $i++;
                }
            }
        }
        $this->save($_POST);

        ?>
        <form id="payment-form" action="<?php echo $url; ?>" method="post">
            <input type="hidden" name="cmd" value="_ext-enter">
            <input type="hidden" name="redirect_cmd" value="_cart">
            <input type="hidden" name="upload" value="1">
            <input type="hidden" name="business" value="<?php echo $paypal->paypal_email; ?>">
            <input type="hidden" name="receiver_email" value="<?php echo $paypal->paypal_email; ?>">
            <input type="hidden" name="currency_code" value="<?php echo $paypal->currency_code; ?>">
            <input type="hidden" name="return" value="<?php echo $paypal->return_url; ?>">
            <input type="hidden" name="cancel_return" value="<?php echo $paypal->cancel_url; ?>">
            <input type="hidden" name="rm" value="2">
            <input type="hidden" name="shipping" value="0">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="charset" value="utf-8">
        <?php foreach ($array as $key => $value) { ?>
            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
        <?php } ?>    
        </form>
        <script type="text/javascript">
            document.getElementById('payment-form').submit();
        </script>
        <?php
        exit;
    }
    
    public function getForm($data = array(), $loadData = true)
    {
        
    }
    
    public function saveUpload($fileName, $maxSize, $types, $id, $i)
    {
        $types = explode(',', $types);
        $maxSize = 1048576 * $maxSize;
        $dir = JPATH_BASE . '/images/baforms';
        $badExt = array('php', 'phps', 'php3', 'php4', 'phtml', 'pl',
                        'py', 'jsp', 'asp', 'htm', 'shtml', 'sh',
                        'cgi', 'htaccess', 'exe', 'dll');
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir);
        }
        $dir = $dir.'/form_'.$id;
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir);
        }
        $type = explode('.', $_FILES[$fileName]['name'][$i]);
        $type = end($type);
        $type = trim(strtolower($type));
        if (!in_array($type, $badExt)) {
            foreach ($types as $allow) {
                if (trim($allow) == $type) {
                    if($_FILES[$fileName]['size'][$i] < $maxSize) {
                        $newFile = rand(666666, 666666666666). '_' .$_FILES[$fileName]['name'][$i];
                        $file = $dir ."/".$newFile;
                        if (move_uploaded_file($_FILES[$fileName]['tmp_name'][$i], $file)) {
                            return $newFile;
                        }
                    }
                    break;
                }
            }
        } else {
            return false;
        }
    }

    public function getLetter($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('email_letter')
            ->from('#__baforms_forms')
            ->where('`id` = ' .$id);
        $db->setQuery($query);
        $letter = $db->loadResult();
        $query = $db->getQuery(true);
        $query->select('id, settings')
            ->from('#__baforms_items')
            ->where('`form_id` = ' .$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $str = '';
        if (empty($letter)) {
            $letter = '<table style="width: 100%; background-color: #ffffff;';
            $letter .= ' border: 1px solid #f3f3f3; margin: 0 auto;"><tbody><tr class="ba-section"';
            $letter .= '><td style="width:100%; padding: 0 20px;"><table style="width: 100%; background-color: rgba(0,0,0,0);';
            $letter .= ' color: #333333; border-top: 1px solid #f3f3f3; border-right:';
            $letter .= ' 1px solid #f3f3f3; border-bottom: 1px solid #f3f3f3; border-';
            $letter .= 'left: 1px solid #f3f3f3; margin-top: 10px; ';
            $letter .= 'margin-bottom: 10px;"><tbody><tr><td class="droppad_area" ';
            $letter .= 'style="width:100%; padding: 20px;">[replace_all_items]</td>';
            $letter .= '</tr></tbody></table></td></tr></tbody></table>';
            foreach ($items as $item) {
                $settings = explode('_-_', $item->settings);
                if ($settings[0] != 'button') {
                    $type = $settings[2];
                    if ($type != 'image' && $type != 'htmltext' && $type != 'map' &&
                        strpos($settings[0], 'baform') === false) {
                        $settings = explode(';', $settings[3]);
                        $name = $this->checkItems($settings[0], $type, $settings[2]);
                        $str .= '<div class="droppad_item system-item" data-item="[item=';
                        $str .= $item->id. ']">[Field='.$name.']</div>';
                    }
                }
            }
        }
        $letter = str_replace('[replace_all_items]', $str, $letter);
        
        $this->letter = $letter;
    }

    public function changeLetter($name, $data, $item)
    {
        $body = $this->letter;
        $pos = strpos($body, 'item='.$item->id. ']');
        $pos = strpos($body, '>', $pos);
        $pos2 = strpos($body, '</div>', $pos);
        $start = substr($body, 0, $pos);
        $end = substr($body, $pos2);
        $str = '<b>'.$name.'</b>: '. nl2br($data);
        $this->letter = $start.'>'.$str.$end;
    }

    public function addLetterTotal($name, $total, $cart)
    {
        $letter = $this->letter;
        $language = JFactory::getLanguage();
        $language->load('com_baforms', JPATH_ADMINISTRATOR);
        $str = '<b>' .$name.'</b>: '.$total;
        $letter = str_replace("[Field=Total]", $str, $letter);
        if (!empty($cart)) {
            $str = '<table style="width: 100%; background-color: rgba(0,0,0,0);';
            $str .= ' margin-top:10px; margin-bottom : 10px;';
            $str .= ' border-top: 1px solid #f3f3f3; border-left: 1px solid';
            $str .= ' #f3f3f3; border-right: 1px solid #f3f3f3; border-';
            $str .= 'bottom: 1px solid #f3f3f3; color:inherit;"><tbody style="';
            $str .= 'color:inherit;"><tr style="color:inherit;';
            $str .= '"><td style="padding: 20px; color:inherit;"><b>'.$language->_("ITEM");
            $str .= '</b></td><td style="padding: 20px; color:inherit;"><b>'.$language->_("PRICE");
            $str .= '</b></td><td style="padding: 20px; color:inherit;"><b>'.$language->_("QUANTITY");
            $str .= '</b></td><td style="padding: 20px; color:inherit;"><b>'.$language->_("TOTAL");
            $str .= '</b></td></tr>';
            foreach ($cart as $value) {
                if (!empty($value)) {
                    $value = json_decode($value);
                    $product = explode(' - ', $value->product);
                    $price = explode(' - ', $value->str);
                    
                    $str .= '<tr><td style="padding: 20px; color:inherit;">'.$product[0];
                    $str .= '</td><td style="padding: 20px; color:inherit;">'.$product[1];
                    $str .= '</td><td style="padding: 20px; color:inherit;">';
                    $str .= $value->quantity.'</td><td style="padding: 20px; color:inherit;">';
                    $str .= $price[1].'</td></tr>';
                }
            }
            $str .= '</tbody></table>';
            $letter = str_replace("[Field=Cart]", $str, $letter);
        }
        $this->letter = $letter;
    }

    public function restoreData()
    {
        $array = array();
        foreach ($this->condition as $key => $condition) {
            $settings = explode('_-_', $condition->item->settings);
            if (strpos($settings[0], 'baform') === false) {
                $array[$key][] = $condition;
            } else {
                $array[$this->condMap[$settings[0]]][] = $condition;
            }
        }
        foreach ($array as $condition) {
            $str = '';
            foreach ($condition as $value) {
                $str .= '<b>'.$value->name.'</b>: '. nl2br($value->str).'<br>';
            }
            $pos = strpos($this->letter, 'item='.$condition[0]->item->id. ']');
            $pos = strpos($this->letter, '>', $pos);
            $pos2 = strpos($this->letter, '</div>', $pos);
            $start = substr($this->letter, 0, $pos);
            $end = substr($this->letter, $pos2);            
            $this->letter = $start.'>'.$str.$end;
        }
    }

    public function addMailchimpSubscribe($form)
    {
        if (!empty($form['mailchimp_api_key']) && !empty($form['mailchimp_list_id'])) {
            $apiKey = $form['mailchimp_api_key'];
            $listId = $form['mailchimp_list_id'];
            $email_address = $_POST[$this->mailchimpMap['EMAIL']];
            $memberId = md5(strtolower($email_address));
            $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;
            $merge_fields = array();
            foreach ($this->mailchimpMap as $key => $value) {
                if ($key != 'EMAIL' && isset($_POST[$this->mailchimpMap[$key]])) {
                    $merge_fields[$key] = $_POST[$this->mailchimpMap[$key]];
                }
            }
            $array = array(
                'email_address' => $email_address,
                'status' => 'subscribed',
                'merge_fields' => $merge_fields
            );
            if (empty($merge_fields)) {
            	unset($array['merge_fields']);
            }
            $json = json_encode($array);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_exec($ch);
            curl_close($ch);
        }
    }
    
    public function sendEmail($title, $msg, $id, $email)
    {
        $this->restoreData();
        $options = $this->getEmailOptions($id);
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array($config->get('mailfrom'), $config->get('fromname') );
        $recipient = $options[0]->email_recipient;
        $recipient = explode(',', $recipient);
        $msg = explode('_-_', $msg);
        $files = array();
        foreach ($msg as $mess) {
            if ($mess != '') {
                $mess = explode('|-_-|', $mess);
                if ($mess[2] == 'upload' && $mess[1] != '') {
                    array_push($files, JPATH_ROOT . '/images/baforms/' .$mess[1]);
                }
            }
        }
        foreach ($recipient as $key => $recip) {
            $recipient[$key] = str_replace('...', '', $recip);
            if ($recip == '') {
                unset($recipient[$key]);
            }
        }
        $regex = '/\[field ID=+(.*?)\]/i';
        preg_match_all($regex, $options[0]->reply_body, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                if (isset($_POST[$match[1]])) {
                    $options[0]->reply_body = str_replace('[field ID='.$match[1].']', $_POST[$match[1]], $options[0]->reply_body);
                } else {
                    $options[0]->reply_body = str_replace('[field ID='.$match[1].']', '', $options[0]->reply_body);
                }
            }
        }
        if (!empty($recipient)) {
            $subject = $options[0]->email_subject;
            if (!empty($files)) {
                $mailer->addAttachment($files);
            }
            if ($options[0]->add_sender_email*1 === 1 && !empty($email)) {
                $mailer->addReplyTo($email);
            }
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $body = $this->letter;
            $mailer->setSender($sender);
            $mailer->setSubject($subject);
            $mailer->addRecipient($recipient);
            $mailer->setBody($body);
            $mailer->Send();
        }

        if (!empty($options[0]->sender_email) && !empty($email)) {
            $mailer = JFactory::getMailer();
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $sender = array($options[0]->sender_email, $options[0]->sender_name);
            $mailer->setSender($sender);
            $subject = $options[0]->reply_subject;
            $mailer->setSubject($subject);
            $mailer->addRecipient($email);
            $body = $options[0]->reply_body;
            if ($options[0]->copy_submitted_data*1 === 1) {
                $body .= '<br>' .$this->letter;
                if (!empty($files)) {
                    $mailer->addAttachment($files);
                }
            }
            $mailer->setBody($body);
            $mailer->Send();
        }
    }
    
    public function getEmailOptions($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('email_recipient, email_subject, email_body, sender_name,
                        sender_email, reply_subject, reply_body, add_sender_email,
                        copy_submitted_data');
        $query->from('#__baforms_forms');
        $query->where('id='.$id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }
    
    public function checkItems($item, $type, $place)
    {
        if ($item != '') {
            return $item;
        } else {
            if ($type == 'textarea') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Textarea';
                }
            }
            if ($type == 'textInput') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'TextInput';
                }
            }
            if ($type == 'chekInline') {
                return 'ChekInline';
            }
            if ($type == 'checkMultiple') {
                return 'CheckMultiple';
            }
            if ($type == 'radioInline') {
                return 'RadioInline';
            }
            if ($type == 'radioMultiple') {
                return 'RadioMultiple';
            }
            if ($type == 'dropdown') {
                return 'Dropdown';
            }
            if ($type == 'selectMultiple') {
                return 'SelectMultiple';
            }
            if ($type == 'date') {
                return 'Date';
            }
            if ($type == 'slider') {
                return 'Slider';
            }
            if ($type == 'email') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Email';
                }
            }
            if ($type == 'address') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Address';
                }
            }
        }
    }

    public function conditionParent($item, $items, $data, $form)
    {
        $settings = explode('_-_', $item->settings);
        $symbol = $form['currency_symbol'];
        $position = $form['currency_position'];
        if (strpos($settings[0], 'baform') !== false) {
            foreach ($items as $value) {
                $sett = explode('_-_', $value->settings);                
                if ($settings[0] == $sett[1]) {
                    if ($this->conditionParent($value, $items, $data, $form)) {
                        $val = explode(';', $sett[3]);
                        $val = explode('\n', $val[2]);
                        foreach ($val as $ind => $v) {
                            if (isset($data['ba_total'])) {
                                $v = explode('====', $v);
                                if (isset($v[1])) {
                                    if ($position == 'before') {
                                        $val[$ind] = $v[0].' - '.$symbol.$v[1];
                                    } else {
                                        $val[$ind] = $v[0].' - '.$v[1].$symbol;
                                    }
                                }
                            } else {
                                $val[$ind] = str_replace('====', '', $val[$ind]);
                                $val[$ind] = trim($val[$ind]);
                            }
                        }
                        if (isset($data[$value->id]) && $data[$value->id] == str_replace('"', '', $val[$settings[4]])) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }                    
                    break;
                }
            }
        }
        return true;        
    }

    public function checkCondition($item, $items, $name, $str, $form)
    {
        $settings = explode('_-_', $item->settings);
        if (isset($settings[5]) && strlen($settings[5]) > 0) {
            if ($this->conditionParent($item, $items, $_POST, $form)) {
                $obj = new stdClass();
                $obj->name = $name;
                $obj->item = $item;
                $obj->str = $str;
                $this->condition[$item->id] = $obj;
            }
            return false;
        } else if (strpos($settings[0], 'baform') !== false) {
            if ($this->conditionParent($item, $items, $_POST, $form)) {
                $obj = new stdClass();
                $obj->name = $name;
                $obj->item = $item;
                $obj->str = $str;
                $this->condition[$item->id] = $obj;
            }
            return false;   
        } else {
            return true;
        }
    }

    public function setCondMap($items, $form)
    {
        $obj = $form['mailchimp_fields_map'];
        $obj = json_decode($obj);
        foreach ($items as $item) {
            $settings = explode('_-_', $item->settings);
            if (strpos($settings[0], 'baform') !== false) {
                $key = $this->getCondParent($settings[0], $items);
                $this->condMap[$settings[0]] = $key;
            }
            foreach ($obj as $key => $value) {
                if ($settings[1] == $value) {
                    $this->mailchimpMap[$key] = $item->id;
                }
            }
        }
    }

    public function getCondParent($key, $items)
    {
        foreach ($items as $item) {
            $settings = explode('_-_', $item->settings);
            if ($key == $settings[1]) {
                if (strpos($settings[0], 'baform') === false) {
                    return $item->id;
                } else {
                    return $this->getCondParent($settings[0], $items);
                }
            }
        }
    }
    
    public function save($data)
    {
        if (isset($_POST['baforms_cart'])) {
            $cart = $_POST['baforms_cart'];
            $cart = explode(';', $cart);
        } else {
            $cart = array();
        }
        $id = $data['form_id'];
        $flag = true;
        $email = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("title, alow_captcha, sent_massage, error_massage,
                        check_ip, currency_symbol, currency_position,
                        mailchimp_api_key, mailchimp_list_id, mailchimp_fields_map");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $form = $db->loadAssoc();
        $query = $db->getQuery(true);
        $query->select("email_options");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $this->emailStyle = $db->loadResult();
        $title = $form['title'];
        $capt = $form['alow_captcha'];
        $succes = $form['sent_massage'];
        $error = $form['error_massage'];
        $submissionData = '';
        $query = $db->getQuery(true);
        $query->select('settings, id')
            ->from('#__baforms_items')
            ->where('form_id='. $id)
            ->order("column_id ASC");
        $db->setQuery($query);
        $items = $db->loadObjectList();
        $captName = array();
        if ($capt != '0') {
            $captcha = JCaptcha::getInstance($capt, array('namespace' => 'anything'));
            if (isset($data[$capt])) {
                $answer = $captcha->checkAnswer($data[$capt]);
                if ($answer) {
                    $flag = true;
                } else {
                    $flag = false;
                }
            } else {
                foreach ($data as $key=> $dat) {
                    if ($key != 'task' && $key != 'form_id') {
                        array_push($captName, $key);
                    }
                }
                foreach ($items as $key=> $item) {
                    $item = $item->id;
                    for ($i = 0; $i < count($captName); $i++) {
                        if ($item == $captName[$i]) {
                            unset($captName[$i]);
                            sort($captName);
                        }
                    }
                }
                if (isset($captName[0])) {
                    $answer = $captcha->checkAnswer($data[$captName[0]]);
                } else {
                    $answer = $captcha->checkAnswer('anything');
                }
                if ($answer) {
                    $flag = true;
                } else {
                    $flag = false;
                }
            }
        }
        $this->getLetter($id);
        if ($flag) {
            $this->setCondMap($items, $form);
            foreach ($items as $item) {
                if ($flag) {
                    $itm = explode('_-_', $item->settings);
                    if ($itm[0] != 'button') {
                        $type = trim($itm[2]);
                        $itm = explode(';', $itm[3]);
                        if ($type == 'textarea' || $type == 'textInput' || $type == 'chekInline' ||
                            $type == 'checkMultiple' || $type == 'radioInline' || $type == 'radioMultiple' ||
                            $type == 'dropdown' || $type == 'selectMultiple') {
                            $required = $itm[3];
                            $itm = trim($this->checkItems($itm[0], $type, $itm[2]));
                            $name = $itm;
                            $itm = str_replace(' ', '_', $itm);
                            if ($this->conditionParent($item, $items, $data, $form)) {
                                if ($required == 1) {
                                    if (!empty($data[$item->id])) {
                                        $flag = true;
                                    } else {
                                        $flag = false;
                                    }
                                } else {
                                    $flag = true;
                                }
                            } else {
                                $flag = true;
                            }
                            
                        } else if ($type == 'email') {
                            $itm = trim($this->checkItems($itm[0], $type, $itm[2]));
                            $name = $itm;
                            $itm = str_replace(' ', '_', $itm);
                            if ($this->conditionParent($item, $items, $data, $form)) {
                                $reg = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,6})+$/";
                                if(!empty($data[$item->id]) && preg_match($reg, $data[$item->id])) {
                                    $email = $data[$item->id];
                                    $flag = true;
                                } else {
                                    $flag = false;
                                }
                            } else {
                                $flag = true;
                            }
                            
                        } else {
                            $itm = trim($this->checkItems($itm[0], $type, ''));
                            $name = $itm;
                            $itm = str_replace(' ', '_', $itm);
                        }
                        if ($flag) {
                            foreach ($data as $key => $elem) {
                                if ($key != "form_id" && $key != "task") {
                                    if ($item->id == $key) {
                                        if (is_array($elem)) {
                                            $message = '';
                                            foreach ($elem as $ind => $element) {
                                                foreach ($cart as $value) {
                                                    if (!empty($value)) {
                                                        $value = json_decode($value);
                                                        if ($value->id == $key && $value->product == $element) {
                                                            $element = $value->str;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if ($ind == 0) {
                                                    $message .= '<br>';
                                                }
                                                $message .= strip_tags($element). ';<br>';
                                            }
                                            $submissionData .= $name. '|-_-|' .$message. '|-_-|'.$type. '_-_';
                                            if ($this->checkCondition($item, $items, $name, $message, $form)) {
                                                $this->changeLetter($name, $message, $item);
                                            }
                                        } else {
                                            foreach ($cart as $value) {
                                                if (!empty($value)) {
                                                    $value = json_decode($value);
                                                    if ($value->id == $key) {
                                                        $elem = $value->str;
                                                        break;
                                                    }
                                                }
                                            }
                                            $submissionData .= $name. '|-_-|' .strip_tags($elem). '|-_-|'.$type. '_-_';
                                            if ($this->checkCondition($item, $items, $name, strip_tags($elem), $form)) {
                                                $this->changeLetter($name, strip_tags($elem), $item);
                                            }                                            
                                        }
                                    }
                                }
                            }
                            if (!array_key_exists($item->id, $data) && $type != 'image' &&
                                $type != 'map' && $type != 'htmltext' && $type != 'upload') {
                                $submissionData .= $name. '|-_-||-_-|'.$type. '_-_';
                                if ($this->checkCondition($item, $items, $name, '', $form)) {
                                    $this->changeLetter($name, '', $item);
                                }
                            }
                        }
                    }
                }
            }
            if (isset($data['ba_total'])) {
                $total = $data['ba_total'];
                if ($form['currency_position'] == 'before') {
                    $total = $form['currency_symbol'].$total;
                } else {
                    $total .= $form['currency_symbol'];
                }
                $submissionData .= 'Total|-_-|' .$total. '|-_-|total_-_';
                $this->addLetterTotal('Total', $total, $cart);
            }
            if ($flag) {
                if (!empty($_FILES)) {
                    foreach ($_FILES as $key => $file) {
                        if ($file['error'][0] === 0 && $flag) {
                            foreach ($items as $item) {
                                if ($key == $item->id) {
                                    $options = $item->settings;
                                    $options = explode('_-_', $options);
                                    $type = trim($options[2]);
                                    $options = explode(';', $options[3]);
                                    $length = count($file['error']);
                                    for ($i = 0; $i < $length; $i++) {
                                        $link = $this->saveUpload($key, $options[2], $options[3], $id, $i);
                                        if ($link) {
                                            $key = str_replace('_', ' ', $key);
                                            $submissionData .= $options[0]. '|-_-|' .'form_'.$id.'/'.$link. '|-_-|' .$type. '_-_';
                                            if ($this->checkCondition($item, $items, $options[0], '', $form)) {
                                                $this->changeLetter($options[0], '', $item);
                                            }
                                        } else {
                                            $flag = false;
                                        }
                                    }
                                    break;
                                }
                            }
                        } else if ($file['error'][0] === 4) {
                            foreach ($items as $item) {
                                if ($key == $item->id) {
                                    $options = $item->settings;
                                    $options = explode('_-_', $options);
                                    $type = trim($options[2]);
                                    $options = explode(';', $options[3]);
                                    $submissionData .= $options[0]. '|-_-||-_-|' .$type. '_-_';
                                    if ($this->checkCondition($item, $items, $options[0], '', $form)) {
                                        $this->changeLetter($options[0], '', $item);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if ($form['check_ip']) {
                $submissionData .= 'Ip Address|-_-|'.$_SERVER['REMOTE_ADDR'].'|-_-|textInput';
                $str = '<b>Ip Address</b> : '.$_SERVER['REMOTE_ADDR'];
                $this->letter = @preg_replace("|\[Field=Ip Address\]|", $str, $this->letter, 1);
            }
            if ($flag) {
                $columns = array('title, mesage, date_time');
                $date = date('Y-m-d');
                $values = array($db->quote($title), $db->quote($submissionData), $db->quote($date));
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->insert('#__baforms_submissions');
                $query->columns($columns);
                $query->values(implode(',', $values));
                $db->setQuery($query);
                $db->execute();
                $this->addMailchimpSubscribe($form);
                $this->sendEmail($title, $submissionData, $id, $email);
                echo '<input id="form-sys-mesage" type="hidden" value="' .htmlspecialchars($succes, ENT_QUOTES). '">';
            } else {
                echo '<input id="form-sys-mesage" type="hidden" value="' .htmlspecialchars($error, ENT_QUOTES). '">';
            }
        } else {
            echo '<input id="form-sys-mesage" type="hidden" value="' .htmlspecialchars($error, ENT_QUOTES). '">';
        }
        if ($data['task'] == 'form.save') {
?>
            <script language="JavaScript">
                
                var intervalId = setInterval(sec,12);
                function sec()
                {
                    var msg = document.getElementById("form-sys-mesage").value;
                    if (msg) {
                        clearInterval(intervalId);
                        window.parent.postMessage(msg, "*");
                    }
                    
                }
            </script>

<?php
            exit;
        }
    }
    
}