<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

if (JVERSION >= '3.4.0') {
    JHtml::_('behavior.formvalidator');
} else {
    JHtml::_('behavior.formvalidation');
}
$checkUpdate = baformsHelper::checkUpdate($this->about->version);
JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task) {
        if (task == "form.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
        {
            Joomla.submitform(task, document.getElementById("adminForm"));
        }
    };
');
?>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="https://maps.google.com/maps/api/js?libraries=places" type="text/javascript"></script>
<script src="components/com_baforms/assets/js/ba-email.js" type="text/javascript"></script>
<script src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>
<link rel="stylesheet" href="components/com_baforms/assets/css/ba-admin.css" type="text/css"/>
<div id="fields-editor" class="modal hide ba-modal-md" style="display:none">
    <div class="fields-backdrop" data-dismiss="modal"></div>
    <div class="modal-header">
        <h3><?php echo JText::_('FIELDS'); ?></h3>
    </div>
    <div class="modal-body">
        <div class="search-bar">
            <input type="text" class="ba-search" placeholder="<?php echo JText::_('SEARCH'); ?>">
        </div>
        <div class="forms-table">
            <table class="forms-list">
                <tbody>
<?php           foreach ($this->items as $item) {
                $settings = explode('_-_', $item->settings);
                if ($settings[0] != 'button') {
                    $type = $settings[2];
                    if ($type != 'image' && $type != 'htmltext' && $type != 'map'){
                        $settings = explode(';', $settings[3]);
                        $name = $this->checkItems($settings[0], $type, $settings[2]);
                        $str = '<tr><th class="form-title"><a href="#">'.$name;
                        $str .= '</a></th><td>'. $item->id. '</td></tr>';
                        echo $str;
                    }
                }
    
} ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
    </div>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_baforms&layout=edit&id='); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
            
<?php
    echo $this->form->getInput('id');
    echo $this->form->getInput('title_settings');
    echo $this->form->getInput('form_settings');
    echo $this->form->getInput('form_content');
    echo $this->form->getInput('form_columns');
    echo $this->form->getInput('email_options');
?>
    <input type="hidden" class="email_letter" name="email_letter">
    <div id="delete-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('DELETE_QUESTION') ?></p>            
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="delete-aply"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="notification-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('NOTIFICATION_MESSAGE'); ?></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="html-editor" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">x</a>
            <h3>Edit text</h3>
        </div>
        <div class="modal-body">
            <textarea name="CKE-editor"></textarea>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="aply-html"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="icons-upload-modal" class="ba-modal-xl modal ba-modal-dialog hide" style="display:none">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">x</a>
            <h3 class="ba-modal-header"><?php echo JText::_('SELECT_ICON'); ?></h3>
        </div>
        <div class="modal-body">
            <iframe src="<?php echo JUri::base(). 'index.php?option=com_baforms&view=icons&tmpl=component'; ?>" width="100%" height="487"></iframe>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="layout-dialog" class="modal hide ba-modal-lg" style="display:none">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">x</a>
            <h3><?php echo JText::_('NUMBER_COLUMNS') ?></h3>
        </div>
        <div class="modal-body">
            <label class="column">
                <input type="radio" name="radioMultiple" value="" data-column="1" class="add-column">
                <img src="components/com_baforms/assets/images/1col.png">
                <p><?php echo JText::_('ONE') ?></p>
            </label>
            <label class="column">
                <input type="radio" name="radioMultiple" value="" data-column="2" class="add-column">
                <img src="components/com_baforms/assets/images/2col.png">
                <p><?php echo JText::_('TWO') ?></p>
            </label>
            <label class="column">
                <input type="radio" name="radioMultiple" value="" data-column="3" class="add-column">
                <img src="components/com_baforms/assets/images/3col.png">
                <p><?php echo JText::_('THREE') ?></p>
            </label>
            <label class="column">
                <input type="radio" name="radioMultiple" value="" data-column="4" class="add-column">
                <img src="components/com_baforms/assets/images/4col.png">
                <p><?php echo JText::_('FOUR') ?></p>
            </label>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="global-options" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">x</a>
            <h3><?php echo JText::_('FORM_SETTINGS') ?></h3>
        </div>
        <div class="modal-body">
            <div id="global-tabs">
                <ul>
                    <li><a href="#form-submission"><?php echo JText::_('FORM_SUBMISSION') ?></a></li>
                    <li><a href="#emails"><?php echo JText::_('EMAIL_NOTIFICATIONS') ?></a></li>
                    <li><a href="#auto-reply"><?php echo JText::_('EMAIL_REPLY') ?></a></li>
                    <li><a href="#popup"><?php echo JText::_('POPUP_OPTIONS') ?></a></li>
                    <li><a href="#payment"><?php echo JText::_('PAYMENT') ?></a></li>
                </ul>
                <div id="form-submission">
                    <label><?php echo JText::_('DISPLAY_TITLE') ?></label>
                    <input type="hidden" name="jform[display_title]" value="0">
                    <?php echo $this->form->getInput('display_title'); ?>
                    <label><?php echo JText::_('DISPLAY_SUBMIT') ?></label>
                    <input type="hidden" name="jform[display_submit]" value="0">
                    <?php echo $this->form->getInput('display_submit'); ?>
                    <label><?php echo JText::_('ALLOW_CAPTCHA') ?></label>
                    <?php echo $this->form->getInput('alow_captcha'); ?>
                    <label><?php echo JText::_('MESSAGE_BG') ?></label>
                    <input type="text" id="message_bg">
                    <?php echo $this->form->getInput('message_bg_rgba'); ?>
                    <label><?php echo JText::_('MESSAGE_COLOR') ?></label>
                    <input type="text" id="message_color">
                    <?php echo $this->form->getInput('message_color_rgba'); ?>
                    <label><?php echo JText::_('LIGHTBOX_BG') ?></label>
                    <input type="text" id="dialog_color">
                    <?php echo $this->form->getInput('dialog_color_rgba'); ?>
                    <label><?php echo JText::_('SENT_MESSAGE') ?></label>
                    <?php echo $this->form->getInput('sent_massage'); ?>
                    <label><?php echo JText::_('ERROR_MESSAGE') ?></label>
                    <?php echo $this->form->getInput('error_massage'); ?>
                    <label><?php echo JText::_('CHECK_IP') ?></label>
                    <input type="hidden" name="jform[check_ip]" value="0">
                    <?php echo $this->form->getInput('check_ip'); ?>
                    <label><?php echo JText::_('REIDRECTION_URL') ?></label>
                    <?php echo $this->form->getInput('redirect_url'); ?>
                </div>
                <div id="emails">
                    <label><?php echo JText::_('EMAIL_RECIPIENT') ?></label>
                    <?php echo $this->form->getInput('email_recipient'); ?>
                    <label><?php echo JText::_('EMAIL_SUBJECT') ?></label>
                    <?php echo $this->form->getInput('email_subject'); ?>
                    <label><?php echo JText::_('REPLY_TO_SUBMITTER') ?></label>
                    <input type="hidden" name="jform[add_sender_email]" value="0">
                    <?php echo $this->form->getInput('add_sender_email'); ?>
                </div>
                <div id="auto-reply">
                    <label><?php echo JText::_('SENDER_NAME') ?></label>
                    <?php echo $this->form->getInput('sender_name'); ?>
                    <label><?php echo JText::_('SENDER_EMAIL') ?></label>
                    <?php echo $this->form->getInput('sender_email'); ?>
                    <label><?php echo JText::_('EMAIL_SUBJECT') ?></label>
                    <?php echo $this->form->getInput('reply_subject'); ?>
                    <label><?php echo JText::_('EMAIL_BODY') ?></label>
                    <?php echo $this->form->getInput('reply_body'); ?>
                    <label><?php echo JText::_('COPY_SUBMITED_DATA'); ?></label>
                    <input type="hidden" name="jform[copy_submitted_data]" value="0">
                    <?php echo $this->form->getInput('copy_submitted_data'); ?>
                </div>
                <div id="popup">
                    <label><?php echo JText::_('DISPLAY_POPUP') ?></label>
                    <input type="hidden" name="jform[display_popup]" value="0">
                    <?php echo $this->form->getInput('display_popup'); ?>
                    <label><?php echo JText::_('MODAL_WIDTH') ?>:</label>
                    <?php echo $this->form->getInput('modal_width'); ?>
                    <label><?php echo JText::_('LABEL') ?></label>
                    <?php echo $this->form->getInput('button_lable'); ?>
                    <label><?php echo JText::_('TYPE') ?></label>
                    <?php echo $this->form->getInput('button_type'); ?>
                    <label><?php echo JText::_('POSITION') ?></label>
                    <?php echo $this->form->getInput('button_position'); ?>
                    <label><?php echo JText::_('BUTTON_BACKGROUND') ?></label>
                    <input type="text" id="button_bg">
                    <?php echo $this->form->getInput('button_bg'); ?>
                    <label><?php echo JText::_('BUTTON_COLOR') ?></label>
                    <input type="text" id="button_color">
                    <?php echo $this->form->getInput('button_color'); ?>
                    <label><?php echo JText::_('FONT_SIZE') ?></label>
                    <?php echo $this->form->getInput('button_font_size'); ?>
                    <label><?php echo JText::_('TITLE_WEIGHT') ?></label>
                    <?php echo $this->form->getInput('button_weight'); ?>
                    <input type="radio" name="popup-font-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                    <input type="radio" name="popup-font-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                    <label><?php echo JText::_('BORDER_RADIUS') ?></label>
                    <?php echo $this->form->getInput('button_border'); ?>
                </div>
                <div id="payment">
                    <label><?php echo JText::_('DISPLAY_TOTAL') ?></label>
                    <input type="hidden" name="jform[display_total]" value="0">
                    <?php echo $this->form->getInput('display_total'); ?>
                    <label><?php echo JText::_('DISPLAY_CART') ?></label>
                    <input type="hidden" name="jform[display_cart]" value="0">
                    <?php echo $this->form->getInput('display_cart'); ?>
                    <label><?php echo JText::_('CURRENCY_CODE') ?></label>
                    <?php echo $this->form->getInput('currency_code'); ?>
                    <label><?php echo JText::_('CURRENCY_SYMBOL') ?></label>
                    <?php echo $this->form->getInput('currency_symbol'); ?>
                    <label><?php echo JText::_('CURRENCY_POSITION') ?></label>
                    <?php echo $this->form->getInput('currency_position'); ?>
                    <label><?php echo JText::_('PAYMENT_METHODS') ?></label>
                    <?php echo $this->form->getInput('payment_methods'); ?>
                    <div class="paypal-login">
                        <label><?php echo JText::_('PAYPAL_EMAIL') ?></label>
                        <?php echo $this->form->getInput('paypal_email'); ?>
                    </div>
                    <div class="2checkout">
                        <label><?php echo JText::_('ACCOUNT_NUMBER') ?></label>
                        <?php echo $this->form->getInput('seller_id'); ?>
                    </div>
                    <div class="skrill">
                        <label><?php echo JText::_('SKRILL_EMAIL') ?></label>
                        <?php echo $this->form->getInput('skrill_email'); ?>
                    </div>
                    <div class="webmoney">
                        <label><?php echo JText::_('WEBMONEY_PURSE') ?></label>
                        <?php echo $this->form->getInput('webmoney_purse'); ?>
                    </div>
                    <div class="custom-payment">
                        <label><?php echo JText::_('LABEL') ?></label>
                        <?php echo $this->form->getInput('custom_payment'); ?>
                    </div>
                    <div class="payu">
                        <label><?php echo JText::_('API_KEY') ?></label>
                        <?php echo $this->form->getInput('payu_api_key'); ?>
                        <label><?php echo JText::_('MERCHANT_ID') ?></label>
                        <?php echo $this->form->getInput('payu_merchant_id'); ?>
                        <label><?php echo JText::_('ACOUNT_ID') ?></label>
                        <?php echo $this->form->getInput('payu_account_id'); ?>
                    </div>
                    <div class="stripe">
                        <label><?php echo JText::_('API_KEY') ?></label>
                        <?php echo $this->form->getInput('stripe_api_key'); ?>
                        <label><?php echo JText::_('IMAGE') ?></label>
                        <?php echo $this->form->getInput('stripe_image'); ?>
                        <label><?php echo JText::_('NAME') ?></label>
                        <?php echo $this->form->getInput('stripe_name'); ?>
                        <label><?php echo JText::_('DESCRIPTION') ?></label>
                        <?php echo $this->form->getInput('stripe_description'); ?>
                    </div>
                    <label><?php echo JText::_('ENVIRONMENT') ?></label>
                    <?php echo $this->form->getInput('payment_environment'); ?>
                    <label><?php echo JText::_('RETURN_URL') ?></label>
                    <?php echo $this->form->getInput('return_url'); ?>
                    <label><?php echo JText::_('CANCEL_URL') ?></label>
                    <?php echo $this->form->getInput('cancel_url'); ?>
                    <div class="multiple-payment">
                        <label>
                            <span>
                                <?php echo JText::_('MULTIPLE_PAYMENT') ?>
                            </span>
                            <span class="ba-tooltip">
                                <?php echo JText::_('MULTIPLE_PAYMENT_TOOLTIP') ?>
                            </span>
                        </label>
                        <input type="hidden" name="jform[multiple_payment]" value="0">
                        <?php echo $this->form->getInput('multiple_payment'); ?>
                    </div>  
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="aply-options"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <!--///////////////////////////////////////////////////////////////////////////////// -->
    <!--Title Here -->
    <div class ="row-fluid">
        <div class="span11">
            <?php 
                echo $this->form->getLabel('title');
                echo $this->form->getInput('title');
            ?>
        </div>
        <div class="span1 btn-settings-cell">
            <a href="#" class="ba-btn btn-settings" ><?php echo JText::_('SETTINGS') ?></a>
        </div>
    </div>
    <!--///////////////////////////////////////////////////////////////////////////////// -->
    <div class ="row-fluid">
        <div class="span2 content">
            <!--Text items Here -->
            <div class="text-items">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('EMAIL') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT_AREA') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT_INPUT') ?></p>
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT') ?></p>
                    </div>
                </div>
            </div>
            <!--Checkbox, radio, select items Here -->
            <div class="box-fields">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('CHECKBOX_INLINE') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('CHECKBOX') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('RADIO_INLINE') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('RADIO') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('DROPDOWN') ?></p>
                    </div>
                </div>
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('SELECT_MULTIPLE') ?></p>
                    </div>
                </div>
            </div>
            <!--////////////////////////////////////////////////////////////////// -->
            <!--Buttons items Here -->
            <div class="buttons-fields">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('UPLOAD_BUTTON') ?></p>
                    </div>
                </div>
            </div>
            <div class="slider-field">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('SLIDER') ?></p>
                    </div>
                </div>
            </div>
            <div class="map-field">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('GOOGLE_MAP') ?></p>
                    </div>
                </div>
            </div>
            <div class="image-field">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('IMAGE') ?></p>
                    </div>
                </div>
            </div>
            <div class="datepick-field">
                <div class="tool disabled">
                    <div class="textbox">
                        <p><?php echo JText::_('CALENDAR') ?></p>
                    </div>
                </div>
            </div>
            <div class="page-break disabled">
                <div class="textbox">
                    <p><?php echo JText::_('PAGE_BREAK') ?></p>
                </div>
            </div>
        </div>
        
        <!--///////////////////////////////////////////////////////////////////////////////// -->
        <div class="span7 content editor">
            <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" id="fake-tabs">
                    <li class="ui-state-default ui-corner-top">
                        <a href="#" class="ui-tabs-anchor"><?php echo JText::_('FORM'); ?></a>
                    </li>
                    <li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active">
                        <a href="#" class="ui-tabs-anchor email-builder"><?php echo JText::_('EMAIL'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="ba-content-editor">
                <div class="email-body">
                    <?php echo $this->email; ?>
                </div>
            </div>
            <input class="ba-btn-primary" type="button" value="<?php echo JText::_('NEW_ROW') ?>" id="add-layout">
        </div>
        <div class="span3 content options">
            <div id="myTab">
                <ul>
                    <li><a href="#element-options"><?php echo JText::_('ELEMENT_OPTIONS') ?></a></li>
                    <li><a href="#email-options"><?php echo JText::_('EMAIL_OPTIONS') ?></a></li>
                </ul>
                <div id="element-options">
                    <div class="text-options" style="display:none">
                        <input class="open-editor ba-btn" type="button" value="<?php echo JText::_('OPEN_EDITOR'); ?>">
                        <br><br>
                        <input class="delete-item ba-btn" type="button" value="<?php echo JText::_('DELETE'); ?>">
                    </div>
                    <div class="table-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('BACKGROUND_COLOR'); ?></lable>
                        <input type="text" class="table-bg">
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR'); ?></lable>
                        <input type="text" class="table-color">
                        <lable class="option-label"><?php echo JText::_('BORDER_COLOR'); ?></lable>
                        <input type="text" class="table-border-color">
                        <lable class="option-label"><?php echo JText::_('BORDER_TOP'); ?></lable>
                        <input type="checkbox" class="table-border-top"><br>
                        <lable class="option-label"><?php echo JText::_('BORDER_RIGHT'); ?></lable>
                        <input type="checkbox" class="table-border-right"><br>
                        <lable class="option-label"><?php echo JText::_('BORDER_BOTTOM'); ?></lable>
                        <input type="checkbox" class="table-border-bottom"><br>
                        <lable class="option-label"><?php echo JText::_('BORDER_LEFT'); ?></lable>
                        <input type="checkbox" class="table-border-left"><br>
                        <lable class="option-label"><?php echo JText::_('MARGIN_TOP'); ?></lable>
                        <input type="number" class="table-margin-top">
                        <lable class="option-label"><?php echo JText::_('MARGIN_BOTTOM'); ?></lable>
                        <input type="number" class="table-margin-bottom">
                    </div>
                </div>
                <div id="email-options">
                    <div id="tabs-1">
                        <lable class="option-label"><?php echo JText::_('BACKGROUND_COLOR'); ?></lable>
                        <input type="text" class="email-bg">
                        <lable class="option-label"><?php echo JText::_('WIDTH'); ?>, %</lable>
                        <input type="number" class="email-width">
                    </div>
                    <div id="tabs-2">
                        <p><span><?php echo JText::_('LABEL_OPTIONS') ?></span></p><br>
                        <lable class="option-label"><?php echo JText::_('FONT_SIZE') ?>:</lable>
                        <input class="label-size" type="number">
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_WEIGHT') ?></lable>
                        <div class="weight_radio">
                            <input type="radio" class="lable-weight" name="lable-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                            <input type="radio" class="lable-weight" name="lable-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                        </div>
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR') ?>:</lable>
                        <input class="label-color" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="forms.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>