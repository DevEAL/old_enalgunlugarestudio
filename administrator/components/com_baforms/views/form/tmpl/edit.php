<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$mapSrc = 'https://maps.google.com/maps/api/js?libraries=places&key='.$this->maps_key;

if (JVERSION >= '3.4.0') {
    JHtml::_('behavior.formvalidator');
} else {
    JHtml::_('behavior.formvalidation');
}
JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task) {
        if (task == "form.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
        {
            emailBuild()
            Joomla.submitform(task, document.getElementById("adminForm"));
        }
    };
');
?>
<script type="text/javascript">
    var str = "<div class='btn-wrapper' id='toolbar-integration'>";
    str += "<button class='btn btn-small'><span class='icon-loop'>";
    str += "</span><?php echo JText::_('INTEGRATION'); ?></button></div>";
    jQuery('#toolbar').append(str);
    function checkItems(name, type, place)
    {
        if (name != '') {
            return name;
        } else {
            if (type == 'textarea') {
                if (place != '') {
                    return place;
                } else {
                    return 'Textarea';
                }
            } else if (type == 'textInput') {
                if (place != '') {
                    return place;
                } else {
                    return 'TextInput';
                }
            } else if (type == 'chekInline') {
                return 'ChekInline';
            } else if (type == 'checkMultiple') {
                return 'CheckMultiple';
            } else if (type == 'radioInline') {
                return 'RadioInline';
            } else if (type == 'radioMultiple') {
                return 'RadioMultiple';
            } else if (type == 'dropdown') {
                return 'Dropdown';
            } else if (type == 'selectMultiple') {
                return 'SelectMultiple';
            } else if (type == 'date') {
                return 'Date';
            } else if (type == 'slider') {
                return 'Slider';
            } else if (type == 'email') {
                if (place != '') {
                    return place;
                } else {
                    return 'Email';
                }
            } else if (type == 'address') {
                if (place != '') {
                    return place;
                } else {
                    return 'Address';
                }
            }
        }
    }
    function emailBuild()
    {
        var letter = jQuery("#jform_email_letter").val(),
            emailSettings = jQuery("#jform_email_options").val();
        emailSettings = JSON.parse(emailSettings);
        if (!emailSettings.bg) {
            emailSettings.color = "#111111";
            emailSettings.size = "14";
            emailSettings.weight = "normal";
        }
        if (letter) {
            var div = document.createElement("div");
            div.innerHTML = letter;
            if (jQuery(div).find(".second-table").length == 0) {
                jQuery(div).find("> table").addClass("second-table");
            }
            jQuery(div).find('.system-item').not('.email-cart-field, .email-total-field, .email-ip-field').each(function(){
                var id = jQuery(this).attr('data-item'),
                    item;
                id = id.replace('[item=', '').replace(']', '');
                item = jQuery('.droppad_item').find('> .ba-options[data-id="'+id+'"]')
                if (item.length == 0 || item.closest('.droppad_area').hasClass('condition-area')) {
                    jQuery(this).remove();
                }
            });
            jQuery(".droppad_item").each(function(){
                var type = jQuery.trim(jQuery(this).find("[class*=ba]")[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1]),
                    options = jQuery(this).find("> .ba-options").val(),
                    flag = false,
                    id = jQuery(this).find("> .ba-options").attr("data-id"),
                    str;
                if (jQuery(this).parent().hasClass("condition-area") || type == "image" ||
                    type == "htmltext" || type == "map") {
                    return;
                }
                if (!id) {
                    flag = true;
                    str = "[add_new_item]";
                } else {
                    options = options.split(';');
                    if (!options[2]) {
                        options[2] = ''
                    }
                    name = checkItems(options[0], type, options[2]);
                    if (jQuery(div).find('.system-item[data-item="[item='+id+']"]').length == 0) {
                        flag = true;
                        str = '<div class="droppad_item system-item" data-item="[item=';
                        str += id+']" style="color: '+emailSettings.color+'; font-size: ';
                        str += emailSettings.size+'px; font-weight: '+emailSettings.weight;
                        str += '; line-height: 200%; font-family: Helvetica Neue, Helvetica, Arial;"';
                        str += '>[Field='+name+']</div>';
                    } else {
                        jQuery(div).find('.system-item[data-item="[item='+id+']"]').text('[Field='+name+']');
                    }
                }                
                if (flag) {
                    var tr = document.createElement("tr"),
                        td = document.createElement("td"),
                        table = document.createElement("table"),
                        tbody = document.createElement("tbody"),
                        secondTr = document.createElement("tr"),
                        secondTd2 = document.createElement("td");
                    jQuery(div).find(".second-table > tbody").append(tr);
                    tr.className = "ba-section";
                    jQuery(td).css({
                        "width" : "100%",
                        "padding" : "0 20px"
                    });
                    tr.appendChild(td);
                    jQuery(table).css({
                        "width" : "100%",
                        "background-color" : "rgba(0,0,0,0)",
                        "margin-top" : "10px",
                        "margin-bottom" : "10px",
                        "border-top" : "1px solid #f3f3f3",
                        "border-left" : "1px solid #f3f3f3",
                        "border-right" : "1px solid #f3f3f3",
                        "border-bottom" : "1px solid #f3f3f3"
                    });
                    secondTd2.className = "droppad_area"
                    jQuery(secondTd2).css({
                        "width" : "100%",
                        "padding" : "20px"
                    });
                    secondTd2.innerHTML = str;
                    td.appendChild(table);
                    table.appendChild(tbody);
                    tbody.appendChild(secondTr);
                    secondTr.appendChild(secondTd2);
                }
            });
            if (jQuery("#jform_check_ip").prop("checked")) {
                if (jQuery(div).find(".email-ip-field").length == 0) {
                    var tr = document.createElement("tr"),
                        td = document.createElement("td"),
                        table = document.createElement("table"),
                        tbody = document.createElement("tbody"),
                        secondTr = document.createElement("tr"),
                        secondTd2 = document.createElement("td"),
                        item = document.createElement("div");
                    jQuery(div).find(".second-table > tbody").append(tr);
                    tr.className = "ba-section";
                    item.className = "system-item email-ip-field droppad_item";
                    jQuery(td).css({
                        "width" : "100%",
                        "padding" : "0 20px"
                    });
                    tr.appendChild(td);
                    jQuery(table).css({
                        "width" : "100%",
                        "background-color" : "rgba(0,0,0,0)",
                        "margin-top" : "10px",
                        "margin-bottom" : "10px",
                        "border-top" : "1px solid #f3f3f3",
                        "border-left" : "1px solid #f3f3f3",
                        "border-right" : "1px solid #f3f3f3",
                        "border-bottom" : "1px solid #f3f3f3"
                    });
                    secondTd2.className = "droppad_area"
                    jQuery(secondTd2).css({
                        "width" : "100%",
                        "padding" : "20px"
                    });
                    item.innerHTML = "[Field=Ip Address]";
                    td.appendChild(table);
                    table.appendChild(tbody);
                    tbody.appendChild(secondTr);
                    secondTr.appendChild(secondTd2);
                    secondTd2.appendChild(item);
                    jQuery(item).css({
                        "color" : emailSettings.color,
                        "font-size" : emailSettings.size+"px",
                        "font-weight" : emailSettings.weight,
                        "line-height" : "200%",
                        "font-family" : "Helvetica Neue, Helvetica, Arial"
                    });
                }            
            } else {
                jQuery(div).find(".second-table .email-ip-field").remove()
            }
            if (jQuery("#jform_display_total").prop("checked")) {
                if (jQuery(div).find(".email-total-field").length == 0) {
                    var tr = document.createElement("tr"),
                        td = document.createElement("td"),
                        table = document.createElement("table"),
                        tbody = document.createElement("tbody"),
                        secondTr = document.createElement("tr"),
                        secondTd2 = document.createElement("td"),
                        item = document.createElement("div");
                    jQuery(div).find(".second-table > tbody").append(tr);
                    tr.className = "ba-section";
                    item.className = "system-item email-total-field droppad_item";
                    jQuery(td).css({
                        "width" : "100%",
                        "padding" : "0 20px"
                    });
                    tr.appendChild(td);
                    jQuery(table).css({
                        "width" : "100%",
                        "background-color" : "rgba(0,0,0,0)",
                        "margin-top" : "10px",
                        "margin-bottom" : "10px",
                        "border-top" : "1px solid #f3f3f3",
                        "border-left" : "1px solid #f3f3f3",
                        "border-right" : "1px solid #f3f3f3",
                        "border-bottom" : "1px solid #f3f3f3"
                    });
                    secondTd2.className = "droppad_area"
                    jQuery(secondTd2).css({
                        "width" : "100%",
                        "padding" : "20px"
                    });
                    item.innerHTML = "[Field=Total]";
                    td.appendChild(table);
                    table.appendChild(tbody);
                    tbody.appendChild(secondTr);
                    secondTr.appendChild(secondTd2);
                    secondTd2.appendChild(item);
                    jQuery(item).css({
                        "color" : emailSettings.color,
                        "font-size" : emailSettings.size+"px",
                        "font-weight" : emailSettings.weight,
                        "line-height" : "200%",
                        "font-family" : "Helvetica Neue, Helvetica, Arial"
                    });
                }            
            } else {
                jQuery(div).find(".second-table .email-total-field").remove();
                jQuery(div).find(".second-table .email-cart-field").remove();
            }
            if (jQuery("#jform_display_cart").prop("checked") && jQuery("#jform_display_total").prop("checked")) {
                if (jQuery(div).find(".email-cart-field").length == 0) {
                    var tr = document.createElement("tr"),
                        td = document.createElement("td"),
                        table = document.createElement("table"),
                        tbody = document.createElement("tbody"),
                        secondTr = document.createElement("tr"),
                        secondTd2 = document.createElement("td"),
                        item = document.createElement("div");
                    jQuery(div).find(".second-table > tbody").append(tr);
                    tr.className = "ba-section";
                    item.className = "system-item email-cart-field droppad_item";
                    jQuery(td).css({
                        "width" : "100%",
                        "padding" : "0 20px"
                    });
                    tr.appendChild(td);
                    jQuery(table).css({
                        "width" : "100%",
                        "background-color" : "rgba(0,0,0,0)",
                        "margin-top" : "10px",
                        "margin-bottom" : "10px",
                        "border-top" : "1px solid #f3f3f3",
                        "border-left" : "1px solid #f3f3f3",
                        "border-right" : "1px solid #f3f3f3",
                        "border-bottom" : "1px solid #f3f3f3"
                    });
                    secondTd2.className = "droppad_area"
                    jQuery(secondTd2).css({
                        "width" : "100%",
                        "padding" : "20px"
                    });
                    item.innerHTML = "[Field=Cart]";
                    td.appendChild(table);
                    table.appendChild(tbody);
                    tbody.appendChild(secondTr);
                    secondTr.appendChild(secondTd2);
                    secondTd2.appendChild(item);
                    jQuery(item).css({
                        "color" : emailSettings.color,
                        "font-size" : emailSettings.size+"px",
                        "font-weight" : emailSettings.weight,
                        "line-height" : "200%",
                        "font-family" : "Helvetica Neue, Helvetica, Arial"
                    });
                }            
            } else {
                jQuery(div).find(".second-table .email-cart-field").remove();
            }
            jQuery(div).find('.ba-section').each(function(){
                var row = jQuery(this).find('.droppad_area');
                if (row.length > 0 && row.find('.droppad_item').length == 0 && row[0].innerText != '[add_new_item]') {
                    jQuery(this).remove();
                }
            });
            jQuery("#jform_email_letter").val(jQuery(div).html());
        }
    }
</script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="components/com_baforms/assets/css/ba-admin.css" type="text/css"/>
<div id="theme-rule">
    <style type="text/css" scoped></style>    
</div>
<script src="<?php echo $mapSrc; ?>" type="text/javascript"></script>
<script src="components/com_baforms/assets/js/ba-admin.js" type="text/javascript"></script>
<script src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>
<input type="hidden" id="constant-select" value="<?php echo JText::_('SELECT') ?>">
<div id="integration-dialog" class="modal hide ba-modal-md" style="display:none">
    <div class="modal-header">
        <h3><?php echo JText::_('INTEGRATION') ?></h3>
    </div>
    <div class="modal-body">
        <label class="column google-maps-integration">
            <img src="components/com_baforms/assets/images/google-maps.png">
            <p>Google Maps</p>
        </label>
        <label class="column mailchimp-integration">
            <img src="components/com_baforms/assets/images/mailchimp.png">
            <p>MailChimp</p>
        </label>        
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
    </div>
</div>
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
                <tbody></tbody>
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
    echo $this->form->getInput('email_letter');
    echo $this->form->getInput('email_options');
?>
    <div id="add-item-modal" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <h3><?php echo JText::_('ENTER_VALUE'); ?></h3>
        </div>
        <div class="modal-body">
            <textarea placeholder="<?php echo JText::_('ENTER_VALUE_INLINE'); ?>"></textarea>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="item-aply"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="google-maps-integration-modal" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <h3>Google Maps</h3>
        </div>
        <div class="modal-body">
            <label>API Key</label>
            <input type="text" value="<?php echo $this->maps_key; ?>" id="google_maps_apikey" name="google_maps_apikey">
            <input type="button" class="ba-btn apply-google-maps-api-key" value="<?php echo JText::_('APPLY'); ?>">
            <a href="https://developers.google.com/maps/documentation/javascript/" target="_blank" class="ba-btn-primary">Get your API Key</a>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" data-dismiss="modal"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="mailchimp-integration-dialog" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <h3><?php echo JText::_('MailChimp') ?></h3>
        </div>
        <div class="modal-body">
            <div>
                <label>API key</label>
                <?php echo $this->form->getInput('mailchimp_api_key'); ?>
                <?php echo $this->form->getInput('mailchimp_list_id'); ?>
                <?php echo $this->form->getInput('mailchimp_fields_map'); ?>
                <input type="button" class="ba-btn mailchimp-connect" value="<?php echo JText::_('CONNECT'); ?>">
                <div class="mailchimp-message" style="display: none;"></div>
            </div>
            <div>
                <label><?php echo JText::_('LISTS') ?></label>
                <select class="mailchimp-select-list" disabled>
                    <option value=""><?php echo JText::_('SELECT') ?></option>
                </select>
            </div>
            <label><?php echo JText::_('MATCH_YOUR_FIELDS'); ?></label>
            <div class="merge-fields">
                <div class="mailchimp-email">
                    <label data-field="EMAIL">Email</label>
                    <select class="form-fields" disabled>
                        <option value=""><?php echo JText::_('SELECT'); ?></option>
                    </select>
                </div>
                <div class="mailchimp-fields">
                    
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" data-dismiss="modal"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="delete-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('DELETE_QUESTION') ?></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="delete-aply"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="google-maps-notification-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text">To use the Google Maps Tool you need to enter Google Maps API Key</p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="enter-api-key">Enter API Key</a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="condition-remove-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('ARE_YOU_SURE') ?></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary apply-condition-remove"><?php echo JText::_('APPLY') ?></a>
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="select-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('SELECT_ITEMS') ?></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="conditional-notice-dialog" class="modal hide ba-modal-sm" style="display:none">
        <div class="modal-body">
            <p class="modal-text"><?php echo JText::_('SELECT_CONDITIONAL_ITEM') ?></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn" data-dismiss="modal"><?php echo JText::_('CLOSE') ?></a>
        </div>
    </div>
    <div id="embed-modal" class="modal hide ba-modal-md" style="display:none">
        <div class="modal-header">
            <h3><?php echo JText::_('EDIT_EMBED'); ?></h3>
        </div>
        <div class="modal-body">
            <?php echo $this->form->getInput('submit_embed'); ?>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="embed-aply"><?php echo JText::_('APPLY') ?></a>
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
    <div id="location-dialog" class="modal hide ba-modal-lg" style="display:none">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">x</a>
            <h3><?php echo JText::_('CHOOSE_LOCATION') ?></h3>
        </div>
        <div class="modal-body">
            <div class="row-fluid">
                <div class="span12">
                    <input type="text" id="place" placeholder="<?php echo JText::_('ENTER_LOCATION') ?>">
                </div>
            </div>
            <div class="row-fluid">    
                <div class="span8">
                    <div class="new-map">
                        <div id="location-map" style="width:100%;height:280px">
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <textarea id="mark-description" placeholder="<?php echo JText::_('ENTER_MARKER') ?>"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="ba-btn-primary" id="aply-location"><?php echo JText::_('APPLY') ?></a>
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
                    <div class="mollie">
                        <label><?php echo JText::_('API_KEY') ?></label>
                        <?php echo $this->form->getInput('mollie_api_key'); ?>
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
            <a href="#" class="ba-btn btn-settings"><?php echo JText::_('SETTINGS') ?></a>
        </div>
    </div>
    <!--///////////////////////////////////////////////////////////////////////////////// -->
    <div class ="row-fluid">
        <div class="span2 content">
            <!--Text items Here -->
            <div class="text-items">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('EMAIL') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('EMAIL') ?> *</label>
                        <input type="email" class="ba-email" placeholder=""/>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT_AREA') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('TEXT_AREA') ?></label>
                        <textarea class="ba-textarea" placeholder=""></textarea>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT_INPUT') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('TEXT_INPUT') ?></label>
                        <input type="text" placeholder="" class="ba-textInput"/>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('TEXT') ?></p>
                    </div>
                    <div class='model'>
                        <p class="ba-htmltext"><?php echo JText::_('TEXT') ?></p>
                        <input type="hidden" class="ba-options" value="<?php echo JText::_('TEXT') ?>">
                    </div>
                </div>
            </div>
            <!--Checkbox, radio, select items Here -->
            <div class="box-fields">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('CHECKBOX_INLINE') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('CHECKBOX_INLINE') ?></label>
                        <div class="ba-chekInline">
                            <span>
                                <input type="checkbox" name="checkboxInline" value="option1"/>Option 1
                            </span>
                            <span>
                                <input type="checkbox" name="checkboxInline" value="option2"/>Option 2
                            </span>
                            <span>
                                <input type="checkbox" name="checkboxInline" value="option3"/>Option 3
                            </span>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('CHECKBOX') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('CHECKBOX') ?></label>
                        <div class="ba-checkMultiple">
                            <span>
                                <input type="checkbox" name="checkboxMultiple" value="option1"/>Option 1<br/>
                            </span>
                            <span>
                                <input type="checkbox" name="checkboxMultiple" value="option2"/>Option 2<br/>
                            </span>
                            <span>
                                <input type="checkbox" name="checkboxMultiple" value="option3"/>Option 3<br/>
                            </span>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('RADIO_INLINE') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('RADIO_INLINE') ?></label>
                        <div class="ba-radioInline">
                            <span>
                                <input type="radio" name="radioInline" value="option1"/>Option 1
                            </span>
                            <span>
                                <input type="radio" name="radioInline" value="option2"/>Option 2
                            </span>
                            <span>
                                <input type="radio" name="radioInline" value="option3"/>Option 3
                            </span>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('RADIO') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('RADIO') ?></label>
                        <div class="ba-radioMultiple">
                            <span>
                                <input type="radio" name="radioMultiple" value="option1"/>Option 1<br>
                            </span>
                            <span>
                                <input type="radio" name="radioMultiple" value="option2"/>Option 2<br>
                            </span>
                            <span>
                                <input type="radio" name="radioMultiple" value="option3"/>Option 3<br>
                            </span>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('DROPDOWN') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('DROPDOWN') ?></label>
                        <select class="ba-dropdown">
                            <option data-dropdown-select="true"><?php echo JText::_('SELECT'); ?></option>
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                            <option value="option3">Option 3</option>
                        </select>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('SELECT_MULTIPLE') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('SELECT_MULTIPLE') ?></label>
                        <select size="3" class="ba-selectMultiple">
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                            <option value="option3">Option 3</option>
                        </select>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <!--////////////////////////////////////////////////////////////////// -->
            <!--Buttons items Here -->
            <div class="buttons-fields">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('UPLOAD_BUTTON') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('UPLOAD_BUTTON') ?></label>
                        <span><?php echo JText::_('MAX_FILE_SIZE') ?>, 5mb (jpg, jpeg, png, pdf, doc)</span><br>
                        <input class="ba-upload" id="upload_button" type="file"/>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <div class="slider-field">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('SLIDER') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('SLIDER') ?></label>
                        <div class="ba-slider"></div>
                        <input type="hidden" value="">
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <div class="map-field">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('GOOGLE_MAP') ?></p>
                    </div>
                    <div class='model'>
                        <div class="ba-map" style="width:100%;height:400px"></div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('ADDRESS') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('ADDRESS') ?></label>
                        <div class="container-icon">
                            <input type="text" class="ba-address" >
                            <div class="icons-cell">
                                <i class="zmdi zmdi-pin"></i>
                            </div>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <div class="image-field">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('IMAGE') ?></p>
                    </div>
                    <div class='model'>
                        <img class="ba-image" src="<?php echo JUri::root() ?>/components/com_baforms/assets/images/image-placeholder.jpg">
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <div class="datepick-field">
                <div class="tool">
                    <div class="textbox">
                        <p><?php echo JText::_('CALENDAR') ?></p>
                    </div>
                    <div class='model'>
                        <label class="label-item"><?php echo JText::_('CALENDAR') ?></label>
                        <div class="ba-date">
                            <div class="container-icon">
                                <input type="text" value="<?php echo date("d F Y"); ?>">
                                <div class="icons-cell">
                                    <i class="zmdi zmdi-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="ba-options">
                    </div>
                </div>
            </div>
            <div class="page-break">
                <div class="textbox">
                    <p><?php echo JText::_('PAGE_BREAK') ?></p>
                </div>
                <div class='model'>
                    <div>
                        <div class="ba-edit-row" style="z-index: 209;">
                            <a class="zmdi zmdi-arrows"></a>
                            <a href="#" class="delete-layout zmdi zmdi-close"></a>
                        </div>
                        <div class="ba-prev">
                            <input type="button" class="btn-prev" value="<?php echo JText::_('PREV') ?>"
                                   style="font-size:14px; font-weight:normal; width:80px;
                                          background-color: #f0f0f0; border-radius: 3px;
                                          height: 40px; color:#111111">
                            <input type="hidden" value="<?php echo JText::_('PREV') ?>;80;40;f0f0f0;111111;14;normal;3">
                        </div>
                        <div class="ba-next">
                            <input type="button" class="btn-next" value="<?php echo JText::_('NEXT') ?>"
                                   style="font-size:14px; font-weight:normal; width:80px;
                                          background-color: #02adea; border-radius: 3px;
                                          height: 40px; color:#fafafa">
                            <input type="hidden" value="<?php echo JText::_('NEXT') ?>;80;40;02adea;fafafa;14;normal;3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!--///////////////////////////////////////////////////////////////////////////////// -->
        <div class="span7 content editor">
            <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" id="fake-tabs">
                    <li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active">
                        <a href="#" class="ui-tabs-anchor"><?php echo JText::_('FORM'); ?></a>
                    </li>
                    <li class="ui-state-default ui-corner-top">
                        <a href="#" class="ui-tabs-anchor email-builder"><?php echo JText::_('EMAIL'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="ba-content-editor">
                <div class="form-style" style="border: 1px solid #f3f3f3; background-color: #ffffff; border-radius: 2px">
                    <div class="title-form">
                        <h1 style="font-size:26px; font-weight:normal; text-align:left; color:#111111">
                            <span class="title"><?php echo JText::_('NEW_FORM') ?></span>
                            <span class="ba-tooltip"><?php echo JText::_('CLICK_TO_EDIT') ?></span>
                        </h1>
                    </div>
                    <div class="span12" id="content-section">
                        <?php if (!isset($this->item->id) || $this->item->id == '0') {?>
                        <div class="row-fluid">
                            <div id="bacolumn-1" class="span12 droppad_area items">
                                <div class="ba-edit-row">
                                    <a class="zmdi zmdi-arrows"></a>
                                    <a href="#" class="delete-layout zmdi zmdi-close"></a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="baforms-cart">
                        <div class="product-cell ba-cart-headline">
                            <div class="product"><?php echo JText::_('ITEM'); ?></div>
                            <div class="price"><?php echo JText::_('PRICE'); ?></div>
                            <div class="quantity"><?php echo JText::_('QUANTITY'); ?></div>
                            <div><?php echo JText::_('TOTAL'); ?></div>
                            <div class="remove-item"></div>
                        </div>
                        <div class="product-cell">
                            <div class="product"><?php echo JText::_('FORM'); ?></div>
                            <div class="price">$36</div>
                            <div class="quantity"><input type="number" value="1" min="1" step="1" data-cost="36"></div>
                            <div class="total">$36</div>
                            <div class="remove-item"><i class="zmdi zmdi-close"></i></div>
                        </div>
                    </div>
                    <div class="ba-total-price">
                        <p><?php echo JText::_('TOTAL') ?>: <span>0</span></p>
                    </div>
                    <div class="btn-align" style="text-align:left">
                        <input class="ba-btn-submit" id="baform-0" type="button" value="Submit"
                               style="width:15%; height:40px; background-color: #02adea; color: #fafafa; font-size:14px;
                                      font-weight:normal; border-radius:3px; border: none;"/>
                        <span class="ba-tooltip"><?php echo JText::_('CLICK_TO_EDIT') ?></span>
                    </div>
                </div>
            </div>
            <input class="ba-btn-primary" type="button" value="<?php echo JText::_('NEW_ROW') ?>" id="add-layout">
        </div>
        <div class="span3 content options">
            <div id="myTab">
                <ul>
                    <li><a href="#options"><?php echo JText::_('ELEMENT_OPTIONS') ?></a></li>
                    <li><a href="#form-options"><?php echo JText::_('FORM_OPTIONS') ?></a></li>
                </ul>
                <div id="options" style="display:none">
                    <div class="image-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('SELECT_IMAGE') ?>:</lable>
                        <?php echo $this->form->getInput('select_image'); ?>
                        <lable class="option-label"><?php echo JText::_('ALIGNMENT') ?></lable>
                        <select class="image-align">
                            <option value="left"><?php echo JText::_('LEFT') ?></option>
                            <option value="center"><?php echo JText::_('CENTER') ?></option>
                            <option value="right"><?php echo JText::_('RIGHT') ?></option>
                        </select><br>
                        <lable class="option-label"><?php echo JText::_('WIDTH') ?>, %:</lable>
                        <input type="number" class="ba-width-number-image ba-slider-input" value="0"><br>
                        <lable class="option-label"><?php echo JText::_('ALT') ?></lable>
                        <input type="text" class="image-alt"><br>
                        <lable class="option-label"><?php echo JText::_('ENABLE_LIGHTBOX') ?></lable>
                        <input type="checkBox" class="enable-lightbox"><br>
                        <lable class="option-label"><?php echo JText::_('LIGHTBOX_BG') ?></lable>
                        <input type="text" class="image-lightbox-bg">
                    </div>
                    <div id="text-lable" style="display:none">
                        <lable class="option-label"><?php echo JText::_('LABEL_TEXT') ?></lable>
                        <input type="text" name="label"/><br/>
                    </div>
                    <div id="icons-select" style="display:none">
                        <lable class="option-label"><?php echo JText::_('ICON') ?></lable>
                        <input class="ba-btn" type="button" value="<?php echo JText::_('SELECT'); ?>">
                        <a href="#" class="clear-icon zmdi zmdi-close ba-btn"></a>
                    </div>
                    <div id="text-description" style="display:none">
                        <lable class="option-label"><?php echo JText::_('DESCRIPTION') ?></lable>
                        <input type="text" name="description"/><br/>
                    </div>
                    <div id="place-hold" style="display:none">
                        <lable class="option-label"><?php echo JText::_('PLACEHOLDER') ?></lable>
                        <input type="text" name="place"/><br/>
                    </div>
                    <div id="input-type" style="display:none">
                        <lable class="option-label"><?php echo JText::_('INPUT_TYPE') ?></lable>
                        <select class="input-type">
                            <option value="regular"><?php echo JText::_('REGULAR') ?></option>
                            <option value="number"><?php echo JText::_('NUMBER') ?></option>
                            <option value="calculation"><?php echo JText::_('CALCULATION') ?></option>
                        </select>
                    </div>
                    <div id="cheсk-options" style="display:none">
                        <div class="items-control-options">
                            <label class="button-alignment ba-btn">
                                <input type="radio" class="item-add">
                                <i class="zmdi zmdi-playlist-plus"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('ADD_VALUE'); ?>
                                </span>
                            </label>
                            <label class="button-alignment ba-btn">
                                <input type="radio" class="condition-logic">
                                <i class="zmdi zmdi-arrow-split"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('CONDITION_LOGIC'); ?>
                                </span>
                            </label>
                            <label class="button-alignment ba-btn">
                                <input type="radio" class="select-default">
                                <i class="zmdi zmdi-check"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('JTOOLBAR_DEFAULT'); ?>
                                </span>
                            </label>
                            <label class="button-alignment ba-btn">
                                <input type="radio" class="item-delete">
                                <i class="zmdi zmdi-close"></i>
                                <span class="ba-tooltip">
                                    <?php echo JText::_('DELETE'); ?>
                                </span>
                            </label>
                            <input type="hidden" id="show_hidden" value="<?php echo JText::_('SHOW_HIDDEN_FIELDS') ?>">
                        </div>
                        <table>
                            <tbody class="items-list"></tbody>
                        </table>
                    </div>
                    <div id="textarea-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('MIN_HEIGHT') ?></lable>
                        <input type="number">
                    </div>
                    <div id="select-size" style="display:none">
                        <label class="option-label"><?php echo JText::_('SELECT_HEIGHT') ?></label>
                        <input type="number">
                    </div>
                    <div id="required" style="display:none">
                        <lable class="option-label"><?php echo JText::_('REQUIRED_FIELD') ?></lable>
                        <input type="checkbox" name="required" value="option1"/>
                    </div>
                    <div id="button-otions" style="display:none">
                        <lable class="option-label"><?php echo JText::_('LABEL') ?></lable>
                        <input type="text" name="name"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_WIDTH') ?>, %:</lable>
                        <input type="number" id="width"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_HEIGHT') ?></lable>
                        <input type="number" id="height"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_ALIGNMENT') ?></lable>
                        <select class="button-alignment">
                            <option value="left"><?php echo JText::_('LEFT') ?></option>
                            <option value="center"><?php echo JText::_('CENTER') ?></option>
                            <option value="right"><?php echo JText::_('RIGHT') ?></option>
                        </select><br>
                        <lable class="option-label"><?php echo JText::_('BUTTON_BACKGROUND') ?></lable>
                        <input type="text" id="bg-color"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_COLOR') ?></lable>
                        <input type="text" id="text-color"/><br/>
                        <div>
                        <lable class="option-label"><?php echo JText::_('TITLE_SIZE') ?></lable>
                        <input type="number" id="font-size">
                        </div>
                        <lable class="option-label"><?php echo JText::_('TITLE_WEIGHT') ?></lable>
                        <div class="weight_radio">
                            <input type="radio" name="font-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                            <input type="radio" name="font-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                        </div>
                        <p>
                            <label class="option-label"><?php echo JText::_('BORDER_RADIUS') ?>:</label>
                            <input type="number" id="radius" value="3">
                        </p>
                        <lable class="option-label">
                            <?php echo JText::_('EMBED_CODE') ?>
                        </lable>
                        <input type="button" value="<?php echo JText::_('EMBED') ?>" class="submit-embed ba-btn">
                    </div>
                    <div id="breaker-options" style="display: none">
                        <lable class="option-label"><?php echo JText::_('LABEL') ?></lable>
                        <input type="text" name="name" id="break-label"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_WIDTH') ?>, px:</lable>
                        <input type="number" id="break-width"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_HEIGHT') ?></lable>
                        <input type="number" id="break-height"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_BACKGROUND') ?></lable>
                        <input type="text" id="break-bg-color"/><br/>
                        <lable class="option-label"><?php echo JText::_('BUTTON_COLOR') ?></lable>
                        <input type="text" id="break-text-color"/><br/>
                        <lable class="option-label"><?php echo JText::_('TITLE_SIZE') ?></lable>
                        <input type="number" id="break-font-size">
                        <lable class="option-label"><?php echo JText::_('TITLE_WEIGHT') ?></lable>
                        <div class="weight_radio">
                            <input type="radio" name="break-font-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                            <input type="radio" name="break-font-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                        </div>
                        <label class="option-label"><?php echo JText::_('BORDER_RADIUS') ?>:</label>
                        <input type="number" id="break-radius" value="3">
                    </div>
                    <div id="slider-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('MIN') ?></lable>
                        <input type="text" id="min" value="0"/><br/>
                        <lable class="option-label"><?php echo JText::_('MAX') ?></lable>
                        <input type="text" id="max" value="50"/><br/>
                        <lable class="option-label"><?php echo JText::_('STEP') ?></lable>
                        <input type="text" id="step"><br/>
                    </div>
                    <div id="html-options" style="display:none">
                        <input type="button" value="<?php echo JText::_('OPEN_EDITOR') ?>" class="ba-btn" id="html-button">
                        <br>
                        <br>
                    </div>
                    <div id="upload-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('MAX_SIZE') ?></lable>
                        <input type="number" id="upl-size">
                        <lable class="option-label"><?php echo JText::_('FILE_TYPES') ?></lable>
                        <input type="text" id="upl-types">
                    </div>
                    <div id="email-checkbox" style="display:none">
                        <lable class="option-label"><?php echo JText::_('REQUIRED_FIELD') ?></lable>
                        <input type="checkbox" disabled checked value="option1"/>
                    </div>
                    <div id="map-options" style="display:none">
                        <input class="ba-btn" id="map-location" type="button" value="<?php echo JText::_('CHOOSE_LOCATION') ?>" /><br/>
                        <lable class="option-label"><?php echo JText::_('HEIGHT') ?></lable>
                        <input value="400" type="text" id="map-height">
                        <lable class="option-label"><?php echo JText::_('MAP_CONTROLS') ?></lable>
                        <input type="checkbox" checked name="controls" value="1"><br>
                        <lable class="option-label"><?php echo JText::_('DISPLAY_INFOBOX') ?></lable>
                        <input type="checkbox" name="infobox" value="1"><br>
                        <label class="option-label"><?php echo JText::_('SCROLL_ZOOMING') ?></label>
                        <input type="checkbox" class="map-zooming"><br>
                        <label class="option-label"><?php echo JText::_('DRAGGABLE_MAP') ?></label>
                        <input type="checkbox" class="map-draggable"><br>
                        <lable class="option-label"><?php echo JText::_('UPLOAD_MARKER') ?>:</lable>
                        <?php echo $this->form->getInput('marker_image'); ?>
                        <input type="hidden" value="" id="location-options">
                        <input type="hidden" value="" id="marker-position">
                    </div>
                    <div id="delete-buttons" style="display:none">
                        <input type="hidden" value="" id="item-id">
                        <input type="hidden" value="" id="type-item">
                        <input class="ba-btn" id="delete-item" type="button" value="<?php echo JText::_('DELETE') ?>"/>
                    </div>
                    <div class="title-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('FONT_SIZE') ?>:</lable>
                        <input class="title-size" type="number" value="26"><br/>
                        <lable class="option-label"><?php echo JText::_('FONT_WEIGHT') ?></lable>
                        <div class="weight_radio">
                            <input type="radio" name="title-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                            <input type="radio" name="title-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                        </div>
                        <lable class="option-label"><?php echo JText::_('FONT_ALIGNMENT') ?></lable>
                        <select class="title-alignment">
                            <option value="left"><?php echo JText::_('LEFT') ?></option>
                            <option value="center"><?php echo JText::_('CENTER') ?></option>
                            <option value="right"><?php echo JText::_('RIGHT') ?></option>
                        </select>
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR') ?></lable>
                        <input type="text" id="title-color"><br/>
                    </div>
                    <div class="total-options" style="display:none">
                        <lable class="option-label"><?php echo JText::_('LABEL') ?>:</lable>
                        <input class="total-label" type="text"><br/>
                        <lable class="option-label"><?php echo JText::_('ALIGNMENT') ?></lable>
                        <select class="total-alignment">
                            <option value="left"><?php echo JText::_('LEFT') ?></option>
                            <option value="center"><?php echo JText::_('CENTER') ?></option>
                            <option value="right"><?php echo JText::_('RIGHT') ?></option>
                        </select>
                    </div>
                </div>
                <div id="form-options">
                    <p><span><?php echo JText::_('FORM_OPTIONS') ?></span></p><br>
                    <div id="tabs-1">
                        <lable class="option-label"><?php echo JText::_('FORM_WIDTH') ?></lable>
                        <input id="form-width" type="number" value="100">
                        <br>
                        <lable class="option-label"><?php echo JText::_('BACKGROUND_COLOR') ?></lable>
                        <input id="form-bgcolor" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('BORDER_COLOR') ?></lable>
                        <input id="form-borcolor" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('BORDER_RADIUS') ?>:</lable>
                        <input id="form-radius" type="number" value="2">
                        <br>
                        <lable class="option-label"><?php echo JText::_('THEME_COLOR') ?></lable>
                        <?php echo $this->form->getInput('theme_color'); ?>
                        <input class="theme-color" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('CLASS_SUFFIX') ?></lable>
                        <input id="form-class" type="text">
                        <br>
                    </div>
                    <p><span><?php echo JText::_('LABEL_OPTIONS') ?></span></p><br>
                    <div id="tabs-2">
                        <lable class="option-label"><?php echo JText::_('FONT_SIZE') ?>:</lable>
                        <input id="label-size" type="number" value="13">
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_WEIGHT') ?></lable>
                        <div class="weight_radio">
                            <input type="radio" name="lable-weight" value ="normal"><?php echo JText::_('NORMAL') ?>
                            <input type="radio" name="lable-weight" value ="bold"><?php echo JText::_('BOLD') ?>
                        </div>
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR') ?>:</lable>
                        <input id="label-color" type="text">
                    </div>
                    <p><span><?php echo JText::_('INPUTS_OPTIONS') ?></span></p><br>
                    <div id="tabs-3">
                        <lable class="option-label"><?php echo JText::_('INPUTS_HEIGHT') ?></lable>
                        <input id="input-height" type="number" value="30">
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_SIZE') ?>:</lable>
                        <input id="input-size" type="number" value="13">
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR') ?>:</lable>
                        <input id="input-color" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('BACKGROUND_COLOR') ?></lable>
                        <input id="input-bgcolor" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('BORDER_COLOR') ?></lable>
                        <input id="input-borcolor" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('INPUTS_RADIUS') ?></lable>
                        <input id="input-radius" type="number" value="2">
                    </div>
                    <p><span><?php echo JText::_('ICONS_OPTIONS') ?></span></p><br>
                    <div id="tabs-4">
                        <lable class="option-label"><?php echo JText::_('FONT_COLOR') ?></lable>
                        <input id="icons-color" type="text">
                        <br>
                        <lable class="option-label"><?php echo JText::_('FONT_SIZE') ?></lable>
                        <input id="icons-size" type="number" value="2">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="forms.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>