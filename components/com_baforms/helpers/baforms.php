<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

abstract class baformsHelper 
{
    public static function addStyle()
    {
        $document = JFactory::getDocument();
        $regex = '/\[forms ID=+(.*?)\]/i';
        $buffer = $document->getBuffer();
        foreach ($buffer as $buff) {
            foreach ($buff as $pos) {
                foreach ($pos as $items) {
                    preg_match_all($regex, $items, $matches, PREG_SET_ORDER);
                    if ($matches) {
                        foreach ($matches as $index => $match) {
                            $id = $match[1];
                            if (self::checkForm($id)) {
                                self::addScripts($id);
                            }
                        }
                    }
                }
            }
        }
        $app = JFactory::getApplication();
        if ($app->getTemplate() == 'g5_hydrogen') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select("alow_captcha");
            $query->from("#__baforms_forms");
            $query->where("published = 1");
            $query->order("id ASC");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $item) {
                if ($item->alow_captcha != '0') {
                    $captch = JCaptcha::getInstance($item->alow_captcha);
                    $captch->initialise($item->alow_captcha);
                }
            }
        }
    }

    public static function getMapsKey()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`key`')
            ->from('`#__baforms_api`')
            ->where('`service` = '.$db->quote('google_maps'));
        $db->setQuery($query);
        $key = $db->loadResult();
        return $key;
    }
    
    public static function addScripts($id)
    {
        $document = JFactory::getDocument();
        $scripts = $document->_scripts;
        $doc = array();
        $map = true;
        $payments = baformsHelper::getPayment($id);
        $submissionsOptions = baformsHelper::getSubmisionOptions($id);
        $method = $submissionsOptions['payment_methods'];
        foreach ($scripts as $key=>$script) {
            if (strpos($key, 'maps.googleapis.com/maps/api/js')) {
                $map = false;
            }
            $key = explode('/', $key);
            $doc[] = end($key);
        }
        if (!in_array('jquery.min.js', $doc) && !in_array('jquery.js', $doc)) {
            $document->addScript(JUri::root(true) . '/media/jui/js/jquery.min.js');
        }
        $document->addStyleSheet(JURI::root() . 'components/com_baforms/assets/css/ba-style.css');
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css');
        $captcha = self::getCaptcha($id);
        if ($captcha != '0') {
            $captch = JCaptcha::getInstance($captcha);
            $captch->initialise($captcha);
        }
        if ($payments->multiple_payment == 1 || ($method == 'stripe' && $submissionsOptions['display_total'] == 1)) {
            $document->addScript('https://checkout.stripe.com/checkout.js');
        }
        $elements = self::getElement($id);
        foreach ($elements as $element) {
            $element = explode('_-_', $element->settings);
            if ($element[2] == 'map' || $element[2] == 'address') {
                if ($map) {
                    $api_key = self::getMapsKey();
                    $document->addScript('https://maps.googleapis.com/maps/api/js?libraries=places&key='.$api_key);
                }
            }
            if ($element[2] == 'date') {
                JHTML::_('behavior.calendar');
            }
            if ($element[2] == 'slider') {
                $document->addScript(JUri::root() . 'components/com_baforms/libraries/bootstrap-slider/bootstrap-slider.js');
            }
        }
        $document->addScript(JUri::root() . 'components/com_baforms/libraries/modal/ba_modal.js');
        $document->addScript(JURI::root() . 'components/com_baforms/assets/js/ba-form.js');
    }

    protected static function restoreHTML($formSettings, $element, $submissionsOptions, $elements)
    {
        $html = '';
        $settings = explode('_-_', $element->settings);
        $symbol = $submissionsOptions['currency_symbol'];
        $position = $submissionsOptions['currency_position'];
        $language = JFactory::getLanguage();
        $language->load('com_baforms', JPATH_ADMINISTRATOR);
        if ($settings[2] == 'image') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-image tool';
            if ($options[4] == 1) {
                $html .= ' ba-lightbox-image';
            }
            $html .= '" style="text-align: '.$options[1].'">';
            $html .= '<img src="'.$options[0].'" class="ba-image alt="';
            $html .= htmlspecialchars($options[3], ENT_QUOTES).'" style';
            $html .= '="width: '.$options[2].'%;"';
            if ($options[4] == 1) {
                $html .= ' data-lightbox="'.$options[5].'"';
            }
            $html .= '></div>';
        }
        if ($settings[2] == 'textInput') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-textInput tool';
            $html .= '">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            if (!isset($options[4]) || empty($options[4])) {
                $options[4] = 'regular';
            }
            if ($options[4] == 'calculation' && $submissionsOptions['display_total'] != 1) {
                $options[4] = 'regular';
            }
            if (isset($options[5]) && strpos($options[5], 'zmdi') !== false) {
                $html .= '<div class="container-icon">';
            }
            $html .= '<input type="text" data-type="' .$options[4];
            $html .= '" style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .= $formSettings[7] .'; border-radius:' .$formSettings[8]. '"';
            $html .= " placeholder='" .htmlspecialchars($options[2], ENT_QUOTES). "'";
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= ' required';
                }
            }
            $html .= '><br>';
            if (isset($options[5]) && strpos($options[5], 'zmdi') !== false) {
                $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
                $html .= $formSettings[12].'" class="'.$options[5].'"></i></div></div>';
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'address') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-address tool';
            $html .= '">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            if (isset($options[4]) && strpos($options[4], 'zmdi') !== false) {
                $html .= '<div class="container-icon">';
            }
            $html .= '<input type="text" style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .= $formSettings[7] .'; border-radius:' .$formSettings[8]. '"';
            $html .= " placeholder='" .htmlspecialchars($options[2], ENT_QUOTES). "'";
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= ' required';
                }
            }
            $html .= '><br>';
            if (isset($options[4]) && strpos($options[4], 'zmdi') !== false) {
                $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
                $html .= $formSettings[12].'" class="'.$options[4].'"></i></div></div>';
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'email') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-email tool';
            $html .= '">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            if (isset($options[3]) && strpos($options[3], 'zmdi') !== false) {
                $html .= '<div class="container-icon">';
            }
            $html .= '<input type="email" style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .= $formSettings[7] .'; border-radius:' .$formSettings[8]. '"';
            $html .= " placeholder='" .htmlspecialchars($options[2], ENT_QUOTES);
            $html .= "' required";
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            $html .= '>';
            if (isset($options[3]) && strpos($options[3], 'zmdi') !== false) {
                $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
                $html .= $formSettings[12].'" class="'.$options[3].'"></i></div></div>';
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'textarea') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-textarea tool';
            $html .= '">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. ';"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            if (isset($options[5]) && strpos($options[5], 'zmdi') !== false) {
                $html .= '<div class="container-icon">';
            }
            $html .= '<textarea style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .= $formSettings[7] .'; border-radius:' .$formSettings[8];
            $html .= '; min-height:' .$options[4]. 'px;"';
            $html .= " placeholder='" .htmlspecialchars($options[2], ENT_QUOTES);
            $html .= "'";
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= ' required';
                }
            }
            $html .= '></textarea><br>';
            if (isset($options[5]) && strpos($options[5], 'zmdi') !== false) {
                $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
                $html .= $formSettings[12].'" class="'.$options[5].'"></i></div></div>';
            }
            $html .='</div>';
        }
        if ($settings[2] == 'date') {
            $options = explode(';', $settings[3]);
            if (isset($options[1]) && $options[1] == 1) {
                $options[0] .= ' *';
            }
            $html .= '<div class="ba-date tool';
            if (isset($options[1]) && $options[1] == 1) {
                $html .= ' required';
            }
            $html .= '">';
            $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
            $html .= $formSettings[2] .'; font-weight: ';
            $html .= $formSettings[10]. '">' .htmlspecialchars($options[0]). '</label>';
            $html .= '<div class="container-icon"><input type="text" name="';
            $html .= $element->id.'" style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .= $formSettings[7] .'; border-radius:' .$formSettings[8];
            $html .= '" id="date_'.$element->id.'" value="" readonly>';
            $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
            $html .= $formSettings[12].'" class="zmdi zmdi-calendar-alt"></i>';
            $html .= '</div></div></div>';
        }
        if ($settings[2] == 'htmltext') {
            $html .= '<div class="ba-htmltext tool">' .$settings[3];
            $html .= '</div>';
        }
        if ($settings[2] == 'chekInline') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-chekInline tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            $html .= '<div class="';
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= 'required';
                }
            }
            $html .= '">';
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= "<span style='font-size:" .$formSettings[4]. "; color:";
                $html .= $formSettings[5]."'><input type='checkbox' name='";
                $html .= $element->id;
                $html .= "[]' value='";
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= "'";
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[4]) && $options[4] != '' && $options[4] == $i) {
                    $html .= ' checked';
                }
                $html .= '/><span></span>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '</span>';
            }
            $html .= '</div></div>';
        }
        if ($settings[2] == 'radioInline') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-radioInline tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= "<span style='font-size:" .$formSettings[4]. "; color:";
                $html .= $formSettings[5]."'><input type='radio' name='";
                $html .= $element->id;
                $html .= "' value='";
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= "'";
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[3])) {
                    if ($options[3] == 1 && $i == 0) {
                        $html .= ' required';
                    }
                }
                if (isset($options[4]) && $options[4] != '' && $options[4] == $i) {
                    $html .= ' checked';
                }
                $html .= '/><span></span>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '</span>';
            }
            if (isset($settings[5]) && strlen($settings[5]) > 0) {
                $conditions = explode(';', $settings[5]);
                foreach ($conditions as $condition) {
                    $html .= '<div class="droppad_area condition-area" data-condition="'.$condition.'">';
                    foreach ($elements as $value) {
                        $sett = explode('_-_', $value->settings);
                        if ($settings[1] == $sett[0] && $sett[4] === $condition) {
                            $html .= self::restoreHTML($formSettings, $value, $submissionsOptions, $elements);
                        }
                    }
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'checkMultiple') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-checkMultiple tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if ($options[3] == 1){
                    $html .= ' *';
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            $html .= '<div class="';
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= 'required';
                }
            }
            $html .= '">';
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= "<span style='font-size:" .$formSettings[4]. "; color:";
                $html .= $formSettings[5]."'><input type='checkbox' name='";
                $html .= $element->id;
                $html .= "[]' value='";
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= "'";
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[4]) && $options[4] != '' && $options[4] == $i) {
                    $html .= ' checked';
                }
                $html .= '/><span></span>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '<br></span>';
            }
            $html .= '</div></div>';
        }
        if ($settings[2] == 'upload') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-upload tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[4]) && $options[4] == 1) {
                    $html .= ' *';
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $html .= "<input class='ba-upload' type='file'";
            if (isset($options[4]) && $options[4] == 1) {
                $html .= ' required';
            }
            $html .= " multiple name='" ;
            $html .= $element->id;
            $html .= "[]'><br>";
            $html .= '<span style="font-size: 12px; font-style:';
            $html .= ' italic; color: #999;">' .$language->_('MAXIMUM_FILE_SIZE'). ' ' .$options[2];
            $html .= 'mb (' .$options[3]. ')</span>';
            $html .= '<input type="hidden" class="upl-size"';
            $html .= ' value="'.$options[2].'">';
            $html .= '<input type="hidden" class="upl-type"';
            $html .= ' value="'.$options[3].'">';
            $html .= '<input type="hidden" class="upl-error">';
            $html .= "</div>";
        }
        if ($settings[2] == 'radioMultiple') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-radioMultiple tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= "<span style='font-size:" .$formSettings[4]. "; color:";
                $html .= $formSettings[5]."'><input type='radio' name='";
                $html .= $element->id;
                $html .= "' value='";
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= "'";
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' required';
                    }
                }
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                if (isset($options[4]) && $options[4] != '' && $options[4] == $i) {
                    $html .= ' checked';
                }
                $html .= '/><span></span>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '<br></span>';
            }
            if (isset($settings[5]) && strlen($settings[5]) > 0) {
                $conditions = explode(';', $settings[5]);
                foreach ($conditions as $condition) {
                    $html .= '<div class="droppad_area condition-area" data-condition="'.$condition.'">';
                    foreach ($elements as $value) {
                        $sett = explode('_-_', $value->settings);
                        if ($settings[1] == $sett[0] && $sett[4] === $condition) {
                            $html .= self::restoreHTML($formSettings, $value, $submissionsOptions, $elements);
                        }
                    }
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'dropdown') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-dropdown tool';
            $html .= '">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            if (isset($options[4]) && strpos($options[4], 'zmdi') !== false) {
                $html .= '<div class="container-icon">';
            }
            $html .= '<select style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .=  $formSettings[7]. '"';
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= ' required';
                }
            }
            $html .= '>';
            $html .= '<option value="">' .$language->_('SELECT'). '</option>';
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= '<option value="';
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '"';
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[5]) && $options[5] != '' && $options[5] == $i) {
                    $html .= ' selected';
                }
                $html .= '>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '</option>';
            }
            $html .= '</select>';
            if (isset($options[4]) && strpos($options[4], 'zmdi') !== false) {
                $html .= '<div class="icons-cell"><i style="font-size: '.$formSettings[11].'px; color: ';
                $html .= $formSettings[12].'" class="'.$options[4].'"></i></div></div>';
            }
            if (isset($settings[5]) && strlen($settings[5]) > 0) {
                $conditions = explode(';', $settings[5]);
                foreach ($conditions as $condition) {
                    $html .= '<div class="droppad_area condition-area" data-condition="'.$condition.'">';
                    foreach ($elements as $value) {
                        $sett = explode('_-_', $value->settings);
                        if ($settings[1] == $sett[0] && $sett[4] === $condition) {
                            $html .= self::restoreHTML($formSettings, $value, $submissionsOptions, $elements);
                        }
                    }
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        }
        if ($settings[2] == 'selectMultiple') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="ba-selectMultiple tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. ';"><span>' .htmlspecialchars($options[0]);
                if (isset($options[3])) {
                    if ($options[3] == 1) {
                        $html .= ' *';
                    }
                }
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $option = str_replace('"', '', $options[2]);
            $option = explode('\n', $option);
            $html .= '<select multiple size="'.$options[4].'" style="';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .=  $formSettings[7]. '"';
            $html .= " name='";
            $html .= $element->id;
            $html .= "[]'";
            if (isset($options[3])) {
                if ($options[3] == 1) {
                    $html .= ' required';
                }
            }
            $html .= '>';
            for ($i = 0; $i < count($option); $i++) {
                $option[$i] = explode('====', $option[$i]);
                $html .= '<option value="';
                $html .= htmlspecialchars($option[$i][0], ENT_QUOTES);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '"';
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' data-price="'.$option[$i][1].'"';
                    }
                }
                if (isset($options[5]) && $options[5] != '' && $options[5] == $i) {
                    $html .= ' selected';
                }
                $html .= '>' .htmlspecialchars($option[$i][0]);
                if (isset($option[$i][1]) && $submissionsOptions['display_total'] == 1) {
                    if ($option[$i][1] != '') {
                        $html .= ' - ';
                        if ($position == 'before') {
                            $html .= $symbol;
                        }
                        $html .= $option[$i][1];
                        if ($position != 'before') {
                            $html .= $symbol;
                        }
                    }
                }
                $html .= '</option>';
            }
            $html .= '</select></div>';
        }
        if ($settings[2] == 'map') {
            $options = explode(';', $settings[3]);
            $html .= '<div><div class="ba-map tool" style="width:' .$options[3];
            $html .= '%; height:' .$options[4]. 'px;"></div>';
            $html .= "<input type='hidden' value='";
            $html .= str_replace("'", '-_-', $settings[3])."' class='ba-options'></div>";
        }
        if ($settings[2] == 'slider') {
            $options = explode(';', $settings[3]);
            $html .= '<div class="slider tool">';
            if ($options[0] != '') {
                $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
                $html .= $formSettings[2] .'; font-weight: ';
                $html .= $formSettings[10]. '"><span>' .htmlspecialchars($options[0]);
                if (!empty($options[1])) {
                    $html .= '<span class="ba-tooltip">'.htmlspecialchars($options[1]).'</span>';
                }
                $html .= '</span>';
                $html .='</label>';
            }
            $html .= '<input type="hidden" class="ba-slider-values" ';
            $html .= " name='";
            $html .= $element->id;
            $html .= "'";
            $html .= '>';
            $html .= '<div class="ba-slider"></div>';
            $html .= '<input type="hidden" value="' .htmlspecialchars($settings[3]). '" class="ba-options"></div>';
        }
        return $html;
    }
    
    public static function drawHTMLPage($id) 
    {
        $form = baformsHelper::getForm($id);
        $payments = baformsHelper::getPayment($id);
        $columns = baformsHelper::getColumn($id);
        $elements = baformsHelper::getElement($id);
        $popup = baformsHelper::getPopup($id);
        $submissionsOptions = baformsHelper::getSubmisionOptions($id);
        $symbol = $submissionsOptions['currency_symbol'];
        $position = $submissionsOptions['currency_position'];
        $embed = self::getEmbed($id);
        $method = $submissionsOptions['payment_methods'];
        $title = $form[0]->title;
        $titleSettings = $form[0]->title_settings;
        $formSettings = $form[0]->form_settings;
        $formSettings = explode('/', $formSettings);
        $uri = JURI::getInstance();
        $url = $uri->toString( array( 'scheme', 'host', 'port' ) ) . JURI::root(true);
        $url .= '/index.php?option=com_baforms&amp;view=form&amp;form_id='.$id;
        $language = JFactory::getLanguage();
        $language->load('com_baforms', JPATH_ADMINISTRATOR);
        $formStyle = explode(';', $formSettings[9]);
        if (!isset($formSettings[11])) {
            $formSettings[11] = 24;
            $formSettings[12] = '#dedede';
        }
        if (empty($submissionsOptions['message_bg_rgba'])) {
            $submissionsOptions['message_bg_rgba'] = '#ffffff';
        }
        if (empty($submissionsOptions['message_color_rgba'])) {
            $submissionsOptions['message_color_rgba'] = '#333333';
        }
        if (empty($submissionsOptions['dialog_color_rgba'])) {
            $submissionsOptions['dialog_color_rgba'] = 'rgba(0, 0, 0, 0.15)';
        }
        $theme_color = explode(',', $form[0]->theme_color);
        $theme_color[3] = '1)';
        $theme_color = implode(',', $theme_color);
        $shadow_color = explode(',', $form[0]->theme_color);
        $shadow_color[3] = '0.3)';
        $shadow_color = implode(',', $shadow_color);
        $html = "<div class='com-baforms " .$formSettings[0]. "'>";
        $html .= '<style scoped>
            #baform-'.$id.' .ba-form input:focus,
            #baform-'.$id.' .ba-form textarea:focus,
            #baform-'.$id.' .ba-form select:focus,
            #baform-'.$id.' .ba-form input[type="radio"]:checked + span:after,
            #baform-'.$id.' .ba-form input[type="checkbox"]:checked + span:after,
            #baform-'.$id.' .ba-form input[type="radio"]:hover + span:before,
            #baform-'.$id.' .ba-form input[type="checkbox"]:hover + span:before {
                border-color: '.$form[0]->theme_color.' !important;
            }
            .calendar thead td.title:after {
                border-color: '.$theme_color.' !important;
            }
            .calendar thead td.title,
            .calendar thead tr:first-child {
                background: '.$theme_color.' !important;
            }
            #baform-'.$id.' .ba-form .slider-handle:active,
            #baform-'.$id.' .ba-form .slider-handle:hover {
                 box-shadow: 0px 0px 0px 10px '.$shadow_color.' !important;
                 -webkit-box-shadow: 0px 0px 0px 10px '.$shadow_color.' !important;
            }
            #baform-'.$id.' .ba-form input[type="radio"]:checked + span:after,
            #baform-'.$id.' .ba-form input[type="checkbox"]:checked + span:after,
            #baform-'.$id.' .ba-form .slider-handle,
            .calendar .daysrow .day.selected {
                background: '.$form[0]->theme_color.' !important;
            }
            #baform-'.$id.' .ba-form .slider-track {
                background-color: '.$form[0]->theme_color.' !important;
            }
            .calendar thead .weekend {
                color: '.$form[0]->theme_color.' !important;
            }
            </style>';
        $html .= '<div class="modal-scrollable ba-forms-modal"><div class="ba-modal fade hide message-modal"';
        $html .= ' style="color:' .$submissionsOptions['message_color_rgba'];
        $html .= '; background-color: ' .$submissionsOptions['message_bg_rgba'];
        $html .= ';"><a href="#" class="ba-modal-close zmdi zmdi-close"></a>';
        $html .= '<div class="ba-modal-body"><div class="message"></div><input type="hidden" value="';
        $html .= $submissionsOptions['dialog_color_rgba'].'" class="dialog-color"></div></div></div>';
        if ($popup['display_popup'] == 1) {
            if ($popup['button_type'] != 'link') {
                $html .= '<div class="btn-' .$popup['button_position']. '">';
                $html .= "<input type='button' value='".$popup['button_lable'];
                $html .= "' style='background-color: " .$popup['button_bg'];
                $html .= "; font-weight:" .$popup['button_weight'];
                $html .= "; border-radius:" .$popup['button_border']. "px";
                $html .= "; font-size:" .$popup['button_font_size']. "px";
                $html .= "; color: " .$popup['button_color']. "'";
                $html .= " data-popup='popup-form-".$id."' class='popup-btn'>";
                $html .= '</div>';
            } else {
                $html .= '<a href="#" class="popup-btn" data-popup="popup-form-';
                $html .= $id.'">'.$popup['button_lable'].'</a>';
            }
            $html .= '<div class="modal-scrollable  ba-forms-modal"><div class="ba-modal';
            $html .= ' fade hide popup-form" id="popup-form-'.$id.'" style="display: none; ';
            $html .= 'width: ' .$popup['modal_width']. 'px">';
            $html .= '<a href="#" class="ba-modal-close zmdi zmdi-close"></a><div class="ba-modal-body">';
        }
        $html .= '<form novalidate id="baform-'.$id.'" action="' . $url. '"';
        $html .= ' method="post" class="form-validate';
        if ($method != '' && $submissionsOptions['display_total'] == 1) {
            $html .= ' ba-payment';
        }
        $html .= '" enctype="multipart/form-data">';
        $html .= '<div style="' ;
        if ($popup['display_popup'] == 0) {
            $html .= $formStyle[0]. '; ';
        }
        $html .= $formStyle[1]. ';' . $formStyle[2]. ';' . $formStyle[3];
        $html .= '" class="ba-form">';
        if ($submissionsOptions['display_title'] == 1) {
            $html .= '<div class="row-fluid ba-row"><div class="span12" style="' .$titleSettings. '">';
            $html.= $title . '</div></div>';
        }
        $row = '';
        if (empty($columns)) {
            foreach ($elements as $element) {
                $element = explode('_-_', $element->settings);
                if ($element[0] == 'button') {
                    $button = $element[1];
                    $buttonStyle = $element[2];
                    $buttonAligh = $element[3];
                }
            }
        }
        $n = 1;
        $html .= '<div class="page-0">';
        $columnFlag = false;
        foreach ($columns as $column) {
            if (strpos($column->settings, 'first') !== false) {
                $columnFlag = true;
                break;
            }
        }
        foreach ($columns as $column) {
            $column = explode(',',$column->settings);
            if (trim($column[1]) == 'spank') {
                if (count($column) > 6) {
                    $column[3] = $column[3].','.$column[4].','.$column[5].','.$column[6];
                    $column[3] .= ','.$column[7].','.$column[8].','.$column[9];
                    $column[4] = $column[10];
                    $column[5] = $column[11];
                    if (count($column) > 12) {
                        $column[5] .= ','.$column[12].','.$column[13].','.$column[14];
                        $column[5] .= ','.$column[15].','.$column[16].','.$column[17];
                    }
                }
                $prev = $column[3];
                $prev = explode(';', $prev);
                $next = $column[5];
                $next = explode(';', $next);
                if (strpos($prev[3], 'rgb') === false) {
                    $prev[3] = '#'.$prev[3];
                }
                if (strpos($prev[4], 'rgb') === false) {
                    $prev[4] = '#'.$prev[4];
                }
                if (strpos($next[3], 'rgb') === false) {
                    $next[3] = '#'.$next[3];
                }
                if (strpos($next[3], 'rgb') === false) {
                    $next[4] = '#'.$next[4];
                }
                if ($n != 1) {
                    $html .= '<div class="ba-prev"><input type="button" value="';
                    $html .= $prev[0].'" style="border-radius:' .$prev[7];
                    $html .= 'px; background-color: ' .$prev[3]. '; font-size:';
                    $html .= $prev[5]. 'px; font-weight:' .$prev[6]. '; width:';
                    $html .= $prev[1]. 'px; height:' .$prev[2]. 'px; color: ' .$prev[4];
                    $html .= '" class="btn-prev"></div>';
                }
                if ($n == 1) {
                    $last = $prev;
                }
                $html .= '<div class="ba-next"><input type="button" value="';
                $html .= $next[0].'" style="border-radius:' .$next[7];
                $html .= 'px; background-color: ' .$next[3]. '; font-size:';
                $html .= $next[5]. 'px; font-weight:' .$next[6]. '; width:';
                $html .= $next[1]. 'px; height:' .$next[2]. 'px; color: ' .$next[4];
                $html .= '" class="btn-next"></div></div>';
                $html .= '<div class="page-' .$n. '" style="display:none">';
                $n++;
            }
            if (!$columnFlag) {
                if (trim($column[1]) == 'span12') {
                    $html .= '<div class="row-fluid ba-row">';
                }
                if (trim($column[1]) == 'span6') {
                    if ($row == 1) {
                        $row = 2;
                    }
                    if ($row == '') {
                        $html .= '<div class="row-fluid ba-row">';
                        $row = 1;
                    }
                }
                if (trim($column[1]) == 'span4') {
                    if ($row == 2) {
                        $row = 3;
                    }
                    if ($row == 1) {
                        $row = 2;
                    }
                    if ($row == '') {
                        $html .= '<div class="row-fluid ba-row">';
                        $row = 1;
                    }
                }
                if (trim($column[1]) == 'span3') {
                    if ($row == 3) {
                        $row = 4;
                    }
                    if ($row == 2) {
                        $row = 3;
                    }
                    if ($row == 1) {
                        $row = 2;
                    }
                    if ($row == '') {
                        $html .= '<div class="row-fluid ba-row">';
                        $row = 1;
                    }
                }
            } else {
                if (isset($column[2]) && $column[2] == 'first') {
                    $html .= '<div class="row-fluid ba-row">';
                }
            }
            if (trim($column[1]) != 'spank') {
                $html .= '<div class="' .$column[1]. '">';
                foreach ($elements as $element) {
                    $settings = explode('_-_', $element->settings);
                    if ($settings[0] == 'button') {
                        $button = $settings[1];
                        $buttonStyle = $settings[2];
                        $buttonAligh = $settings[3];
                    }
                    if ($settings[0] == $column[0]) {
                        $html .= self::restoreHTML($formSettings, $element, $submissionsOptions, $elements);
                    }
                }
                $html .= '</div>';
            }
            if (trim($column[1]) == 'span12') {
                $html .= '</div>';
            }
            if (!$columnFlag) {
                if (trim($column[1]) == 'span6') {
                    if ($row == 2) {
                        $html .= '</div>';
                        $row = '';
                    }
                }
                if (trim($column[1]) == 'span4') {
                    if ($row == 3) {
                        $html .= '</div>';
                        $row = '';
                    }
                }
                if (trim($column[1]) == 'span3') {
                    if ($row == 4) {
                        $html .= '</div>';
                        $row = '';
                    }
                }
            } else {
                if (isset($column[2]) && $column[2] == 'last') {
                    $html .= '</div>';
                }
            }
            
        }
        $capt = $submissionsOptions['alow_captcha'];
        if ($capt != '0') {
            $captcha = JCaptcha::getInstance($capt);
            $captcha->initialise($capt);
            $html .= "<div class='tool ba-captcha'>";
            $html .= $captcha->display($capt, $capt, 'g-recaptcha');
            $html .= "</div>";
        }
        if ($n != 1) {
            $html .= '<div class="ba-prev"><input type="button" value="';
            $html .= $last[0].'" style="border-radius:' .$last[7];
            $html .= 'px; background-color: ' .$last[3]. '; font-size:';
            $html .= $last[5]. 'px; font-weight:' .$last[6]. '; width:';
            $html .= $last[1]. 'px; height:' .$last[2]. 'px; color: ' .$last[4];
            $html .= '" class="btn-prev"></div>';
        }
        if ($submissionsOptions['display_cart'] == 1 && $submissionsOptions['display_total'] == 1) {
            $html .= '<div class="baforms-cart" style="'.$formSettings[7];
            $html .= ';font-size:' .$formSettings[4]. '; color:' .$formSettings[5];
            $html .='""><div class="product-cell ba-cart-headline" style="';
            $html .= str_replace('border', 'border-bottom', $formSettings[7]);
            $html .='; font-size:' .$formSettings[1]. '; color:';
            $html .= $formSettings[2] .'; font-weight: ';
            $html .= $formSettings[10]. ';">';
            $html .= '<div class="product">'.$language->_("ITEM").'</div>';
            $html .= '<div class="price">'.$language->_("PRICE");
            $html .= '</div><div class="quantity">'.$language->_("QUANTITY");
            $html .= '</div><div class="total">'.$language->_("TOTAL");
            $html .= '</div><div class="remove-item"></div></div></div>';
            $html .= '<input type="hidden" class="cart-currency" value="';
            $html .= $symbol. '">';
            $html .= '<input type="hidden" class="cart-position" value="';
            $html .= $position. '">';
        }
        if ($submissionsOptions['display_total'] == 1) {
            $html .= '<div class="ba-total-price"><p style="text-align: ';
            $html .= $formSettings[14].'; font-size:' .$formSettings[1]. '; color:';
            $html .= $formSettings[2] .'; font-weight: '.$formSettings[10]. ';">';
            $html .= $formSettings[13];
            $html .=': ';
            if ($position == 'before') {
                $html .= '<span>'.$symbol.'</span>';
            }
            $html .= '<span class="ba-price">0</span>';
            if ($position != 'before') {
                $html .= '<span>'.$symbol.'</span>';
            }
            $html .= '<input type="hidden" name="ba_total" value="0"></p></div>';
        }
        if ($payments->multiple_payment == 1) {
            $html .= '<label style="font-size:' .$formSettings[1]. '; color:';
            $html .= $formSettings[2] .'; font-weight: ';
            $html .= $formSettings[10]. '"><span>';
            $html .= $language->_('SELECT_PAYMENT_METHOD').'</span></label>';
            $html .= '<select name="task" style="height:' .$formSettings[3]. '; ';
            $html .= 'font-size:' .$formSettings[4]. ';color:' .$formSettings[5];
            $html .= '; background-color:' .$formSettings[6]. '; ';
            $html .=  $formSettings[7]. '">';
            if (!empty($payments->seller_id)) {
                $html .= '<option value="form.twoCheckout">2checkout</option>';
            }
            if (!empty($payments->mollie_api_key)) {
                $html .= '<option value="form.mollie">Mollie</option>';
            }
            if (!empty($payments->paypal_email)) {
                $html .= '<option value="form.paypal">Paypal</option>';
            }
            if (!empty($payments->payu_api_key) && !empty($payments->payu_merchant_id) && !empty($payments->payu_account_id)) {
                $html .= '<option value="form.payu">Payu</option>';
            }
            if (!empty($payments->skrill_email)) {
                $html .= '<option value="form.skrill">Skrill</option>';
            }
            if (!empty($payments->stripe_api_key)) {
                $html .= '<option value="form.stripe" data-api-key="'.$payments->stripe_api_key;
                $html .= '" data-image="'.JUri::root().$payments->stripe_image;
                $html .= '" data-name="'.$payments->stripe_name.'" data-description="';
                $html .= $payments->stripe_description;
                $html .= '">Stripe</option>';
            }
            if (!empty($payments->webmoney_purse)) {
                $html .= '<option value="form.webmoney">Webmoney</option>';
            }
            if (!empty($payments->custom_payment)) {
                $html .= '<option value="form.save">'.$payments->custom_payment.'</option>';
            }
            $html .= '</select>';
        } else if ($method == 'paypal' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.paypal">';
        } else if ($method == '2checkout' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.twoCheckout">';
        } else if ($method == 'skrill' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.skrill">';
        }  else if ($method == 'webmoney' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.webmoney">';
        }  else if ($method == 'payu' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.payu">';
        }  else if ($method == 'stripe' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.stripe" data-api-key="';
            $html .= $payments->stripe_api_key.'" data-image="'.JUri::root().$payments->stripe_image;
            $html .= '" data-name="'.$payments->stripe_name.'" data-description="';
            $html .= $payments->stripe_description.'">';
        }  else if ($method == 'mollie' && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="task" value="form.mollie">';
        } else {
            $html .= '<input type="hidden" name="task" value="form.save">';
        }
        if ($submissionsOptions['display_cart'] == 1 && $submissionsOptions['display_total'] == 1) {
            $html .= '<input type="hidden" name="baforms_cart" class="forms-cart">';
        }
        if ($submissionsOptions['display_submit'] == 1) {
            $html .= '<div class="row-fluid ba-row"><div class="span12" style="'.$buttonAligh.'">';
            $html .= '<input class="ba-btn-submit';
            $html .= '" type="submit" style="' .$buttonStyle;
            $html .= '" value="' .$button. '" ' .$embed. '>';
            $html .= '</div></div>';
        }
        $html .= '</div><input type="hidden" class="redirect" value="';
        $html .= $submissionsOptions['redirect_url']. '">';
        $html .= '<input type="hidden" class="currency-code" value="';
        $html .= $submissionsOptions['currency_code']. '">';
        $html .= '<input type="hidden" class="sent-massage" value="';
        $html .= htmlspecialchars($submissionsOptions['sent_massage']). '">';
        $html .= '<input type="hidden" value="' .JURI::root();
        $html .= '" class="admin-dirrectory">';
        $html .= '<input type="hidden" name="form_id" value="' .$id. '">';
        $html .= '</div>';
        $html .='</form>';
        if ($popup['display_popup'] == 1) {
            $html .= '</div></div></div>';
        }
        $html .= "</div>";
        return $html;
    }
    
    /*
        get the submission options of the form
    */
    protected static function getSubmisionOptions($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("alow_captcha, display_title, sent_massage, error_massage,
                        redirect_url, display_submit,   dialog_color_rgba,
                        message_color_rgba, message_bg_rgba, display_total,
                        currency_code, currency_symbol, payment_methods,
                        display_cart, currency_position");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadAssoc();
        return $items;
    }
    
    public static function getCaptcha($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("alow_captcha");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadResult();
        return $items;
    }

    protected static function getPayment($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("multiple_payment, custom_payment, paypal_email,
                       seller_id, skrill_email, webmoney_purse, payu_api_key,
                       payu_merchant_id, payu_account_id, stripe_api_key,
                       stripe_image, stripe_name, stripe_description, mollie_api_key");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    /*
        get the form
    */
    protected static function getForm($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("title, title_settings, form_settings, theme_color");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }
    
    /*
        get the fomr columns
    */
    protected static function getColumn($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("settings");
        $query->from("#__baforms_columns");
        $query->where("form_id=" . $id);
        $query->order("id ASC");
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }
    
    /*
        get the form items
    */
    public static function getElement($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("settings, id");
        $query->from("#__baforms_items");
        $query->where("form_id=" . $id);
        $query->order("column_id ASC");
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }
    
    /*
        check the publishing of the form
    */
    public static function checkForm($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("published");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $publish = $db->loadResult();
        if (isset($publish)) {
            if ($publish == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public static function getEmbed($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("submit_embed");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadResult();
        return $items;
    }
    
    /*
        get the popup options
    */
    public static function getPopup($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("display_popup, button_lable, button_position, button_bg,
                        button_color, button_font_size, button_weight,
                        button_border, modal_width, button_type");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadAssoc();
        return $items;
    }
}