/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


function initMap()
{
    jQuery('.droppad_item').find('.ba-map').each(function(){
        var options = jQuery(this).parent().find('> .ba-options').val(),
            option,
            image;
        options = options.split(';');
        if (options[0] != '') {
            option = JSON.parse(options[0]);
            if (typeof(option.center) == 'string') {
                option.center = option.center.split(',');
                option.center = {
                    lat : option.center[0]*1,
                    lng : option.center[1]*1
                }
            }
        } else {
            option = {
                center : {
                    lat : 42.345573,
                    lng : -71.098326
                },
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                zoomControl: false,
                disableDefaultUI: true,
                disableDoubleClickZoom: true,
                zoom: 14
            }
        }
        if (options[7] != '') {
            image = '../'+options[7];
        } else {
            image = options[7];
        }
        var content = options[2],
            flag = options[5],
            map = new google.maps.Map(jQuery(this)[0], option),
            marker = '';
        if (options[1] != '') {
            var mark = JSON.parse(options[1]);
            var keys = [];
            for (var key in mark) {
                keys.push(key);
            }
            marker = new google.maps.Marker({
                position: {
                    lat : mark[keys[0]]*1,
                    lng : mark[keys[1]]*1
                },
                map: map,
                icon : image
            });
            if (content != '') {
                var infowindow = new google.maps.InfoWindow({
                    content : content
                });
                if (flag == 1) {
                    infowindow.open(map, marker);
                }
                marker.addListener('click', function(event){
                    infowindow.open(map, marker);
                });
            }
        }
    });
}

jQuery(document).ready(function(){

    var formFields = new Array(),
        google_maps = jQuery('#theme-rule').next()[0],
        delay = '',
        google_maps_apikey = jQuery('#google_maps_apikey').val(),
        emailFields,
        mailchimpMap = JSON.parse(jQuery('#jform_mailchimp_fields_map').val());

    jQuery('.google-maps-integration').on('click', function(){
        jQuery('#google-maps-integration-modal').modal();
    });

    jQuery('#enter-api-key').on('click', function(event){
        event.preventDefault();
        jQuery('#google-maps-notification-dialog').modal('hide');
        jQuery('#google-maps-integration-modal').modal();
    });

    jQuery('.apply-google-maps-api-key').on('click', function(){
        google_maps_apikey = jQuery('#google_maps_apikey').val();
        clearTimeout(delay);
        delete(window.google)
        jQuery(google_maps).remove();
        google_maps = document.createElement('script');
        jQuery('body').append(google_maps);
        google_maps.src = 'https://maps.google.com/maps/api/js?libraries=places&callback=initMap&key='+google_maps_apikey;
        jQuery('#google-maps-integration-modal').modal('hide');
    });

    jQuery('.mailchimp-fields, .mailchimp-email').on('change', '.form-fields', function(){
        var key = jQuery(this).prev().attr('data-field');
        mailchimpMap[key] = jQuery(this).val();
        jQuery('#jform_mailchimp_fields_map').val(JSON.stringify(mailchimpMap));
    });
    jQuery('#integration-dialog').on('show', function(){
        var options,
            label;
        formFields = [];
        emailFields = [];
        jQuery('#content-section .droppad_item').each(function(){
            if (jQuery(this).parent().hasClass('condition-area')) {
                return;
            }
            if (jQuery(this).find('.ba-email').length > 0) {
                options = jQuery(this).find(' > .ba-options').val().split(';');
                if (options[0]) {
                    label = options[0];
                } else if (options[2]) {
                    label = options[2];
                } else {
                    label = ''
                }
                var obj = {
                    'label' : label,
                    'id' : jQuery(this).attr('id')
                }
                emailFields.push(obj);
            } else if (jQuery(this).find('.ba-textarea').length > 0 ||
                       jQuery(this).find('.ba-textInput').length > 0 ||
                       jQuery(this).find('.ba-address').length > 0) {
                options = jQuery(this).find(' > .ba-options').val().split(';');
                if (options[0]) {
                    label = options[0];
                } else if (options[2]) {
                    label = options[2];
                } else {
                    label = ''
                }
                var obj = {
                    'label' : label,
                    'id' : jQuery(this).attr('id')
                }
                formFields.push(obj);
            } else if (jQuery(this).find('.ba-radioInline').length > 0 ||
                       jQuery(this).find('.ba-radioMultiple').length > 0 ||
                       jQuery(this).find('.ba-dropdown').length > 0 ||
                       jQuery(this).find('.ba-date').length > 0) {
                options = jQuery(this).find(' > .ba-options').val().split(';');
                if (options[0]) {
                    label = options[0];
                } else {
                    label = ''
                }
                var obj = {
                    'label' : label,
                    'id' : jQuery(this).attr('id')
                }
                formFields.push(obj);
            }
        });
        var api_key = jQuery('#jform_mailchimp_api_key').val(),
            list_id = jQuery('#jform_mailchimp_list_id').val();
        if (api_key && list_id) {
            jQuery('.mailchimp-message').hide();
            jQuery('.mailchimp-fields').empty();
            jQuery.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_baforms&view=form&task=form.connectMailChimp",
                data:{
                    api_key: api_key,
                },
                success: function(msg){
                    var msg = JSON.parse(msg);
                    if (msg.message) {
                        msg = msg.message;
                        msg = JSON.parse(msg);
                        var len = msg.lists.length,
                            str ='';
                        for (var i = 0; i < len; i++) {
                            str += '<option value="'+msg.lists[i].id+'"';
                            if (msg.lists[i].id == list_id) {
                                str += ' selected';
                            }
                            str += '>'+msg.lists[i].name+'</option>';
                        }
                        jQuery('.mailchimp-select-list option').each(function(){
                            if (jQuery(this).val() != '') {
                                jQuery(this).remove();
                            }
                        });
                        jQuery('.mailchimp-select-list').append(str).removeAttr('disabled');
                    }
                }
            });
            jQuery.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_baforms&view=form&task=form.getMailChimpFields",
                data:{
                    api_key: api_key,
                    list_id: list_id,
                },
                success: function(msg){
                    var msg = JSON.parse(msg);
                    if (msg.message) {
                        msg = msg.message;
                        msg = JSON.parse(msg);
                        var fields = msg.merge_fields;
                        fields.forEach(function(item, i, arr){
                            var str = '<label data-field="'+item.tag+'">'+item.name+'</label>';
                            str += '<select class="form-fields"><option value="">';
                            str += jQuery('#constant-select').val()+'</option></select>';
                            jQuery('.mailchimp-fields').append(str);
                        });
                        jQuery('.mailchimp-email select').removeAttr('disabled');
                        jQuery('.mailchimp-fields select option').each(function(){
                            if (jQuery(this).val() != '') {
                                jQuery(this).remove();
                            }
                        });
                        emailFields.forEach(function(item, i , arr){
                            var str = '<option value="'+item.id+'">'+item.label+'</option>';
                            jQuery('.mailchimp-email select').append(str);
                        });
                        var str = '';
                        formFields.forEach(function(item, i , arr){
                            str += '<option value="'+item.id+'">'+item.label+'</option>';
                        });
                        jQuery('.mailchimp-fields select').append(str);
                        jQuery('.merge-fields label').each(function(){
                            var key = jQuery(this).attr('data-field');
                            jQuery(this).next().find('option').each(function(){
                                if (jQuery(this).val() == mailchimpMap[key]) {
                                    jQuery(this).attr('selected', true);
                                } else {
                                    jQuery(this).removeAttr('selected');
                                }
                            });
                        });
                    }
                }
            });
        }
    });
    jQuery('#toolbar-integration button').on('click', function(){
        jQuery('#integration-dialog').modal();
    });
    jQuery('.mailchimp-integration').on('click', function(){
        jQuery('#mailchimp-integration-dialog').modal();
    });
    jQuery('.mailchimp-connect').on('click', function(){
        var api_key = jQuery('#jform_mailchimp_api_key').val();
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_baforms&view=form&task=form.connectMailChimp",
            data:{
                api_key: api_key,
            },
            success: function(msg){
                var msg = JSON.parse(msg);
                if (!msg.data) {
                    jQuery('.mailchimp-message').show().text(msg.message);
                    jQuery('.mailchimp-select-list option').each(function(){
                        if (jQuery(this).val() != '') {
                            jQuery(this).remove();
                        }
                    });
                    jQuery('.mailchimp-select-list').attr('disabled', true);
                    jQuery('.mailchimp-fields').empty();
                    return;
                }
                if (msg.message) {
                    msg = msg.message;
                    msg = JSON.parse(msg);
                    if (msg.status) {
                        jQuery('.mailchimp-message').show().text(msg.title);
                        jQuery('.mailchimp-select-list option').each(function(){
                            if (jQuery(this).val() != '') {
                                jQuery(this).remove();
                            }
                        });
                        jQuery('.mailchimp-select-list').attr('disabled', true);
                        jQuery('.mailchimp-fields').empty();
                        return;
                    } else {
                        jQuery('.mailchimp-message').hide();
                    }
                    var len = msg.lists.length,
                        str ='';
                    for (var i = 0; i < len; i++) {
                        str += '<option value="'+msg.lists[i].id+'">'+msg.lists[i].name+'</option>';
                    }
                    mailchimpMap = {};
                    jQuery('#jform_mailchimp_fields_map').val(JSON.stringify(mailchimpMap));
                    jQuery('.mailchimp-select-list option').each(function(){
                        if (jQuery(this).val() != '') {
                            jQuery(this).remove();
                        }
                    });
                    jQuery('.mailchimp-select-list').append(str).removeAttr('disabled');
                } else {
                    jQuery('.mailchimp-select-list option').each(function(){
                        if (jQuery(this).val() != '') {
                            jQuery(this).remove();
                        }
                    });
                    jQuery('.mailchimp-select-list').attr('disabled', true);
                    jQuery('.mailchimp-fields').empty();
                }
            }
        });
    });
    jQuery('.mailchimp-select-list').on('change', function(){
        var id = jQuery(this).val(),
            api_key = jQuery('#jform_mailchimp_api_key').val();
        jQuery('#jform_mailchimp_list_id').val(id);
        jQuery('.mailchimp-fields').empty();
        mailchimpMap = {};
        jQuery('#jform_mailchimp_fields_map').val(JSON.stringify(mailchimpMap));
        if (id) {
            jQuery.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_baforms&view=form&task=form.getMailChimpFields",
                data:{
                    api_key: api_key,
                    list_id: id,
                },
                success: function(msg){
                    var msg = JSON.parse(msg);
                    if (msg.message) {
                        msg = msg.message;
                        msg = JSON.parse(msg);
                        var fields = msg.merge_fields;
                        fields.forEach(function(item, i, arr){
                            var str = '<label data-field="'+item.tag+'">'+item.name+'</label>';
                            str += '<select class="form-fields"><option value="">';
                            str += jQuery('#constant-select').val()+'</option></select>';
                            jQuery('.mailchimp-fields').append(str);
                        });
                        jQuery('.mailchimp-email select').removeAttr('disabled');
                        jQuery('.mailchimp-fields select option').each(function(){
                            if (jQuery(this).val() != '') {
                                jQuery(this).remove();
                            }
                        });
                        var str = '';
                        formFields.forEach(function(item, i , arr){
                            str += '<option value="'+item.id+'">'+item.label+'</option>';
                        });
                        jQuery('.mailchimp-fields select').append(str);
                    }
                }
            });
        } else {
            jQuery('.mailchimp-email select').attr('disabled', true);
        }
    });

    var hidden_fields = jQuery('#show_hidden').val();

    jQuery('#cheсk-options .items-control-options, #cheсk-options .items-list')
        .on('mouseenter', '.button-alignment', function(){
        var coord = this.getBoundingClientRect(),
            top = coord.top,
            left = coord.left;
        left = left + (coord.right - coord.left) / 2;
        jQuery(this).find('.ba-tooltip').css({
            'top' : top+'px',
            'left' : left+'px'
        })
    });
    jQuery('.title-form h1, .btn-align').on('mouseenter', '.title, .ba-btn-submit', function(){
        var coord = this.getBoundingClientRect(),
            top = coord.top,
            left = coord.left;
        left = left + (coord.right - coord.left) / 2;
        jQuery(this).parent().find('.ba-tooltip').css({
            'top' : top+'px',
            'left' : left+'px'
        })
    });
    jQuery('.ba-tooltip').each(function(){
        jQuery(this).parent().find('span').not(this).on('mouseenter', function(){
            var coord = this.getBoundingClientRect(),
                top = coord.top,
                left = coord.left;
            left = left + (coord.right - coord.left) / 2;
            jQuery(this).parent().find('.ba-tooltip').css({
                'top' : top+'px',
                'left' : left+'px'
            })
        });
    });

    jQuery('#jform_currency_position').on('change', function(){
        var position = jQuery(this).val(),
            symbol = jQuery('#jform_currency_symbol').val(),
            options,
            childs,
            option,
            str;
        jQuery('.droppad_item [class*="ba-radio"], .droppad_item [class*="ba-che"]').each(function(){
            options = jQuery(this).closest('.droppad_item').find('> .ba-options').val().split(';');
            option = options[2];
            childs = jQuery(this).find('span');
            option = option.replace(new RegExp('"','g'), '');
            option = option.split("\\n");
            for (var i = 0; i < option.length; i++) {
                option[i] = option[i].split('====');
                str = option[i][0];
                if (option[i][1]) {
                    str += ' - '
                    if (position == 'before') {
                        str += symbol;
                    }
                    str += jQuery.trim(option[i][1]);
                    if (position != 'before') {
                        str += symbol;
                    }
                    childs[i].childNodes[1].data = str;
                }
            }
        });
        jQuery('.droppad_item .ba-dropdown, .droppad_item .ba-selectMultiple').each(function(){
            options = jQuery(this).closest('.droppad_item').find('> .ba-options').val().split(';');
            option = options[2];
            childs = jQuery(this).find('option');
            option = option.replace(new RegExp('"','g'), '');
            option = option.split("\\n");
            for (var i = 0; i < option.length; i++) {
                option[i] = option[i].split('====');
                str = option[i][0];
                if (option[i][1]) {
                    str += ' - '
                    if (position == 'before') {
                        str += symbol;
                    }
                    str += jQuery.trim(option[i][1]);
                    if (position != 'before') {
                        str += symbol;
                    }
                    childs[i].childNodes[0].data = str;
                }
            }
        });
    });

    jQuery('#fake-tabs li a').on('click', function(event){
        event.preventDefault();
        if (jQuery(this).hasClass('email-builder')) {
            Joomla.submitbutton('form.buildEmail');
        }
    });

    jQuery('#jform_email_recipient').attr('placeholder', 'email-1@email.com, email-2@email.com...');
    jQuery('#jform_sender_email').attr('placeholder',"email@email.com");
    
    var CKE = CKEDITOR.replace( 'CKE-editor'),
        CKEreplyBody = CKEDITOR.replace('jform[reply_body]'),
        totalLabel = 'Total',
        totalAlign = 'left',
        uploadMode,
        buttonId = 0;

    CKEDITOR.dtd.$removeEmpty.span = 0;
    CKEDITOR.dtd.$removeEmpty.i = 0;
    CKE.setUiColor('#fafafa');
    CKE.config.allowedContent = true;
    CKE.config.toolbar_Basic =
        [
        { name: 'document',    items : [ 'Source' ] },
        { name: 'styles',      items : [ 'Styles','Format' ] },
        { name: 'colors',      items : [ 'TextColor' ] },
        { name: 'clipboard',   items : [ 'Undo','Redo' ] },
        '/',
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline'] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent',
                                        'Indent','-','Blockquote','-','JustifyLeft',
                                        'JustifyCenter','JustifyRight','JustifyBlock','-' ] },
        { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
        { name: 'insert',      items : [ 'Image','Table','HorizontalRule'] }
    ];
    CKE.config.toolbar = 'Basic';
    CKEreplyBody.setUiColor('#fafafa');
    CKEreplyBody.config.allowedContent = true;
    CKEreplyBody.config.toolbar_Basic =
        [
        { name: 'document',    items : [ 'Source' ] },
        { name: 'styles',      items : [ 'Styles','Format' ] },
        { name: 'colors',      items : [ 'TextColor' ] },
        { name: 'clipboard',   items : [ 'Undo','Redo' ] },
        '/',
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline'] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent',
                                        'Indent','-','Blockquote','-','JustifyLeft',
                                        'JustifyCenter','JustifyRight','JustifyBlock','-' ] },
        { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
        { name: 'insert',      items : [ 'Image','Table','HorizontalRule'] }
    ];
    CKEreplyBody.config.toolbar = 'Basic';
    function getCSSrulesString()
    {
        var str = 'h1.cke_panel_grouptitle';
        str += ' {font-size: initial !important;} h1,h2,h3,h4,h5,h6,p, div,';
        str += ' address, pre, span {line-height: 32px !important; text-align:';
        str += ' left !important; font-weight: normal !important; font-family:';
        str += ' initial !important; text-transform: none !important;}';
        str += '.cke_panel_list a, span {line-height: 18px !important;}';
        str += 'h1 {font-size: 22px !important;} h2 {font-size: 20px !important;}';
        str += 'h3 {font-size: 18px !important;} h4 {font-size: 16px !important;}';
        str += 'h5 {font-size: 14px !important;} h6 {font-size: 12px !important;}';
        str += 'p, div, address, pre, span {font-size: 16px !important;}';
        return str;
    }
    var iconsLib = 'https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css';
    CKEDITOR.config.contentsCss = [getCSSrulesString(), iconsLib];
    CKE.on('instanceReady', function(e) {
        var id = e.editor.id,
            ckeControl = jQuery('#'+id+'_top'),
            span = document.createElement('span'),
            button = document.createElement('a');
        button.className = "cke_button cke-add-icon zmdi zmdi-font cke_button_off";
        span.className = 'cke_toolgroup';
        span.appendChild(button)
        ckeControl.find('.cke_toolbar_break').before(span)
        jQuery(button).on('click', function(){
            uploadMode = 'CKE';
            jQuery('#icons-upload-modal').modal();
        });
    });
    CKEreplyBody.on('instanceReady', function(e) {
        var id = e.editor.id,
            ckeControl = jQuery('#'+id+'_top'),
            span = document.createElement('span'),
            icon = document.createElement('a'),
            button = document.createElement('a');
        icon.className = 'zmdi zmdi-account-add';
        button.className = "cke_button cke-fields cke_button_off";
        span.className = 'cke_toolgroup';
        span.appendChild(button);
        button.appendChild(icon);
        ckeControl.find('.cke_toolbar_break').before(span)
        jQuery(button).on('click', function(){
            jQuery('#fields-editor').modal();
        });
    });

    jQuery('#fields-editor .forms-list tbody').on('click', 'a', function(event){
        event.preventDefault();
        var flag = true,
            frame = jQuery('#auto-reply iframe'),
            doc,
            id = jQuery(this).closest('tr').find('td').text();
        id = jQuery.trim(id);
        if (frame.length == 0) {
            flag = false;
        } else { 
            doc = frame[0].contentDocument;
        }
        if (flag && doc.getSelection().rangeCount > 0) {
            var iRange = doc.getSelection().getRangeAt(0),
                i = doc.createElement('span')
            i.innerText = '[field ID='+id+']';
            iRange.insertNode(i);
        } else {
            var data = CKEreplyBody.getData();
            data += '[field ID='+id+']';
            CKEreplyBody.setData(data);
        }
        jQuery('#fields-editor').modal('hide');
    });

    jQuery('.ba-search').on('keyup', function(){
        var search = jQuery(this).val();
        jQuery('.forms-list tbody .form-title a').each(function(){
            var title = jQuery(this).text();
            title = jQuery.trim(title);
            title = title.toLowerCase();
            search = search.toLowerCase();
            if (title.indexOf(search) < 0) {
                jQuery(this).closest('tr').hide();
            } else {
                jQuery(this).closest('tr').show();
            }
        });
    });

    function createRule(color)
    {
        var shadow,
            rule;
        shadow = color.split(',');
        if (!shadow[3]) {
            shadow = color
        } else {
            shadow[3] = '0.3)';
            shadow = shadow.join(',');
        }
        rule = '.ba-slider .ui-slider-handle:active, .ba-slider .ui-slider-handle:hover {';
        rule += 'box-shadow: 0px 0px 0px 10px '+shadow+' !important;';
        rule += '-webkit-box-shadow: 0px 0px 0px 10px '+shadow+' !important;}';
        rule += '.ba-slider .ui-slider-handle { background: '+color+' !important;}'
        rule += '.ba-slider .ui-slider-range { background-color: '+color+' !important;}';
        rule += '.droppad_item input.checked-radio:before, ';
        rule += '.droppad_item input.checked-checkbox:before {border-color: ';
        rule += color+' !important; } .droppad_item input.checked-radio:before,';
        rule += ' .droppad_item input.checked-checkbox:before { background: ';
        rule += color+' !important; }';
        jQuery('#theme-rule style').text(rule);
    }


    function checkCart()
    {
        if (jQuery('#jform_display_cart').prop('checked')) {
            jQuery('.baforms-cart').show();
        } else {
            jQuery('.baforms-cart').hide();
        }
    }

    checkCart();
    jQuery('#jform_display_cart').on('click', checkCart);
    
    jQuery('#aply-html').on('click', function(){
        var item = jQuery('#item-id').val();
        var content = CKE.getData();
        jQuery('#'+item).find('.ba-htmltext').html(content);
        jQuery('#'+item).find('a').on('click', function(){
            return false;
        });
        jQuery('#'+item).find('> .ba-options').val(content);
        saveDroppadItems();
        jQuery('#html-editor').modal('hide');
    });
    
    jQuery('#html-button').on('click', function(){
        jQuery('#html-editor').modal();
        jQuery('#html-editor').css('z-index', '1500');
    });
    
    function displayFormTitle()
    {
        if (jQuery('#jform_display_title').prop('checked')) {
            jQuery('.title-form').show();
        } else {
            jQuery('.title-form').hide();
        }
    }
    displayFormTitle()
    
    function displayFormTotal()
    {
        var $this = jQuery('#jform_display_total')[0];
        if (jQuery('#jform_display_total').prop('checked')) {
            jQuery('.ba-total-price').show();
            jQuery('#jform_display_total').parent().find('input, select').not($this).removeAttr('disabled');
            jQuery('#cheсk-options .items-list').removeClass('empty-price');
            jQuery('#jform_alow_captcha').hide().find('option').each(function(){
                jQuery(this).removeAttr('selected');
            }).first().attr('selected', true).parent().prev().hide();
        } else {
            jQuery('#jform_display_total').parent().find('input, select').not($this).not('[type="hidden"]').attr('disabled', true);
            jQuery('#jform_display_cart').removeAttr('checked');
            checkCart();
            jQuery('.ba-total-price').hide();
            jQuery('#cheсk-options .items-list').addClass('empty-price');
            jQuery('#jform_alow_captcha').show().prev().show();
        }
    }
    function checkPayment()
    {
        var value = jQuery('#jform_payment_methods').val();
        if (value == 'paypal') {
            jQuery('.webmoney').hide();
            jQuery('.paypal-login').show();
            jQuery('#jform_payment_environment').show().prev().show();
            jQuery('#jform_payment_environment').nextAll().show();
            jQuery('.skrill').hide();
            jQuery('.2checkout').hide();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide();
            jQuery('.stripe').hide();
            jQuery('.mollie').hide();
        } else if (value == '2checkout') {
            jQuery('.webmoney').hide();
            jQuery('.paypal-login').hide();
            jQuery('.skrill').hide();
            jQuery('#jform_payment_environment').show().prev().show();
            jQuery('#jform_return_url').show().prev().show();
            jQuery('.2checkout').show();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide();
            jQuery('.stripe').hide();
            jQuery('.mollie').hide();
        } else if (value == 'payu') {
            jQuery('#jform_payment_methods').nextAll().hide();
            jQuery('#jform_payment_environment').show().prev().show();
            jQuery('#jform_return_url').show().prev().show();
            jQuery('.payu').show();
            jQuery('.custom-payment').hide()
            jQuery('.stripe').hide();
            jQuery('.mollie').hide();
        } else if (value == 'skrill') {
            jQuery('.webmoney').hide();
            jQuery('.paypal-login').hide();
            jQuery('#jform_payment_environment').hide().prev().hide();
            jQuery('#jform_payment_environment').nextAll().show();
            jQuery('.skrill').show();
            jQuery('.2checkout').hide();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide()
            jQuery('.stripe').hide();
            jQuery('.mollie').hide();
        } else if (value == 'webmoney') {
            jQuery('.paypal-login').hide();
            jQuery('.2checkout').hide();
            jQuery('.skrill').hide().nextAll().hide();
            jQuery('.webmoney').show();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide();
            jQuery('.stripe').hide();
            jQuery('.mollie').hide();
        } else if (value == 'stripe') {
            jQuery('.paypal-login').hide();
            jQuery('.2checkout').hide();
            jQuery('.skrill').hide().nextAll().hide();
            jQuery('.webmoney').hide();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide();
            jQuery('.stripe').show();
            jQuery('.mollie').hide();
        } else if (value == 'mollie') {
            jQuery('.paypal-login').hide();
            jQuery('.2checkout').hide();
            jQuery('.skrill').hide().nextAll().hide();
            jQuery('.webmoney').hide();
            jQuery('.payu').hide();
            jQuery('.custom-payment').hide();
            jQuery('.stripe').hide();
            jQuery('.mollie').show();
            jQuery('#jform_return_url').show().prev().show();
        } else {
            jQuery('.webmoney').hide();
            jQuery('.paypal-login').hide();
            jQuery('.2checkout').hide();
            jQuery('.skrill').hide().nextAll().hide();
            jQuery('.payu').hide();
            jQuery('.custom-payment').show();
            jQuery('.mollie').hide();
        }
        jQuery('.multiple-payment').show();
    }
    displayFormTotal();
    jQuery('#jform_display_total').on('click', displayFormTotal);
    
    jQuery('#jform_display_title').on('click', displayFormTitle);
    
    jQuery('#jform_payment_methods').on('change', function(){
        checkPayment();
    });
    
    checkPayment();

    if (jQuery('#jform_sent_massage').val() == '') {
        jQuery('#jform_sent_massage').val('<h3>Congratulations!</h3><p>Your message was sent successfully!</p>');
    }
    if (jQuery('#jform_error_massage').val() == '') {
        jQuery('#jform_error_massage').val('<h3>Send Message Error!</h3><p>Your message could not be sent!</p>');
    }
    
    function displayFormSubmitBtn()
    {
        if (jQuery('#jform_display_submit').prop('checked')) {
            jQuery('.btn-align').show();
        } else {
            jQuery('.btn-align').hide();
        }
    }
    displayFormSubmitBtn()
    
    jQuery('#jform_display_submit').on('click', displayFormSubmitBtn);
    
    /*
        show section of options break buttons
        and add event to change the button values
    */
    function showBreakOptions (id, option)
    {
        breakButton = id;
        option = option.split(';');
        jQuery("#myTab").tabs({ active: 0 });
        jQuery('#options > div').hide();
        jQuery('#breaker-options').show();
        jQuery('#break-label').val(option[0]);
        jQuery('#break-width').val(option[1]);
        jQuery('#break-height').val(option[2]);
        jQuery('#break-font-size').val(option[5]);
        subColor = rgb2hex(option[3]);
        jQuery('#break-bg-color').minicolors('value', subColor[0]);
        jQuery('#break-bg-color').minicolors('opacity', subColor[1]);
        subColor = rgb2hex(option[4]);
        jQuery('#break-text-color').minicolors('value', subColor[0]);
        jQuery('#break-text-color').minicolors('opacity', subColor[1]);
        jQuery('[name="break-font-weight"]').each(function(){
            if (jQuery(this).val() == option[6]) {
                jQuery(this).attr('checked', true);
            } else {
                jQuery(this).removeAttr('checked');
            }
        });
        jQuery('#break-radius').val(option[7]);
        jQuery('#break-label').off();
        jQuery('#break-label').on('keyup', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).val(value);
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[0] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
        jQuery('#break-width').off();
        jQuery('#break-width').on('keyup click', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).css('width', value+'px');
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[1] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
        jQuery('#break-height').off();
        jQuery('#break-height').on('keyup click', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).css('height', value+'px');
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[2] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
        jQuery('#break-font-size').off();
        jQuery('#break-font-size').on('keyup click', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).css('font-size', value+'px');
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[5] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
        jQuery('[name="break-font-weight"]').off();
        jQuery('[name="break-font-weight"]').on('click', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).css('font-weight', value);
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[6] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
        jQuery('#break-radius').off();
        jQuery('#break-radius').on('click keyup', function(){
            var value = jQuery(this).val();
            jQuery('.'+id).css('border-radius', value+'px');
            option = jQuery('.'+id).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[7] = value;
            var str = option.join(';')
            jQuery('.'+id).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        });
    }
    
    //button identifier
    var breakButton = '';
    
    jQuery('#break-bg-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.'+breakButton).css('background-color', hex)
            var option = jQuery('.'+breakButton).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[3] = hex;
            var str = option.join(';');
            jQuery('.'+breakButton).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        }
    });
    
    jQuery('#break-text-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.'+breakButton).css('color', hex)
            var option = jQuery('.'+breakButton).parent().find('input[type="hidden"]').val();
            option = option.split(';');
            option[4] = hex;
            var str = option.join(';');
            jQuery('.'+breakButton).parent().find('input[type="hidden"]').each(function(){
                jQuery(this).val(str);
            });
            saveFormColumns();
        }
    });
    
    //add event to lock/unlock popup options
    jQuery('#jform_display_popup').on('click', DisableOptions)
    
    jQuery('[name="popup-font-weight"]').each(function(){
        if (jQuery('#jform_button_weight').val() == jQuery(this).val()) {
            jQuery(this).attr('checked', true);
        } else {
            jQuery(this).removeAttr('checked', true);
        }
    });
    
    jQuery('[name="popup-font-weight"]').on('click', function(){
        var weight = jQuery(this).val();
        jQuery('#jform_button_weight').val(weight);
    });

    jQuery('#jform_button_type').on('change', checkBtnType);

    function checkBtnType()
    {
        if (jQuery('#jform_button_type').val() == 'button') {
            jQuery('#jform_button_position').removeAttr('disabled');
            jQuery('#button_bg').removeAttr('disabled');
            jQuery('#button_color').removeAttr('disabled');
            jQuery('#jform_button_font_size').removeAttr('disabled');
            jQuery('#jform_button_border').removeAttr('disabled');
            jQuery('[name="popup-font-weight"]').removeAttr('disabled');
        } else {
            jQuery('#jform_button_position').attr('disabled', true);
            jQuery('#button_bg').attr('disabled', true);
            jQuery('#button_color').attr('disabled', true);
            jQuery('#jform_button_font_size').attr('disabled', true);
            jQuery('#jform_button_border').attr('disabled', true);
            jQuery('[name="popup-font-weight"]').attr('disabled', true);
        }
    }
    
    //function to lock/unlock popup options
    function DisableOptions ()
    {
        if (!jQuery('#jform_display_popup').prop("checked")) {
            jQuery('#jform_modal_width').attr('disabled', true);
            jQuery('#jform_button_position').attr('disabled', true);
            jQuery('#button_bg').attr('disabled', true);
            jQuery('#button_color').attr('disabled', true);
            jQuery('#jform_button_lable').attr('disabled', true);
            jQuery('#jform_button_font_size').attr('disabled', true);
            jQuery('#jform_button_border').attr('disabled', true);
            jQuery('#jform_button_type').attr('disabled', true);
            jQuery('[name="popup-font-weight"]').each(function(){
                jQuery(this).attr('disabled', true);
            });
        } else {
            jQuery('#jform_modal_width').removeAttr('disabled');
            jQuery('#jform_button_lable').removeAttr('disabled');
            jQuery('#jform_button_type').removeAttr('disabled');
            checkBtnType();
            jQuery('[name="popup-font-weight"]').each(function(){
                jQuery(this).removeAttr('disabled');
            });
        }
    }
    DisableOptions();
    checkBtnType();
    
    jQuery('#button_bg').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('#jform_button_bg').val(hex);
        }
    });
    
    jQuery('#button_color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('#jform_button_color').val(hex);
        }
    });
    
    function rgb2hex(rgb)
    {
        var parts = rgb.toLowerCase().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
            hex = '#',
            part,
            color = new Array();
        if (parts) {
            for ( var i = 1; i <= 3; i++ ) {
                part = parseInt(parts[i]).toString(16);
                if (part.length < 2) {
                    part = '0'+part;
                }
                hex += part;
            }
            if (!parts[4]) {
                parts[4] = 1;
            }
            color.push(hex);
            color.push(parts[4]);
            
            return color;
        } else {
            color.push(rgb);
            color.push(1);
            
            return color;
        }
    }
    
    var color = rgb2hex(jQuery('#jform_dialog_color_rgba').val());
    
    jQuery('#dialog_color').minicolors({
        opacity: true,
        change: function(hex, opacity) {
            if (hex != '') {
                var rgba = jQuery(this).minicolors('rgbaString');
                jQuery('#jform_dialog_color_rgba').val(rgba);
            }
        }
    });
    jQuery('#dialog_color').minicolors('value', color[0]);
    jQuery('#dialog_color').minicolors('opacity', color[1]);
    
    color = rgb2hex(jQuery('#jform_message_color_rgba').val());
    jQuery('#message_color').minicolors({
        opacity: true,
        change: function(hex, opacity) {
            if (hex != '') {
                var rgba = jQuery(this).minicolors('rgbaString');
                jQuery('#jform_message_color_rgba').val(rgba);
            }
        }
    });
    jQuery('#message_color').minicolors('value', color[0]);
    jQuery('#message_color').minicolors('opacity', color[1]);
    
    color = rgb2hex(jQuery('#jform_message_bg_rgba').val());
    jQuery('#message_bg').minicolors({
        opacity: true,
        change: function(hex, opacity) {
            if (hex != '') {
                var rgba = jQuery(this).minicolors('rgbaString');
                jQuery('#jform_message_bg_rgba').val(rgba);
            }
        }
    });
    jQuery('#message_bg').minicolors('value', color[0]);
    jQuery('#message_bg').minicolors('opacity', color[1]);
    
    //set color of color pick
    subColor = jQuery('#jform_button_bg').val();
    subColor = rgb2hex(subColor);
    jQuery('#button_bg').minicolors('value', subColor[0]);
    jQuery('#button_bg').minicolors('opacity', subColor[1]);
    subColor = jQuery('#jform_button_color').val();
    subColor = rgb2hex(subColor);
    jQuery('#button_color').minicolors('value', subColor[0]);
    jQuery('#button_color').minicolors('opacity', subColor[1]);
    
    //add event to show popup whith options
    jQuery('.btn-settings').on('click', function(event){
        event.preventDefault();
        jQuery('#global-options').modal();
        jQuery('#global-options').css('z-index', '1500');
    });
    
    //add event on click aply button of popup options
    jQuery('#aply-options').on('click', function(){
        jQuery('#global-options').modal('hide');
    })
    
    //make options whith tabs
    jQuery('#global-tabs').tabs();
    
    //add to the title some attributes
    jQuery('#jform_title').addClass('form-title');
    jQuery('#jform_title').attr('placeholder', 'New Form');
    jQuery('#jform_title-lbl').attr('style', 'display:none');
    
    //global variables to store form options
    var formWidth = 'width: 100%',
        formBgColor = 'background-color: #ffffff',
        formBorderColor = 'border: 1px solid #ffffff',
        formBorderRadius = 'border-radius: 2px',
        formClassSufix = '',
        lableFontSize = '13px',
        labelFontWeight = 'normal',
        lableFontColor = '#333333',
        inputsHeight = '50px',
        inputsFontSize = '13px',
        inputsFontColor = '#999999',
        inputsBgColor = '#ffffff',
        inputsBorderColor = 'border: 1px solid #f3f3f3',
        inputsBorderRadius = '2px',
        columnNumber = 2,
        iconFontColor = '#d9d9d9',
        iconFontSize = 24;
    
    //add event to change font weight of the lables
    jQuery('input[name="lable-weight"]').on('click', function() {
        var weight = jQuery(this).val();
        jQuery('.droppad_item').find('.label-item').each(function() {
            jQuery(this).css('font-weight', weight);
        });
        labelFontWeight = weight;
        jQuery('.ba-total-price p, .ba-cart-headline').css('font-weight', weight);
        saveFormToDataBase();
    });

    jQuery('.theme-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('#jform_theme_color').val(hex);
            clearTimeout(delay);
            delay = setTimeout(function(){
                createRule(hex);
            }, 200);
        }
    });

    jQuery('#form-bgcolor').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.form-style').css('background-color', hex);
            formBgColor = 'background-color: '+hex;
            saveFormToDataBase();
        }
    });

    jQuery('#form-borcolor').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.form-style').css('border', '1px solid '+hex);
            formBorderColor = 'border: 1px solid '+hex;
            saveFormToDataBase();
        }
    });

    jQuery('#label-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.droppad_item').find('label').each(function(i, el){
                jQuery(el).css('color', hex);
            });
            lableFontColor = hex;
            jQuery('.ba-total-price p, .ba-cart-headline').css('color', hex);
            saveFormToDataBase();
        }
    });

    jQuery('#input-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
                var type = jQuery.trim(jQuery(this).attr('class').substr(3));
                if (type == 'email' || type == 'textarea' || type== 'textInput' ||
                    type == 'dropdown' || type == 'selectMultiple' || type == 'address') {
                    jQuery(el).css('color', hex);
                } else if (type == 'checkMultiple' || type == 'chekInline' || type == 'radioInline' ||
                    type == 'radioMultiple') {
                    jQuery(el).css('color', hex);
                } else if (type == 'date') {
                    jQuery(el).find('input').css('color', hex);
                }
            });
            jQuery('.baforms-cart').css('color', hex);
            inputsFontColor = hex;
            saveFormToDataBase();
        }
    });

    jQuery('#input-bgcolor').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
                var type = jQuery.trim(jQuery(this).attr('class').substr(3));
                if (type == 'email' || type == 'textarea' || type== 'textInput' ||
                    type == 'dropdown' || type == 'selectMultiple' || type== 'address') {
                    jQuery(el).css('background-color', hex);
                }  else if (type == 'date') {
                    jQuery(el).find('input').css('background-color', hex);
                }
            });
            inputsBgColor = hex;
            saveFormToDataBase();
        }
    });

    jQuery('#input-borcolor').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
                var type = jQuery.trim(jQuery(this).attr('class').substr(3));
                if (type == 'email' || type == 'textarea' || type== 'textInput' ||
                    type == 'dropdown' || type == 'selectMultiple' || type == 'address') {
                    jQuery(el).css('border', '1px solid '+hex);
                } else if (type == 'date') {
                    jQuery(el).find('input').css('border', '1px solid '+hex);
                }
            });
            jQuery('.baforms-cart').css('border', '1px solid '+hex);
            jQuery('.ba-cart-headline').css('border-bottom', '1px solid '+hex);
            inputsBorderColor = 'border: 1px solid '+hex;
            saveFormToDataBase();
        }
    });
    
    jQuery('#icons-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            jQuery('.icons-cell i').css('color', hex);
            iconFontColor = hex;
            saveFormToDataBase();
        }
    });

    //add the values to the options fields
    var subColor = formBgColor.substring(18);
    subColor = rgb2hex(subColor);
    jQuery('#form-bgcolor').minicolors('value', subColor[0]);
    jQuery('#form-bgcolor').minicolors('opacity', subColor[1]);
    subColor = formBorderColor.substring(18);
    subColor = rgb2hex(subColor);
    jQuery('#form-borcolor').minicolors('value', subColor[0])
    jQuery('#form-borcolor').minicolors('opacity', subColor[1])
    subColor = rgb2hex(lableFontColor);
    jQuery('#label-color').minicolors('value', subColor[0]);
    jQuery('#label-color').minicolors('opacity', subColor[1]);
    subColor = rgb2hex(inputsFontColor);
    jQuery('#input-color').minicolors('value', subColor[0]);
    jQuery('#input-color').minicolors('opacity', subColor[1]);
    subColor = rgb2hex(inputsBgColor);
    jQuery('#input-bgcolor').minicolors('value', subColor[0]);
    jQuery('#input-bgcolor').minicolors('opacity', subColor[1]);
    subColor = rgb2hex(inputsBorderColor.substring(18));
    jQuery('#input-borcolor').minicolors('value', subColor[0]);
    jQuery('#input-borcolor').minicolors('opacity', subColor[1]);
    subColor = rgb2hex(iconFontColor);
    jQuery('#icons-color').minicolors('value', subColor[0]);
    jQuery('#icons-color').minicolors('opacity', subColor[1]);
    subColor = rgb2hex(jQuery('#jform_theme_color').val());
    jQuery('.theme-color').minicolors('value', subColor[0]);
    jQuery('.theme-color').minicolors('opacity', subColor[1]);
    jQuery('#icons-size').val(iconFontSize);
    
    //variable to check it is a new form or loaded
    var formId = jQuery('#jform_id').val();
    
    //add the values to the options fields for the loaded form
    function formsSettings()
    {
        var width = formWidth.substring(7, formWidth.length-1);
        jQuery('#form-width').val(width);
        subColor = formBgColor.substring(18);
        subColor = rgb2hex(subColor);
        jQuery('#form-bgcolor').minicolors('value', subColor[0]);
        jQuery('#form-bgcolor').minicolors('opacity', subColor[1]);
        subColor = formBorderColor.substring(18);
        subColor = rgb2hex(subColor);
        jQuery('#form-borcolor').minicolors('value', subColor[0]);
        jQuery('#form-borcolor').minicolors('opacity', subColor[1]);
        jQuery('#form-radius').val(formBorderRadius.substring(15, formBorderRadius.length-2));
        jQuery('#form-class').val(formClassSufix);
        jQuery('#label-size').val(lableFontSize.substring(0, lableFontSize.length-2));
        subColor = rgb2hex(lableFontColor)
        jQuery('#label-color').minicolors('value', subColor[0]);
        jQuery('#label-color').minicolors('opacity', subColor[1]);
        jQuery('#input-height').val(inputsHeight.substring(0, inputsHeight.length-2));
        jQuery('#input-size').val(inputsFontSize.substring(0, inputsFontSize.length-2));
        subColor = rgb2hex(inputsFontColor);
        jQuery('#input-color').minicolors('value', subColor[0]);
        jQuery('#input-color').minicolors('opacity', subColor[1]);
        subColor = rgb2hex(inputsBgColor);
        jQuery('#input-bgcolor').minicolors('value', subColor[0]);
        jQuery('#input-bgcolor').minicolors('opacity', subColor[1]);
        subColor = rgb2hex(inputsBorderColor.substring(18));
        jQuery('#input-borcolor').minicolors('value', subColor[0]);
        jQuery('#input-borcolor').minicolors('opacity', subColor[1]);
        jQuery('#input-radius').val(inputsBorderRadius.substring(0, inputsBorderRadius.length-2));
        subColor = rgb2hex(iconFontColor);
        jQuery('#icons-color').minicolors('value', subColor[0]);
        jQuery('#icons-color').minicolors('opacity', subColor[1]);
        jQuery('#icons-size').val(iconFontSize);
    }
    
    /*
        make elements draggable
    */
    function makeDragg ()
    {
        jQuery(".tool").draggable({
            helper: "clone",
            stack: "div",
            cursor: "move",
            cancel: null
        });
        jQuery(".page-break").draggable({
            helper: "clone",
            stack: "div",
            cursor: "move",
            cancel: null
        });
    }
    
    //function what save title options to the database
    function saveTitleToDataBase()
    {
        if (formId == '') {
            var settings = jQuery('.title-form').find('h1').attr('style');
            jQuery('#jform_title_settings').val(settings);
        }
    }
    
    //function what save form options to database
    function saveFormToDataBase()
    {
        if (formId == '') {
            jQuery('#jform_form_settings').val(formClassSufix+'/'+lableFontSize+'/'+lableFontColor+'/'+
                                               inputsHeight+'/'+inputsFontSize+'/'+inputsFontColor+'/'+
                                               inputsBgColor+'/'+inputsBorderColor+'/'+inputsBorderRadius+'/'+
                                               formWidth+';'+formBgColor+';'+formBorderColor+';'+formBorderRadius+
                                               '/'+labelFontWeight+'/'+iconFontSize+'/'+iconFontColor+
                                               '/'+totalLabel+'/'+totalAlign+'/');
        }
    }
    
    //add style to droppable items
    function addStyle(draggable)
    {
        var el = draggable.find("[class*=ba]");
        draggable.find('label').css({
            'font-size' : lableFontSize,
            'color' : lableFontColor,
            'font-weight' : labelFontWeight
        });
        var type = jQuery.trim(draggable.find("[class*=ba]")[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
        if (type == 'email' || type == 'textarea' || type== 'textInput' || type== 'address') {
            el.css({
                'font-size' : inputsFontSize,
                'height' : inputsHeight,
                'color' : inputsFontColor,
                'background-color' : inputsBgColor,
                'border' : inputsBorderColor.substring(8),
                'border-radius' : inputsBorderRadius
            });
        } else if (type == 'date') {
            el.find('input[type="text"]').css({
                'font-size' : inputsFontSize,
                'height' : inputsHeight,
                'color' : inputsFontColor,
                'background-color' : inputsBgColor,
                'border' : inputsBorderColor.substring(8),
                'border-radius' : inputsBorderRadius
            });
        }
        if (type == 'dropdown' || type == 'selectMultiple') {
            el.css({
                'font-size' : inputsFontSize,
                'color' : inputsFontColor,
                'background-color' : inputsBgColor,
                'border' : inputsBorderColor.substring(8)
            });
        }
        if (type == 'dropdown') {
            el.css('height', inputsHeight);
        }
        if (type == 'checkMultiple' || type == 'chekInline' || type == 'radioInline' ||
            type == 'radioMultiple') {
            el.css({
                'font-size' : inputsFontSize,
                'color' : inputsFontColor
            });
        }
    }
    
    //add event to draw title on the form
    jQuery('.form-title').on('keyup', function(){
        var title = jQuery(this).val();
        jQuery('.title-form').find('h1 .title').text(title);
        saveDroppadItems();
        saveTitleToDataBase();
    });
    
    //add event to change title font size
    jQuery('.title-size').on('keyup click', function(){
        var size = jQuery(this).val();
        var style = jQuery('.title-form').find('h1').attr('style');
        style = style.split(';');
        jQuery('.title-form').find('h1').attr('style', 'font-size:'+size+'px;'+style[1]+';'+style[2]+';'+style[3]+';');
        saveDroppadItems();
        saveTitleToDataBase();
    });
    
    //add event to change title font weight
    jQuery('input[name="title-weight"]').on('click', function(){
        var weight = jQuery(this).val();
        var style = jQuery('.title-form').find('h1').attr('style');
        style = style.split(';');
        jQuery('.title-form').find('h1').attr('style', style[0]+';font-weight:'+weight+';'+style[2]+';'+style[3]+';');
        saveDroppadItems();
        saveTitleToDataBase();
    });
    
    //add event to change title text align
    jQuery('.title-alignment').on('change', function(){
        var aligment = jQuery(this).val();
        var style = jQuery('.title-form').find('h1').attr('style');
        style = style.split(';');
        jQuery('.title-form').find('h1').attr('style', style[0]+';'+style[1]+';text-align:'+aligment+';'+style[3]+';');
        saveDroppadItems();
        saveTitleToDataBase();
    });
    
    jQuery('#title-color').minicolors({
        opacity : true,
        change : function(hex){
            var style = jQuery('.title-form').find('h1').attr('style');
            style = style.split(';');
            hex = jQuery(this).minicolors('rgbaString')
            jQuery('.title-form').find('h1').attr('style', style[0]+';'+style[1]+';'+style[2]+';color:'+hex+';');
            saveDroppadItems();
            saveTitleToDataBase();
        }
    });
    
    //add event to change form width
    jQuery('#form-width').on('keyup click', function(){
        var width = jQuery(this).val();
        if (width > 100) {
            width = 100;
            jQuery(this).val(width)
        }
        jQuery('.form-style').css('width', width+'%');
        formWidth = 'width: '+width+'%';
        saveFormToDataBase();
    });
    
    //add event to change form border radius
    jQuery('#form-radius').on('keyup click', function(){
        var radius = jQuery(this).val();
        jQuery('.form-style').css('border-radius', radius+'px');
        formBorderRadius = 'border-radius: '+radius+'px';
        saveFormToDataBase();
    });
    
    //add event to add form class sufix
    jQuery('#form-class').on('keyup', function(){
        var sufix = jQuery(this).val();
        var data = jQuery('.form-style').attr('class');
        jQuery('.form-style').removeClass(data.substr(10));
        jQuery('.form-style').addClass(sufix);
        formClassSufix = sufix;
        saveFormToDataBase();
    });
    
    //add event to change labels font size of the form item
    jQuery('#label-size').on('keyup click', function(){
        var size = jQuery(this).val();
        jQuery('.droppad_item').find('label').each(function(i, element){
            jQuery(element).css('font-size', size+'px');
        });
        lableFontSize = size+'px';
        jQuery('.ba-total-price p, .ba-cart-headline').css('font-size', size+'px');
        saveFormToDataBase();
    });

    //add event to change input font size of the form
    jQuery('#input-size').on('keyup click', function(){
        var size = jQuery(this).val();
        jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
            var type = jQuery.trim(jQuery(this).attr('class').substr(3));
            if (type == 'email' || type == 'textarea' || type== 'textInput' ||
                type == 'dropdown' || type == 'selectMultiple' || type== 'address') {
                jQuery(el).css('font-size', size+'px');
            } else if (type == 'checkMultiple' || type == 'chekInline' || type == 'radioInline' ||
                type == 'radioMultiple') {
                jQuery(el).css('font-size', size+'px');
            }  else if (type == 'date') {
                jQuery(el).find('input').css('font-size', size+'px');
            }
        });
        jQuery('.baforms-cart').css('font-size', size+'px');
        inputsFontSize = size+'px';
        saveFormToDataBase();
    });
    
    //add event to change border radius of the input elements
    jQuery('#input-radius').on('keyup click', function(){
        var radius = jQuery(this).val();
        jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
            var type = jQuery.trim(jQuery(this).attr('class').substr(3));
            if (type == 'email' || type == 'textarea' || type== 'textInput' || type== 'address') {
                jQuery(el).css('border-radius', radius+'px');
            }  else if (type == 'date') {
                jQuery(el).find('input').css('border-radius', radius+'px');
            }
        });
        inputsBorderRadius = radius+'px';
        saveFormToDataBase();
    });

    jQuery('#icons-size').on('keyup click', function(){
        var size = jQuery(this).val();
        iconFontSize = size;
        jQuery('.icons-cell i').css('font-size', size+'px');
        saveFormToDataBase();
    });
    
    //add event to change inputs heiht
    jQuery('#input-height').on('keyup click', function(){
        var height = jQuery(this).val();
        jQuery('.droppad_item').find('[class*=ba]').each(function(i, el){
            var type = jQuery.trim(jQuery(this).attr('class').substr(3));
            if (type == 'email' || type == 'textarea' || type== 'textInput' || type == 'dropdown' || type == 'address') {
                jQuery(el).css('height', height+'px');
            } else if (type == 'date') {
                jQuery(el).find('input').css('height', height+'px');
            }
            inputsHeight = height+'px';
            saveFormToDataBase();
        });
    });
    
    //add event on click of the title to show the title options
    jQuery('.title-form').on('click', function(){
        jQuery("#myTab").tabs({ active: 0 });
        jQuery('#options > div').hide();
        jQuery('#options, .title-options').show();
        var style = jQuery('.title-form').find('h1').attr('style');
        style = style.split(';');
        var size = jQuery.trim(style[0]);
        size = size.substring(10, size.length-2);
        jQuery('.title-size').val(size);
        var align = jQuery.trim(style[2]);
        align = align.substring(11);
        jQuery('.title-alignment').find('option').each(function(){
            jQuery(this).removeAttr('selected');
            if (jQuery(this).val() == align) {
                jQuery(this).attr('selected', true);
            }
        });
        var color = jQuery.trim(style[3]);
        color = color.substring(6);
        color = rgb2hex(color);
        jQuery('#title-color').minicolors('value', color[0]).minicolors('opacity', color[1]);
        var fontWeight = jQuery.trim(style[1]);
        fontWeight = fontWeight.substr(12);
        jQuery('.title-options').find('[name="title-weight"]').each(function(i, element){
            jQuery(this).removeAttr('checked');
            if (element.value == fontWeight) {
                jQuery(this).attr('checked', true);
            }
        })
    });
    
    //add event to change text align of submit button
    jQuery('#button-otions .button-alignment').on('change', function(){
        var aligment = jQuery(this).val();
        jQuery('.btn-align').css('text-align', aligment)
        saveDroppadItems();
    });
    
    //make options tabs
    jQuery( "#myTab" ).tabs();
    
    //global variable for unique id
    var number = 1;
    
    //options for map
    
    var options = {
        scrollwheel: false,
        navigationControl: false,
        mapTypeControl: false,
        scaleControl: false,
        draggable: false,
        zoomControl: false,
        disableDefaultUI: true,
        disableDoubleClickZoom: true,
    }
    
    //add event to show options of page break
    function breakEvent() {
        jQuery('.btn-next').on('click', function(){
            var option = jQuery(this).parent().find('input[type="hidden"]').val(),
                nextOpt = jQuery(this).closest('.break').find('.ba-prev input[type="hidden"]').val();
            showBreakOptions('btn-prev', nextOpt);
            showBreakOptions('btn-next', option);
        });
        jQuery('.btn-prev').on('click', function(){
            var option = jQuery(this).parent().find('input[type="hidden"]').val();
            showBreakOptions('btn-prev', option);
        });
    }
    
    //function to save break options to the hidden input
    function breakStyle()
    {
        var prev = jQuery('.break').find('.ba-prev').find('input[type="hidden"]').val(),
            next = jQuery('.break').find('.ba-next').find('input[type="hidden"]').val();
        if (prev) {
            return prev+'|'+next;
        } else {
            return false;
        }
    }
    
    /*
        function make column droppad area, add unique id for items and show droppad item
    */
    function main()
    {
        makeDragg();
        jQuery('#content-section').droppable({
            activeClass: "activeDroppable",
            hoverClass: "hoverDroppable",
            accept: ".page-break",
            drop: function( event, ui ) {
                var draggable = ui.draggable;
                draggable = jQuery(ui.draggable).find(".model").clone();
                draggable.removeClass("model");
                draggable.removeClass("page-break");
                draggable.addClass("break items");
                draggable.attr ("id", "break-"+(number++));
                draggable.find('.btn-next').attr ("id", "next-"+(number++));
                draggable.find('.btn-prev').attr ("id", "prev-"+(number++));
                var style = breakStyle();
                if (style) {
                    style = style.split('|');
                    var prev = style[0];
                    var next = style[1];
                    prev = prev.split(';');
                    next = next.split(';');
                    draggable.find('.btn-prev').removeAttr('style');
                    draggable.find('.btn-prev').val(prev[0]);
                    draggable.find('.btn-prev').css('width', prev[1]+'px');
                    draggable.find('.btn-prev').css('height', prev[2]+'px');
                    draggable.find('.btn-prev').css('background-color', '#'+prev[3]);
                    draggable.find('.btn-prev').css('color', '#'+prev[4]);
                    draggable.find('.btn-prev').css('font-size', prev[5]+'px');
                    draggable.find('.btn-prev').css('font-weight', prev[6]);
                    draggable.find('.btn-prev').css('border-radius', prev[7]+'px');
                    prev = prev.join(';')
                    draggable.find('.ba-prev').find('input[type="hidden"]').val(prev);
                    draggable.find('.btn-next').removeAttr('style');
                    draggable.find('.btn-next').val(next[0]);
                    draggable.find('.btn-next').css('width', next[1]+'px');
                    draggable.find('.btn-next').css('height', next[2]+'px');
                    draggable.find('.btn-next').css('background-color', '#'+next[3]);
                    draggable.find('.btn-next').css('color', '#'+next[4]);
                    draggable.find('.btn-next').css('font-size', next[5]+'px');
                    draggable.find('.btn-next').css('font-weight', next[6]);
                    draggable.find('.btn-next').css('border-radius', next[7]+'px');
                    next = next.join(';')
                    draggable.find('.ba-next').find('input[type="hidden"]').val(next);
                }
                draggable.appendTo(this);
                canDelete ();
                saveFormColumns();
                breakEvent();
            }
        });
        jQuery(".droppad_area").droppable({
            greedy: true,
            activeClass: "activeDroppable",
            hoverClass: "hoverDroppable",
            accept: ".tool",
            drop: function( event, ui ) {
                var draggable = ui.draggable;
                draggable = jQuery(ui.draggable).find(".model").clone();
                draggable.removeClass("model");
                draggable.removeClass("tool");
                draggable.addClass("droppad_item");
                draggable[0].id = "baform-"+(number++);
                this.style.height = '';
                var newType = jQuery.trim(draggable.find("[class*=ba]")[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
                if ((newType == 'map' || newType == 'address') && !google_maps_apikey) {
                    jQuery('#google-maps-notification-dialog').modal()
                    return;
                }
                draggable.appendTo(this);
                var content = jQuery('#jform_form_content').val(),
                    parent = draggable.parent().attr('id'),
                    opt = saveDroppadOptions(newType, draggable[0].id);
                saveDroppadItems();
                if (draggable.find('.ba-map').hasClass('ba-map')) {
                    baGmap()
                }
                sortDrop();
                addStyle(draggable);
                draggable.on('click', function(event){
                    event.stopPropagation();
                    var me = jQuery(this).find("[class*=ba]")[0],
                        type = jQuery.trim(me.className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
                    customize(type, this.id);
                    jQuery('#options').removeAttr('style');
                });
                makeDragg ();
            }
        });
        sortDrop();
        saveFormColumns();
        saveDroppadItems();
        saveTitleToDataBase();
        saveFormToDataBase();
        if (checkColumn()) {
            drawHTMLColumn();
        }
        if (checkItems()) {
            drawHTMLItems();
        }
        jQuery('.ba-total-price p').css({
            'text-align' : totalAlign,
            'font-size' : lableFontSize,
            'color' : lableFontColor,
            'font-weight' : labelFontWeight
        });
        var  cartBorder = inputsBorderColor.split(':');
        jQuery('.ba-cart-headline').css({
            'border-bottom' : cartBorder[1],
            'font-size' : lableFontSize,
            'color' : lableFontColor,
            'font-weight' : labelFontWeight
        });
        jQuery('.baforms-cart').css({
            'border' : cartBorder[1],
            'font-size' : inputsFontSize,
            'color' : inputsFontColor
        });
        jQuery('.ba-total-price p')[0].firstChild.data = totalLabel+': ';
    }
    
    //function to the initial map of loaded items
    function baGmap()
    {
        initMap();
    }

    function restoreHTML(element, str)
    {
        if (element[0].indexOf('baform') != -1) {
            if (jQuery('#'+element[0]).find('> [data-condition="'+element[4]+'"]').length == 0) {
                var div = '<div class="droppad_area condition-area" data-condition="'+element[4]+'"></div>';
                jQuery('#'+element[0]).append(div);
                jQuery('#'+element[0]).addClass('conditional-field');
            }
            jQuery('#'+element[0]).find('> [data-condition="'+element[4]+'"]').append(str);
        } else {
            jQuery(".droppad_area").html(function(indx, oldHtml) {
                var id = jQuery(this).attr('id');
                if (id == element[0]) {
                    return oldHtml + str;
                } else {
                    return oldHtml;
                }
            });
        }
        
    }
    
    /*
        function what draw the elements of the loaded form,
        change id of the elements
    */
    function drawHTMLItems()
    {
        var items = jQuery('#jform_form_content').val(),
            str = '',
            button = "",
            buttonName = "",
            buttonAlign = '',
            formSettings = jQuery('#jform_form_settings').val(),
            flag = jQuery('#jform_display_total').prop('checked'),
            symbol = jQuery('#jform_currency_symbol').val();
        items = items.split('|_-_|');
        formSettings = formSettings.split('/');
        if (formSettings[9] == 'undefined') {
            formSettings[9] = '';
        }
        jQuery('.form-style').attr('style', formSettings[9]);
        jQuery('.form-style').addClass(formSettings[0])
        var aray = formSettings[9].split(';');
        formWidth = jQuery.trim(aray[0]);
        formBgColor = jQuery.trim(aray[1]);
        formBorderColor = jQuery.trim(aray[2]);
        formBorderColor = jQuery.trim(formBorderColor);
        formBorderRadius = jQuery.trim(aray[3]);
        formBorderRadius = jQuery.trim(formBorderRadius)
        formClassSufix = formSettings[0];
        lableFontSize = formSettings[1];
        lableFontColor = formSettings[2];
        inputsHeight = formSettings[3];
        inputsFontSize = formSettings[4];
        inputsFontColor = formSettings[5];
        inputsBgColor = formSettings[6];
        inputsBorderColor = formSettings[7];
        inputsBorderRadius = formSettings[8];
        labelFontWeight = formSettings[10];
        if (formSettings[11]) {
            iconFontSize = formSettings[11];
            iconFontColor = formSettings[12];
        }
        if (formSettings[13]) {
            totalLabel = formSettings[13];
            totalAlign = formSettings[14];
        }
        for (var i = 0; i < items.length - 1; i++) {
            var obj = JSON.parse(items[i]),
                element = obj.settings.split('_-_');
            if (element[2] == 'textInput') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += "<label title='"+options[1]+"' class='label-item'>"
                str += '</label>';
                if (options[5] && options[5].indexOf('zmdi') >= 0) {
                    str += '<div class="container-icon">';
                }
                str += '<input class="ba-textInput" type="text" placeholder=';
                str += "'' title=''/>";
                if (options[5] && options[5].indexOf('zmdi') >= 0) {
                    str += '<div class="icons-cell"><i class="'+options[5]+'"></i></div></div>';
                }
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('.ba-textInput').attr('placeholder', options[2]);
                jQuery('#'+element[1]).find('.ba-textInput').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap();
            }
            if (element[2] == 'address') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += "<label title='"+options[1]+"' class='label-item'>"
                str += '</label>';
                if (options[4] && options[4].indexOf('zmdi') >= 0) {
                    str += '<div class="container-icon">';
                }
                str += '<input class="ba-address" type="text" placeholder=';
                str += "'' title=''/>";
                if (options[4] && options[4].indexOf('zmdi') >= 0) {
                    str += '<div class="icons-cell"><i class="'+options[4]+'"></i></div></div>';
                }
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('.ba-address').attr('placeholder', options[2]);
                jQuery('#'+element[1]).find('.ba-address').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap();
            }
            if (element[2] == 'image') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += '<img src="../'+options[0]+'" class="ba-image">';
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                jQuery('#'+element[1]).find('.ba-image').attr('alt', options[3]).css({
                    'width' : options[2]+'%'
                });
                jQuery('#'+element[1]).css({
                    'text-align' : options[1]
                });
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap();
            }
            if (element[2] == 'email') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += "<label title='"+options[1]+"' class='label-item'></label>";
                if (options[3] && options[3].indexOf('zmdi') >= 0) {
                    str += '<div class="container-icon">';
                }
                str += '<input class="ba-email" type="email" placeholder=';
                str += "'' title=''/>";
                if (options[3] && options[3].indexOf('zmdi') >= 0) {
                    str += '<div class="icons-cell"><i class="'+options[3]+'"></i></div></div>';
                }
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                baGmap();
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('.ba-email').attr('placeholder', options[2]);
                jQuery('#'+element[1]).find('.ba-email').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'textarea') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += "<label title='"+options[1]+"' class='label-item'>"
                str += '</label>';
                if (options[5] && options[5].indexOf('zmdi') >= 0) {
                    str += '<div class="container-icon">';
                }
                str += '<textarea class="ba-textarea" placeholder=';
                str += "'' title='' style='";
                str += "min-height:"+options[4]+"px'></textarea>";
                if (options[5] && options[5].indexOf('zmdi') >= 0) {
                    str += '<div class="icons-cell"><i class="'+options[5]+'"></i></div></div>';
                }
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('.ba-textarea').attr('placeholder', options[2]);
                jQuery('#'+element[1]).find('.ba-textarea').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'chekInline') {
                var options = element[3].split(';'),
                    option = options[2];
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>"
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                str += '<div class="ba-chekInline" >';
                for (var j = 0; j < option.length; j++) {
                    option[j] = option[j].split('====');
                    str += "<span><input type='checkbox'";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[4] && options[4] == j) {
                        str += ' class="checked-checkbox"';
                    }
                    str += " name='checkboxInline'>"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str += "</span>";
                }
                str += '</div>'
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'checkMultiple') {
                var options = element[3].split(';'),
                    option = options[2];
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>"
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                str += '<div class="ba-checkMultiple">';
                for (var j = 0; j < option.length; j++) {
                    option[j] = option[j].split('====');
                    str += "<span><input type='checkbox'";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[4] && options[4] == j) {
                        str += ' class="checked-checkbox"';
                    }
                    str += " name='checkMultiple'>"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str += "<br></span>";
                }
                str += '</div>'
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'radioInline') {
                var options = element[3].split(';'),
                    option = options[2];
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>";
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                str += '<div class="ba-radioInline">';
                for (var j = 0; j < option.length; j++){
                    option[j] = option[j].split('====');
                    str += "<span><input type='radio'";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[4] && options[4] == j) {
                        str += ' class="checked-radio"';
                    }
                    str += " name='radioInline'>"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str += "</span>";
                }
                str += '</div>'
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0]);
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'radioMultiple') {
                var options = element[3].split(';'),
                    option = options[2];
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>";
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                str += '<div class="ba-radioMultiple">';
                for (var j = 0; j < option.length; j++) {
                    option[j] = option[j].split('====');
                    str += "<span><input type='radio' ";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[4] && options[4] == j) {
                        str += ' class="checked-radio"';
                    }
                    str += " name='radioMultiple'>"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str += "<br></span>";
                }
                str += '</div>'
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'dropdown') {
                var options = element[3].split(';'),
                    option = options[2];
                str = '<div id="'+element[1]+'" class="droppad_item';
                str += '">';
                str += "<label title='' class='label-item'>"
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                if (options[4] && options[4].indexOf('zmdi') >= 0) {
                    str += '<div class="container-icon">';
                }
                str += '<select class="ba-dropdown">';
                str += '<option data-dropdown-select="true">'+jQuery('option[data-dropdown-select]').text()+'</option>';
                for (var j = 0; j < option.length; j++){
                    option[j] = option[j].split('====');
                    str += "<option";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[5] && options[5] == j) {                        
                        str += ' selected';
                    }
                    str += ">"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str += "</option>";
                }
                str += '</select>'
                if (options[4] && options[4].indexOf('zmdi') >= 0) {
                    str += '<div class="icons-cell"><i class="'+options[4]+'"></i></div></div>';
                }
                str += "<input type='hidden' class='ba-options' value=''>";
                str += "</div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'selectMultiple') {
                var options = element[3].split(';'),
                    option = options[2];;
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>"
                str += '</label>';
                option = option.replace(new RegExp('"','g'), '');
                option = option.split("\\n");
                str += '<select size="'+options[4]+'" class="ba-selectMultiple">';
                for (var j=0; j<option.length; j++){
                    option[j] = option[j].split('====');
                    str += "<option";
                    if (option[j][1]) {
                        str += " data-price='"+option[j][1]+"'"
                    }
                    if (options[5] && options[5] == j) {
                        str += ' selected';
                    }
                    str += ">"+option[j][0];
                    if (option[j][1] && flag) {
                        str += ' - '
                        if (jQuery('#jform_currency_position').val() == 'before') {
                            str += symbol;
                        }
                        str += jQuery.trim(option[j][1]);
                        if (jQuery('#jform_currency_position').val() != 'before') {
                            str += symbol;
                        }
                    }
                    str +="</option>";
                }
                str += '</select>'
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                if (options[3] == 1 && options[0] != '') {
                    options[0] += ' *';
                }
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'upload') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'></label>";
                str += '<span>Max. file size, '+options[2]+'mb ('+options[3]+')</span><br>'
                str += "<input type='file' class='ba-upload'>";
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                baGmap();
                jQuery('#'+element[1]).find('label').text(options[0])
                if (options[4] == 1) {
                    jQuery('#'+element[1]).find('label').text(options[0]+' *')
                }
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'map') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += '<div class="ba-map" style="width: '+options[3]+'%; height: '+options[4]+'px;" >';
                str += "</div><input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap ();
            }
            if (element[2] == 'slider') {
                var options = element[3].split(';');
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label title='' class='label-item'>"
                str += "</label>";
                str += "<div class='ba-slider'></div>";
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                var minimum = options[2],
                    maximum = options[3],
                    step = options[4];
                jQuery('#'+element[1]).find('.ba-slider').prop("slide",null);
                jQuery('#'+element[1]).find('.ba-slider').slider({
                    values: [Number(minimum), Number(maximum)],
                    min: Number(minimum),
                    max: Number(maximum),
                    step: Number(step),
                    range: "min"
                });
                jQuery('#'+element[1]).find('label').text(options[0])
                jQuery('#'+element[1]).find('label').attr('title', options[1]);
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap();
            }
            if (element[2] == 'date') {
                var options = element[3].split(';'),
                    label = options[0];
                if (options[1] == 1) {
                    label += ' *';
                }
                str = '<div id="'+element[1]+'" class="droppad_item">';
                str += "<label class='label-item'>"+label+"</label>";
                str += "<input type='hidden' class='ba-options' value=''></div>";
                restoreHTML(element, str);
                jQuery('#'+element[1]).find('label').text(label)
                var date= jQuery('.datepick-field').find('.ba-date').clone();
                jQuery('#'+element[1]).find('.label-item').after(date)
                baGmap();
                jQuery('#'+element[1]).find('> .ba-options').val(element[3]).attr('data-id', obj.id);
            }
            if (element[2] == 'htmltext') {
                str = '<div id="'+element[1]+'" class="droppad_item">';
                if (element[3] == '') {
                    element[3] = 'Text';
                }
                str += '<p class="ba-htmltext"></p>';
                str += "<input type='hidden' class='ba-options' value=''>";
                str += '</div>';
                restoreHTML(element, str);
                jQuery('#'+element[1]+' > .ba-options').val(element[3]).attr('data-id', obj.id);
                baGmap()
            }
            if (element[0] == 'button') {
                button = element[2].split(';');
                buttonName = element[1];
                buttonAlign = element[3];
                for (var id = 0; id < button.length; id++) {
                    button[id] = jQuery.trim(button[id]);
                    if (button[id] == 'undefined') {
                        button[id] = '';
                    }
                }
                button = button.join(';');
                buttonId = obj.id;
            }
        }
        if (button == '' || buttonAlign == '' || buttonName == '') {
            var elem = items[0].split('_-_');
            button = elem[2];
            buttonAlign = elem[3];
            buttonName = elem[1];
        }
        jQuery('.btn-align').attr('style', buttonAlign);
        jQuery('#baform-0').attr('style', button);
        jQuery('#baform-0').val(buttonName);
        jQuery('.droppad_item').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            var id = jQuery(this).attr('id');
            var me = jQuery(this).find("[class*=ba]")[0];
            var type = jQuery.trim(me.className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
            customize(type, id)
        });
        var numbers = [];
        jQuery('.droppad_item').each(function(index, elem){
            var newId = jQuery(this).attr('id');
            newId = jQuery.trim(newId);
            numbers[index] = newId.substring(7);
        });
        var max = 0;
        for (var n=0; n<numbers.length; n++) {
            if (Number(numbers[n])>Number(max)) {
                max = numbers[n];
                if (Number(max) >= Number(number)) {
                    number = Number(max)+1;
                }
            }
        }
        var title = jQuery('#jform_title').val();
        jQuery('.title-form').find('h1 .title').text(title);
        var titleSettings = jQuery('#jform_title_settings').val();
        jQuery('.title-form').find('h1').removeAttr('style');
        jQuery('.title-form').find('h1').attr('style', titleSettings);
        jQuery('.droppad_item').each(function(){
            jQuery(this).find('.label-item').css('font-size', formSettings[1]);
            jQuery(this).find('.label-item').css('color', formSettings[2]);
            jQuery(this).find('.label-item').css('font-weight', formSettings[10]);
            var me = jQuery(this).find("[class*=ba]")[0];
            var type = jQuery.trim(me.className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
            if (type == 'email' || type == 'textarea' || type == 'textInput' || type == 'address') {
                jQuery(this).find('.ba-'+type).css('height', formSettings[3]);
                jQuery(this).find('.ba-'+type).css('font-size', formSettings[4]);
                jQuery(this).find('.ba-'+type).css('color', formSettings[5]);
                jQuery(this).find('.ba-'+type).css('background-color', formSettings[6]);
                jQuery(this).find('.ba-'+type).css('border', formSettings[7].substring(8));
                jQuery(this).find('.ba-'+type).css('border-radius', formSettings[8]);
            } else if (type == 'date') {
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('height', formSettings[3]);
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('font-size', formSettings[4]);
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('color', formSettings[5]);
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('background-color', formSettings[6]);
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('border', formSettings[7].substring(8));
                jQuery(this).find('.ba-'+type+' input[type="text"]').css('border-radius', formSettings[8]);
            }
            if (type == 'dropdown' || type == 'selectMultiple') {
                jQuery(this).find('.ba-'+type).css('font-size', formSettings[4]);
                jQuery(this).find('.ba-'+type).css('color', formSettings[5]);
                jQuery(this).find('.ba-'+type).css('background-color', formSettings[6]);
                jQuery(this).find('.ba-'+type).css('border', formSettings[7].substring(8));
            }
            if (type == 'dropdown') {
                jQuery(this).find('.ba-'+type).css('height', formSettings[3]);
            }
            if (type == 'checkMultiple' || type == 'chekInline' || type == 'radioInline' ||
            type == 'radioMultiple') {
                jQuery(this).find('.ba-'+type).css('font-size', formSettings[4]);
                jQuery(this).find('.ba-'+type).css('color', formSettings[5]);
            }
            
        });
        jQuery('.container-icon .icons-cell i').css({
            'color' : iconFontColor,
            'font-size' : iconFontSize+'px'
        });
        drawBaHtml()
        main();
        sortDrop ();
        formId = '';
        formsSettings();
        displayFormSubmitBtn();
        jQuery('input[name="lable-weight"]').each(function(){
            if (jQuery(this).val() == labelFontWeight) {
                jQuery(this).attr('checked', true);
            }
        });
    }

    jQuery('input[name="lable-weight"]').each(function(){
        if (jQuery(this).val() == labelFontWeight) {
            jQuery(this).attr('checked', true);
        }
    });
    
    function drawBaHtml()
    {
        jQuery('.ba-htmltext').each(function(){
            var value = jQuery(this).parent().find('> .ba-options').val();
            jQuery(this).html(value)
        })
    }
    
    //function what draw the coluns and page break
    function drawHTMLColumn()
    {
        var columns = jQuery('#jform_form_columns').val(),
            str = '',
            k = '',
            n = '+',
            parent = jQuery('#content-section'),
            flag = false;
        if (columns.indexOf('first') >= 0) {
            flag = true;
        }
        columns = columns.split('|');
        parent.empty();
        for (var i = 0; i < columns.length-1; i++) {
            if(columns[i] != '') {
                var column = columns[i].split(',');
                if (!flag) {
                    if (column[1] == 'span12') {
                        str += '<div class="row-fluid"><div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                        str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                        str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div></div></div>';
                    }
                    if (jQuery.trim(column[1]) == 'span6') {
                        if (k == 1) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                            str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div></div></div>';
                            k = '';
                            continue;
                        }
                        if (k == '') {
                            str += '<div class="row-fluid"><div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 1;
                            continue;
                        }
                    }
                    if (jQuery.trim(column[1]) == 'span4') {
                        if (k == '') {
                            str += '<div class="row-fluid"><div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 1;
                            continue;
                        }
                        if (k == 1) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 2;
                            continue;
                        }
                        if (k == 2) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                            str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div></div></div>';
                            k = '';
                            continue;
                        }
                    }
                    if (jQuery.trim(column[1]) == 'span3') {
                        if (k == '') {
                            str += '<div class="row-fluid"><div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 1;
                            continue;
                        }
                        if (k == 1) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 2;
                            continue;
                        }
                        if (k == 2) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '</div>';
                            k = 3;
                            continue;
                        }
                        if (k == 3) {
                            str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                            str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div></div></div>';
                            k = '';
                            continue;
                        }
                    }
                } else {
                    if (jQuery.trim(column[1]) != 'spank') {
                        if (column[1] == 'span12') {
                            str += '<div class="row-fluid"><div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                            str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                            str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div></div></div>';
                            continue;
                        }
                        if (column[2] == 'first') {
                            str += '<div class="row-fluid">';
                        }
                        str += '<div class="'+column[1]+' droppad_area items" id="'+column[0]+'">';
                        if (column[2] == 'last') {
                            str += '<div class="ba-edit-row"><a class="zmdi zmdi-arrows"></a>';
                            str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div>'
                        }
                        str += '</div>';
                        if (column[2] == 'last') {
                            str += '</div>';
                        }
                    }
                }
                if (jQuery.trim(column[1]) == 'spank') {
                    if (column.length > 6) {
                        column[3] = column[3]+','+column[4]+','+column[5]+','+column[6];
                        column[3] += ','+column[7]+','+column[8]+','+column[9];
                        column[4] = column[10];
                        column[5] = column[11]+','+column[12]+','+column[13]+','+column[14];
                        column[5] += ','+column[15]+','+column[16]+','+column[17];
                    }
                    var prev = column[3].split(';'),
                        next = column[5].split(';');
                    if (prev[3].indexOf('rgb') < 0) {
                        prev[3] = '#'+prev[3];
                    }
                    if (prev[4].indexOf('rgb') < 0) {
                        prev[4] = '#'+prev[4];
                    }
                    if (next[3].indexOf('rgb') < 0) {
                        next[3] = '#'+next[3];
                    }
                    if (next[4].indexOf('rgb') < 0) {
                        next[4] = '#'+next[4];
                    }
                    str += '<div class="break items" id="'+column[0]+'"><div>';
                    str += '<div class="ba-edit-row">    <a class="zmdi zmdi-arrows"></a>';
                    str += '<a href="#" class="delete-layout zmdi zmdi-close"></a></div><div class="ba-prev">';
                    str += '<input type="button" class="btn-prev" value="';
                    str += prev[0]+ '" id="'+column[2]+'" style="font-size:';
                    str += prev[5]+'px; font-weight:'+prev[6]+'; width:';
                    str += prev[1]+'px; height:'+prev[2]+'px; background-color:';
                    str += prev[3]+'; color:'+prev[4]+'; border-radius:'+prev[7]+'px">';
                    str += '<input type="hidden" value="'+column[3]+'"></div>';
                    str += '<div class="ba-next">';
                    str += '<input type="button" class="btn-next" value="';
                    str += next[0]+ '" id="'+column[4]+'" style="font-size:';
                    str += next[5]+'px; font-weight:'+next[6]+'; width:';
                    str += next[1]+'px; height:'+next[2]+'px; background-color:';
                    str += next[3]+'; color:'+next[4]+'; border-radius:'+next[7]+'px">';
                    str += '<input type="hidden" value="'+column[5]+'"></div>';
                    str += '</div></div>';
                    jQuery('.ba-prev').find('[type="hidden"]').val(column[3])
                    jQuery('.ba-next').find('[type="hidden"]').val(column[5])
                    var id = column[2].substr(5);
                    if (Number(number) < Number(id)) {
                        number = Number(id)+1;
                    }
                    id = column[4].substr(5);
                    if (Number(number) <= Number(id)) {
                        number = Number(id)+1;
                    }
                }
            }
        }
        parent.html(str);
        var id = 0;
        jQuery('.droppad_area').each(function (){
            var col = jQuery(this).attr('id');
            col = col.substring(9);
            if (Number(col)>id) {
                id = Number(col)+1;
            }
        });
        parent.find('.row-fluid').each(function(){
            var childrens = jQuery(this).children();
            if (childrens.length > 1 && childrens.length < 4) {
                childrens.first().append('<span class="ba-right-resize"></span>');
                childrens.last().prepend('<span class="ba-left-resize"></span>');
                if (childrens.length == 3) {
                    jQuery(childrens[1]).append('<span class="ba-right-resize"></span>')
                        .prepend('<span class="ba-left-resize"></span>');
                }
            }
        });
        columnNumber = id+1;
        breakEvent();
        saveFormColumns();
    }
    
    //function what check it is a new form or loaded, to the draw elements of loaded
    function checkItems()
    {
        if (megaflag2 == '') {
            megaflag2 = '+'
            return true;
        }
    }
    
    //check it is a new form or loaded
    if (formId != '') {
        var megaflag = '';
    } else {
        var megaflag = '+';
    }
    if (formId != '') {
        var megaflag2 = '';
    } else {
        var megaflag2 = '+';
    }
    
    //function what check it is a new form or loaded to the draw columns of loaded
    function checkColumn() 
    {
        if (megaflag == '') {
            megaflag = '+';
            return true;
        }
    }
    
    //function what save the droppad items to database
    function saveDroppadItems()
    {
        if (formId == '') {
            var align = jQuery('#baform-0').parent().attr('style'),
                style = jQuery('#baform-0').attr('style'),
                name = jQuery('#baform-0').val(),
                obj = {
                    settings : 'button_-_'+name+'_-_'+style+'_-_'+align,
                    id : buttonId
                },
                str = JSON.stringify(obj);
            jQuery('.droppad_item').each(function(i, element) {
                var parent = jQuery(element).parent().attr('id'),
                    newType = jQuery.trim(jQuery(element).find("[class*=ba]").not('[type=hidden]')[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1]),
                    id = jQuery(element).attr('id'),
                    newOptions = jQuery(element).find('> .ba-options').val(),
                    content = jQuery('#jform_form_content').val(),
                    itemId = jQuery(element).find('> .ba-options').attr('data-id'),
                    cond = '',
                    conditions = '';
                if (jQuery(element).parent().attr('data-condition')) {
                    parent = jQuery(element).parent().closest('.droppad_item').attr('id');
                    cond = jQuery(element).parent().attr('data-condition');
                }
                if (jQuery(element).find(' > .condition-area').length > 0) {
                    jQuery(element).find(' > .condition-area').each(function(){
                        if (jQuery(this).find('.droppad_item').length == 0) {
                            return;
                        }
                        conditions += jQuery(this).attr('data-condition')+';';
                    });
                    conditions = conditions.substr(0, conditions.length - 1);
                }                
                if (!itemId) {
                    itemId = 0;
                }
                var sett = parent+'_-_'+id+'_-_'+newType+'_-_'+newOptions+'_-_'+cond+'_-_'+conditions;
                obj = {
                    settings : sett,
                    id : itemId
                }
                str += '|_-_|'+JSON.stringify(obj);
            });
            jQuery('#jform_form_content').val(str);
        }
    }
    
    var deleteTarget = "";

    function conditionShow()
    {
        jQuery('#cheсk-options .condition-cell label').off('click')
        jQuery('#cheсk-options .condition-cell label').on('click', function(){
            var condition = jQuery(this).parent().attr('data-condition'),
                value = jQuery(this).closest('tr').find('.label-cell input').val(),
                height = 20,
                flag = false,
                id = jQuery('#item-id').val(),
                parent = jQuery('#'+id);
                condArea = jQuery('#'+id).find(' > [data-condition="'+condition+'"]');
            jQuery('#cheсk-options .condition-cell label').removeClass('active');
            if (condArea.find('> .conditional-header').length == 0) {
                condArea.prepend('<p class="conditional-header"></p>');
            }
            condArea.find('> .conditional-header').text('If "'+value+'" is selected, then will be displayed:');
            /*parent.parentsUntil('.items').each(function(){
                this.style.height = '';
            });*/
            condArea[0].style.height = '';
            var h = condArea.innerHeight();
            if (!condArea.hasClass('selected')) {
                flag = true;
            }
            if (parent.find('> .condition-area.selected').length > 0) {
                parent.find('> .condition-area.selected').addClass('close-condition');
                height = parent.find('.close-condition').height();
                condArea.height(height);
                parent.find('> .condition-area').removeClass('selected');
            }
            if (!flag) {
                parent.css('margin-bottom', height+'px');
                parent.animate({
                    'margin-bottom' : 20
                }, 600, function(){
                    parent.find('.close-condition').removeClass('close-condition');
                });
            } else {
                condArea.addClass('selected');
                h = condArea.innerHeight();
                condArea.height(0);
                jQuery(this).addClass('active');
                condArea.animate({
                    'height' : h
                }, 600, function(){
                    parent.find('.close-condition').removeClass('close-condition');
                    this.style.height = '';
                    var top = condArea.position().top;
                    condArea.css('top', top+'px');
                })
            }
            jQuery(".condition-area").not('.selected').sortable( "option", "disabled", true );
            jQuery('.condition-area.selected').sortable( "option", "disabled", false );
            baGmap();
        });
    }
    
    function customize(type, id)
    {
        jQuery("#myTab").tabs({ active: 0 });
        jQuery('#breaker-options').hide();
        jQuery('#delete-buttons').show();
        jQuery('.title-options').hide();
        jQuery('#select-size').hide();
        jQuery('#button-otions').hide();
        jQuery('#text-lable').show();
        jQuery('#delete-item').show();
        jQuery('#text-description').hide();
        jQuery('#item-id').val(id);
        jQuery('#type-item').val(type);
        jQuery('#email-checkbox, .total-options').hide();
        var labelText = jQuery('#' +id).find('> .ba-options').val();
        labelText = labelText.split(';');
        labelText = labelText[0];
        if (type == 'textarea' || type == 'textInput' || type == 'chekInline' ||
            type == 'checkMultiple' || type == 'radioInline' ||
            type == 'radioMultiple' || type == 'dropdown' || type == 'selectMultiple') {
            var elem = jQuery('#'+id).find('> .ba-options').val();
            elem = elem.split(';');
        }
        if (type == 'textInput') {
            jQuery('#input-type').show();
            var inputOptions = jQuery('#'+id).find('> .ba-options').val();
            inputOptions = inputOptions.split(';');
            if (inputOptions[4]) {
                jQuery('.input-type option').each(function(){
                    if (inputOptions[4] == jQuery(this).val()) {
                        jQuery(this).attr('selected', true);
                    } else {
                        jQuery(this).removeAttr('selected');
                    }
                });
            } else {
                inputOptions[4] = 'regular';
                inputOptions = inputOptions.join(';');
                jQuery('#'+id).find('> .ba-options').val(inputOptions);
                jQuery('.input-type option[value="regular"]').attr('selected', true);
                jQuery('.input-type option[value="number"]').show();
            }
        } else {
            jQuery('#input-type').hide();
        }
        if (type == 'email') {
            labelText = labelText.substring(0, labelText.length-2);
        }
        if (type == 'textarea' || type == 'textInput' ||
            type == 'email' || type == 'dropdown' || type == 'address') {
            jQuery('#icons-select').show();
        } else {
            jQuery('#icons-select').hide();
        }
        if (type == 'slider') {
            var opt = jQuery('#'+id).find('> .ba-options').val();
            opt = opt.split(';');
            labelText = opt[0];
        }
        var title = jQuery('#' +id).find('> .ba-options').val();
        title = title.split(';');
        jQuery('#text-lable').find('[name="label"]').val(labelText);
        var title = jQuery('#' +id).find('> .ba-options').val();
        title = title.split(';');
        title = title[1];
        jQuery('#text-description').find('input').val(title);
        jQuery('#map-options').hide();
        jQuery('#textarea-options').hide();
        if (type == 'radioInline' || type == 'radioMultiple' || type == 'dropdown') {
            jQuery('#cheсk-options .condition-logic').parent().show();
        } else {
            jQuery('#cheсk-options .condition-logic').parent().hide();
        }
        if (type == 'date') {
            jQuery('#text-description').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').hide();
            jQuery('#required').show();
            jQuery('#slider-options').hide();
            jQuery('#html-options').hide();
            jQuery('#upload-options').hide();
            var dateOptions = jQuery('#'+id).find('> .ba-options').val();
            dateOptions = dateOptions.split(';');
            if (dateOptions[1] == 1) {
                jQuery('#required input[name="required"]').attr('checked', true);
            } else {
                jQuery('#required input[name="required"]').removeAttr('checked');
            }
        }
        if (type == 'htmltext') {
            jQuery('#text-lable').hide();
            jQuery('#text-description').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').hide();
            jQuery('#required').hide();
            jQuery('#slider-options').hide();
            jQuery('#html-options').show();
            var val = jQuery('#'+id).find('> .ba-options').val();
            CKE.setData(val);
            jQuery('#upload-options').hide();
        }
        if (type == 'textarea') {
            jQuery('#textarea-options').removeAttr('style');
            var val = jQuery('#'+id).find('> .ba-options').val();
            val = val.split(';')
            jQuery('#textarea-options').find('input').val(val[4]);
        }
        if (type == 'map') {
            var height = jQuery('#'+id).height();
            jQuery('#map-height').val(height);
            var mapCheckBoxes = jQuery('#'+id).find('> .ba-options').val();
            mapCheckBoxes = mapCheckBoxes.split(';');
            if (!mapCheckBoxes[8]) {
                mapCheckBoxes[8] = 1;
            }
            if (!mapCheckBoxes[9]) {
                mapCheckBoxes[9] = 1;
            }
            
            if (mapCheckBoxes[8] == 0) {
                jQuery('#map-options').find('.map-zooming').removeAttr('checked');
            } else {
                jQuery('#map-options').find('.map-zooming').attr('checked', true);
            }
            if (mapCheckBoxes[9] == 0) {
                jQuery('#map-options').find('.map-draggable').removeAttr('checked');
            } else {
                jQuery('#map-options').find('.map-draggable').attr('checked', true);
            }
            if (mapCheckBoxes[5] == 0) {
                jQuery('#map-options').find('[name="infobox"]').removeAttr('checked');
            } else {
                jQuery('#map-options').find('[name="infobox"]').attr('checked', true);
            }
            if (mapCheckBoxes[6] == 0) {
                jQuery('#map-options').find('[name="controls"]').removeAttr('checked');
            } else {
                jQuery('#map-options').find('[name="controls"]').attr('checked', true);
            }
            jQuery('#jform_marker_image').val(mapCheckBoxes[7])
            jQuery('#map-options').show();
            jQuery('#html-options').hide();
            jQuery('#text-lable').hide();
            jQuery('#text-description').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').hide();
            jQuery('#required').hide();
            jQuery('#slider-options').hide();
            jQuery('#upload-options').hide();
        }
        if (type == 'image') {
            var opt = jQuery('#'+id).find('> .ba-options').val();
            opt = opt.split(';');
            jQuery('#jform_select_image').val(opt[0]);
            jQuery('.image-align option').each(function(){
                if (jQuery(this).val() == opt[1]) {
                    jQuery(this).attr('selected', true);
                } else {
                    jQuery(this).removeAttr('selected');
                }
            });
            jQuery('.ba-width-number-image').val(opt[2]);
            jQuery('.image-alt').val(opt[3]);
            if (opt[4] == 1) {
                jQuery('.enable-lightbox').attr('checked', true);
                jQuery('.image-lightbox-bg').removeAttr('disabled');
            } else {
                jQuery('.enable-lightbox').removeAttr('checked');
                jQuery('.image-lightbox-bg').attr('disabled', true);
            }
            subColor = rgb2hex(opt[5]);
            jQuery('.image-lightbox-bg').minicolors('value', subColor[0]);
            jQuery('.image-lightbox-bg').minicolors('opacity', subColor[1]);
            jQuery('.image-options').show();
            jQuery('#text-lable, #map-options, #html-options').hide();
            jQuery('#place-hold, #cheсk-options, #text-description').hide();
            jQuery('#upload-options, #slider-options, #required').hide();
        } else {
            jQuery('.image-options').hide();
        }
        if (type == 'slider') {
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').hide();
            jQuery('#required').hide();
            jQuery('#slider-options').show();
            jQuery('#html-options').hide();
            jQuery('#upload-options').hide();
            var opt = jQuery('#'+id).find('> .ba-options').val();
            opt = opt.split(';');
            jQuery('#step').val(opt[4]);
            jQuery('#max').val(opt[3]);
            jQuery('#min').val(opt[2])
        }
        if (type == 'textarea' || type == 'textInput' || type == 'address') {
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#slider-options').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').show();
            jQuery('#required').show();
            jQuery('#html-options').hide();
            var placeHolder = jQuery('#'+id).find('[placeholder]').attr('placeholder');
            if (placeHolder) {
                jQuery('#place-hold').find('[name="place"]').val(placeHolder);
            } else {
                jQuery('#place-hold').find('[name="place"]').val("");
            }
            jQuery('#upload-options').hide();
            requirIt(id);
        }
        if (type == 'email') {
            jQuery('#email-checkbox').show();
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#slider-options').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#place-hold').show();
            jQuery('#required').hide();
            jQuery('#html-options').hide();
            var placeHolder = jQuery('#'+id).find('[placeholder]').attr('placeholder');
            if (placeHolder) {
                jQuery('#place-hold').find('[name="place"]').val(placeHolder);
            } else {
                jQuery('#place-hold').find('[name="place"]').val("");
            }
            jQuery('#upload-options').hide();
        }
        if (type == 'dropdown' || type == 'selectMultiple') {
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#slider-options').hide();
            jQuery('#cheсk-options').show();
            jQuery('#place-hold').hide();
            jQuery('#required').show();
            jQuery('#html-options').hide();
            var options = '',
                table = jQuery('#cheсk-options .items-list'),
                flag = jQuery('#jform_display_total').prop('checked'),
                symbol = jQuery('#jform_currency_symbol').val(),
                label,
                price;
            table.empty();
            jQuery('#'+id).find(" > select, >.container-icon > select").find('option').each(function(i, el){
                if (jQuery(this).attr('data-dropdown-select')) {
                    return;
                }
                label = jQuery(el).text();
                price = jQuery(this).attr('data-price');
                if (flag && price) {
                    var repl = ' - ';
                    if (jQuery('#jform_currency_position').val() == 'before') {
                        repl += symbol;
                    }
                    repl += price;
                    if (jQuery('#jform_currency_position').val() != 'before') {
                        repl += symbol;
                    }
                    label = label.replace(repl, '');
                }
                options += '<tr><td><input type="checkbox" class="list-item">';
                options += '</td><td class="label-cell"><input type="text"';
                options += ' value=""></td><td class="price-cell"><input';
                options += ' placeholder="$0.00" type="number" step="0.01" min="0" value=""></td>';
                if (jQuery('#'+id).find(' > [data-condition="'+(i - 1)+'"]').length > 0) {
                    options += '<td class="condition-cell" data-condition="'+(i - 1);
                    options += '"><label class="button-alignment ba-btn';
                    if (jQuery('#'+id).find(' > [data-condition="'+ (i - 1) +'"]').hasClass('selected')) {
                        options += ' active';
                    }
                    options += '"><span class="ba-tooltip">'+hidden_fields+'</span><i ';
                    options += 'class="zmdi zmdi-arrow-split"></i></label></td>';
                }
                if (type == 'dropdown') {
                    if (elem[5] && elem[5] == i - 1) {
                        options += '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                    }
                } else {
                    if (elem[5] && elem[5] == i) {
                        options += '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                    }
                }                
                options += '</tr>';
                table.append(options);
                table.find('.label-cell input').last().val(jQuery.trim(label));
                if (price) {
                    table.find('.price-cell input').last().val(price)
                }
                options = '';
            });
            jQuery('#upload-options').hide();
            requirIt(id);
        }
        if (type == 'selectMultiple') {
            jQuery('#select-size').show();
            var selectSize = jQuery('#'+id).find('select').attr('size');
            jQuery('#select-size').find('input').val(selectSize);
        }
        if (type == 'chekInline' || type == 'checkMultiple' || type == 'radioInline' ||
            type == 'radioMultiple') {
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#slider-options').hide();
            jQuery('#cheсk-options').show();
            jQuery('#place-hold').hide();
            jQuery('#required').show();
            jQuery('#html-options').hide();
            var options = '',
                table = jQuery('#cheсk-options .items-list'),
                flag = jQuery('#jform_display_total').prop('checked'),
                symbol = jQuery('#jform_currency_symbol').val(),
                label,
                price;
            table.empty();
            jQuery('#'+id).find('> .ba-'+type+' span').each(function(i, el){
                label = jQuery(el).text();
                price = jQuery(this).find('input').attr('data-price');
                if (flag && price) {
                    var repl = ' - ';
                    if (jQuery('#jform_currency_position').val() == 'before') {
                        repl += symbol;
                    }
                    repl += price;
                    if (jQuery('#jform_currency_position').val() != 'before') {
                        repl += symbol;
                    }
                    label = label.replace(repl, '');
                }
                options += '<tr><td><input type="checkbox" class="list-item">';
                options += '</td><td class="label-cell"><input type="text"';
                options += ' value=""></td><td class="price-cell"><input';
                options += ' placeholder="$0.00" type="number" step="0.01" min="0" value=""></td>'
                if (jQuery('#'+id).find(' > [data-condition="'+i+'"]').length > 0) {
                    options += '<td class="condition-cell" data-condition="'+i;
                    options += '"><label class="button-alignment ba-btn';
                    if (jQuery('#'+id).find(' > [data-condition="'+i+'"]').hasClass('selected')) {
                        options += ' active';
                    }
                    options += '"><span class="ba-tooltip">'+hidden_fields+'</span><i ';
                    options += 'class="zmdi zmdi-arrow-split"></i></label></td>';
                }
                if (elem[4] && elem[4] == i) {
                    options += '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                }
                options += '</tr>';
                table.append(options);
                table.find('.label-cell input').last().val(jQuery.trim(label));
                if (price) {
                    table.find('.price-cell input').last().val(price)
                }
                options = '';
            });
            jQuery('#upload-options').hide();
            requirIt(id);
        }
        if (type == 'upload') {
            jQuery('#text-lable').show();
            jQuery('#text-description').show();
            jQuery('#slider-options').hide();
            jQuery('#place-hold').hide();
            jQuery('#cheсk-options').hide();
            jQuery('#required').hide();
            jQuery('#html-options').hide();
            jQuery('#upload-options').show();
            var option = jQuery('#'+id).find('> .ba-options').val();
            option = option.split(';');
            jQuery('#upl-size').val(option[2]);
            jQuery('#upl-types').val(option[3]);
            jQuery('#required').show();
            if (option[4] == 1) {
                jQuery('#required input').attr('checked', true);
            } else {
                jQuery('#required input').removeAttr('checked');
            }
        }
        if (jQuery('#'+id).closest('.droppad_area').hasClass('condition-area')) {
            jQuery('.select-default').closest('label').hide();
        } else {
            jQuery('.select-default').closest('label').show();
        }
        conditionShow();
    }

    jQuery('.image-lightbox-bg').minicolors({
        opacity : true,
        change : function(){
            var id = jQuery('#item-id').val(),
                options = jQuery('#'+id).find('> .ba-options').val();
            options = options.split(';');
            options[5] = jQuery(this).minicolors('rgbaString');
            options = options.join(';');
            jQuery('#'+id).find('> .ba-options').val(options);
            saveDroppadItems();
        }
    });

    jQuery('.enable-lightbox').on('click', function(){
        var id = jQuery('#item-id').val(),
            options = jQuery('#'+id).find('> .ba-options').val();
        options = options.split(';');
        if (jQuery(this).prop('checked')) {
            options[4] = 1;
            jQuery('.image-lightbox-bg').removeAttr('disabled');
        } else {
            options[4] = 0;
            jQuery('.image-lightbox-bg').attr('disabled', true);
        }
        options = options.join(';');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('.image-alt').on('keyup', function(){
        var id = jQuery('#item-id').val(),
            options = jQuery('#'+id).find('> .ba-options').val();
        options = options.split(';');
        options[3] = jQuery(this).val().replace(new RegExp(";",'g'), '');
        jQuery('#'+id).find('img').attr('alt', options[3]);
        options = options.join(';');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('.ba-width-number-image').on('click keyup', function(){
        var id = jQuery('#item-id').val(),
            options = jQuery('#'+id).find('> .ba-options').val(),
            value = jQuery(this).val();
        options = options.split(';');
        if (value < 0) {
            value = 0;
        } else if (value > 100) {
            value =  100;
        }
        options[2] = value;
        options = options.join(';');
        jQuery('#'+id).find('img').css('width', value+'%');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('.image-align').on('change', function(){
        var id = jQuery('#item-id').val(),
            options = jQuery('#'+id).find('> .ba-options').val();
        options = options.split(';');
        options[1] = jQuery(this).val();
        jQuery('#'+id).css('text-align', options[1]);
        options = options.join(';');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('#jform_select_image').on('change', function(){
        var id = jQuery('#item-id').val(),
            options = jQuery('#'+id).find('> .ba-options').val();
        options = options.split(';');
        options[0] = jQuery(this).val();
        jQuery('#'+id).find('img').attr('src', '../'+options[0]);
        options = options.join(';');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('#icons-select input.ba-btn').on('click', function(){
        uploadMode = 'iconUpload';
        jQuery('#icons-upload-modal').modal();
    });

    jQuery('.clear-icon').on('click', function(event){
        event.preventDefault();
        var item = jQuery('#item-id').val(),
            options = jQuery('#'+item).find('> .ba-options').val(),
            type = jQuery('#type-item').val();
        options = options.split(';');
        if (type == 'textInput' || type == 'textarea') {
            options[5] = '';
        } else if (type == 'email') {
            options[3] = '';
        } else {
            options[4] = '';
        }
        if (jQuery('#'+item).find('.icons-cell').length > 0) {
            var itm = jQuery('#'+item).find('.ba-'+type)[0];
            jQuery('#'+item).find('> .ba-options').before(itm);
            jQuery('#'+item).find('.container-icon').remove();
        }
        options = options.join(';');
        jQuery('#'+item).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('#icons-upload-modal').on('show', function(){
        window.addEventListener("message", listenMessage, false);
    });
    jQuery('#icons-upload-modal').on('hide', function(){
        window.removeEventListener("message", listenMessage, false);
    });

    function listenMessage(event)
    {
        if (event.origin == location.origin) {
            if (uploadMode == 'iconUpload') {
                if (event.data != '') {
                    var item = jQuery('#item-id').val(),
                        options = jQuery('#'+item).find('> .ba-options').val(),
                        type = jQuery('#type-item').val(),
                        div = document.createElement('div'),
                        container = document.createElement('div'),
                        icon = document.createElement('i');
                    container.className = 'container-icon';
                    container.appendChild(jQuery('#'+item+' .ba-'+type)[0]);
                    container.appendChild(div);
                    icon.className = event.data;
                    div.className = 'icons-cell';
                    div.appendChild(icon);
                    options = options.split(';');
                    if (type == 'textInput' || type == 'textarea') {
                        options[5] = event.data;
                    } else if (type == 'email') {
                        options[3] = event.data;
                    } else {
                        options[4] = event.data;
                    }
                    jQuery('#'+item).find('.container-icon').remove();
                    options = options.join(';');
                    jQuery('#'+item).find('> .ba-options').val(options);
                    jQuery('#'+item).find('> .ba-options').before(container);
                    jQuery('#'+item+' .icons-cell i').css({
                        'color' : iconFontColor,
                        'font-size' : iconFontSize+'px'
                    });
                    saveDroppadItems();
                }
            } else if (uploadMode == 'CKE') {
                if (event.data != '') {
                    var flag = true,
                        frame = jQuery('#html-editor iframe'),
                        doc;
                    if (frame.length == 0) {
                        flag = false;
                    } else { 
                        doc = frame[0].contentDocument;
                    }
                    if (flag && doc.getSelection().rangeCount > 0) {
                        var iRange = doc.getSelection().getRangeAt(0),
                            i = doc.createElement('i')
                        i.className = event.data;
                        iRange.insertNode(i);
                    } else {
                        var data = CKE.getData();
                        data+= '<i class="'+event.data+'"></i>';
                        CKE.setData(data);
                    }
                }
            }
            
        }
        jQuery('#icons-upload-modal').modal('hide');
    }
    
    jQuery('#select-size').find('input').on('click keyup', function(){
        var select = jQuery(this).val();
        var item = jQuery('#item-id').val();
        jQuery('#'+item).find('select').attr('size', select);
        var opt = jQuery('#'+item).find('> .ba-options').val();
        opt = opt.split(';');
        opt[4] = select;
        opt = opt.join(';');
        jQuery('#'+item).find('> .ba-options').val(opt);
        saveDroppadItems();
    });
    
    function saveDroppadOptions (type, id)
    {
        var option = '',
            labelText = jQuery('#' +id).find('.label-item').text();
        if (type == 'email') {
            option = labelText+';;;0';
        }
        if (type == 'textInput') {
            option = labelText+';;;0;regular';
        }
        if (type == 'address') {
            option = labelText+';;;0;zmdi zmdi-pin';
        }
        if (type == 'textarea') {
            option = labelText+';;;0;120';
        }
        if (type == 'dropdown' || type == 'chekInline' || type == 'checkMultiple' ||
            type == 'radioInline' || type == 'radioMultiple') {
            option = labelText+';;'+JSON.stringify("Option 1\nOption 2\nOption 3")+';0';
        }
        if (type == 'selectMultiple') {
            option = labelText+';;'+JSON.stringify("Option 1\nOption 2\nOption 3")+';0;3';
        }
        if (type == 'map') {
            option = ';;;100;400;0;1;';
        }
        if (type == 'slider') {
            option = labelText+';;0;50;1';
        }
        if (type == 'htmltext') {
            option = 'Text';
        }
        if (type == 'upload') {
            option = 'Upload file button;;5;jpg, jpeg, png, pdf, doc'
        }
        if (type == 'date') {
            option = 'Calendar';
        }
        if (type == 'image') {
            option = 'components/com_baforms/assets/images/image-placeholder.jpg;center;100;;0;rgba(0, 0, 0, 0.6)';
        }
        addOptions (id, option);
        return option;
    }
    
    //add event to change the maximum upload size
    jQuery('#upl-size').on('click keyup', function(){
        var size = jQuery('#upl-size').val(),
            item = jQuery('#item-id').val(),
            option = jQuery('#'+item).find('> .ba-options').val();
        option = option.split(';');
        option[2] = size;
        jQuery('#'+item).find('span').text('Max. file size, '+size+'mb ('+option[3]+')');
        option = option.join(';');
        jQuery('#'+item).find('> .ba-options').val(option);
        saveDroppadItems();
    });
    
    //add event to change the alowed types of oploading
    jQuery('#upl-types').on('keyup', function(){
        var types = jQuery('#upl-types').val(),
            item = jQuery('#item-id').val(),
            option = jQuery('#'+item).find('> .ba-options').val();
        option = option.split(';');
        option[3] = types;
        jQuery('#'+item).find('span').text('Max. file size '+option[2]+'mb ('+option[3]+')');
        option = option.join(';');
        jQuery('#'+item).find('> .ba-options').val(option);
        saveDroppadItems();
    });
    
    //function to add options of droppad items
    function addOptions (id, option)
    {
        jQuery('#' +id).find('> .ba-options').val(option);
    }
    
    function checkTable()
    {
        var id = jQuery('#item-id').val(),
            type = jQuery('#type-item').val(),
            target = jQuery('#'+id).find('.ba-'+type)[0],
            names = new Array(),
            mainOptions = jQuery('#'+id).find('> .ba-options').val(),
            flag = jQuery('#jform_display_total').prop('checked');
        target = jQuery(target);
        if (type != 'dropdown') {
            target.empty();
        } else {
            target.find('option').each(function(){
                if (!jQuery(this).attr('data-dropdown-select')) {
                    jQuery(this.remove());
                }
            });
        }
        mainOptions = mainOptions.split(';');
        var conditionsArea = new Array(),
            values = new Array();
        jQuery('#cheсk-options .items-list tr').each(function(ind, el){
            if (jQuery(this).find('.condition-cell').length > 0) {
                var i = jQuery(this).find('.condition-cell').attr('data-condition');
                conditionsArea[ind] = jQuery('#'+id).find('> [data-condition="'+i+'"]');
                values[ind] = jQuery(this).find('.label-cell input').val();
                jQuery(this).find('.condition-cell').attr('data-condition', ind);
            }
            var label = jQuery(this).find('.label-cell input').val(),
                price = jQuery(this).find('.price-cell input').val(),
                str = '',
                symbol = jQuery('#jform_currency_symbol').val();;
            names.push(label+'===='+price);
            if (type == "dropdown" || type == "selectMultiple") {
                if (jQuery(this).find('.default-value').length > 0) {
                    mainOptions[5] = ind;
                }
                str += '<option';
                if (jQuery.trim(price)) {
                    str += ' data-price="'+jQuery.trim(price)+'"';
                }
                if (jQuery(this).find('.default-value').length > 0) {
                    str += ' selected';
                }
                str += '>'+jQuery.trim(label);
                if (jQuery.trim(price) && flag) {
                    str += ' - '
                    if (jQuery('#jform_currency_position').val() == 'before') {
                        str += symbol;
                    }
                    str += jQuery.trim(price);
                    if (jQuery('#jform_currency_position').val() != 'before') {
                        str += symbol;
                    }
                }
                str += '</option>';
            } else {
                if (jQuery(this).find('.default-value').length > 0) {
                    mainOptions[4] = ind
                }
                var inputType = '';
                if (type.indexOf('radio') < 0) {
                    inputType = 'checkbox';
                } else {
                    inputType = 'radio';
                }
                str += '<span><input type="'+inputType+'"';
                if (jQuery.trim(price)) {
                    str += ' data-price="'+jQuery.trim(price)+'"';
                }
                if (jQuery(this).find('.default-value').length > 0) {
                    str += ' class="checked-'+inputType+'"';
                }
                str += '>'+jQuery.trim(label);
                if (jQuery.trim(price) && flag) {
                    str += ' - '
                    if (jQuery('#jform_currency_position').val() == 'before') {
                        str += symbol;
                    }
                    str += jQuery.trim(price);
                    if (jQuery('#jform_currency_position').val() != 'before') {
                        str += symbol;
                    }
                }
                if (type.indexOf('Multiple') > 0) {
                    str += '<br>';
                }
                str += '</span>';
            }
            target.append(str);
        });
        conditionsArea.forEach(function(item, ind){
            item.attr('data-condition', ind);
            if (item.find('> .conditional-header').length > 0) {
                item.find('> .conditional-header').text('If "'+values[ind]+'" is selected, then will be displayed:');
            }
        })
        names = names.join('\\n');
        mainOptions[2] = names;
        mainOptions = mainOptions.join(';');
        jQuery('#'+id).find('> .ba-options').val(mainOptions);
        saveDroppadItems();
    }
    
    jQuery('#item-aply').on('click', function(){
        var value = jQuery('#add-item-modal textarea')[0].value;
        value = jQuery.trim(value);
        if (value) {
            value = value.split('\n');
            for (var i = 0; i < value.length; i++) {
                var str = '<tr><td><input type="checkbox" class="list-item"></td><td class';
                str += '="label-cell"><input type="text" value="'+value[i]+'"></td><td class="price-cell">';
                str += '<input placeholder="$0.00" type="number" step="0.01" min="0"></td></tr>';
                jQuery('#cheсk-options .items-list').append(str);
                checkTable();
            }
        }
        jQuery('#add-item-modal').modal('hide');
    });

    jQuery('#cheсk-options .item-add').on('click', function(){
        jQuery('#add-item-modal textarea').val('');
        jQuery('#add-item-modal').modal();
    });
    var deleteChecked = false;
    jQuery('.apply-condition-remove').on('click', function(event){
        event.preventDefault();
        var items = new Array(),
            id = jQuery('#item-id').val(),
            i;
        jQuery('#cheсk-options .items-list tr').each(function(ind, el){
            if (jQuery(this).find('.list-item').prop('checked')) {
                items.push(jQuery(this));
                i = ind;
                return false;
            }
        });
        jQuery(items[0]).find('.condition-cell').remove();
        jQuery('#'+id).find('> .droppad_area[data-condition="'+i+'"]').remove();
        jQuery('#condition-remove-dialog').modal('hide');
        saveDroppadItems();
    });

    jQuery('.select-default').on('click', function(){
        var items = new Array(),
            id = jQuery('#item-id').val(),
            i,
            options = jQuery('#'+id).find('> .ba-options').val(),
            type = jQuery('#type-item').val();
        options = options.split(';');
        jQuery('#cheсk-options .items-list tr').each(function(ind, el){
            if (jQuery(this).find('.list-item').prop('checked')) {
                items.push(jQuery(this));
                i = ind;
            }
        });
        if (items.length == 0 || items.length > 1) {
            jQuery('#conditional-notice-dialog').modal();
        } else if (items.length == 1) {
            if (type == 'radioInline' || type == 'radioMultiple') {
                var spans = jQuery('#'+id).find('> .ba-'+type+' > span');
                spans.find('input').removeClass('checked-radio');
                if (items[0].find('td').last().hasClass('default-value')) {
                    items[0].find('.default-value').remove();
                    options[4] = '';
                } else {
                    options[4] = i;
                    items[0].parent().find('.default-value').remove();
                    var str = '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                    items[0].find('td').last().after(str);
                    jQuery(spans[i]).find('input').addClass('checked-radio');
                }
            } else if (type == 'checkMultiple' || type == 'chekInline') {
                var spans = jQuery('#'+id).find('> .ba-'+type+' > span');
                spans.find('input').removeClass('checked-checkbox');
                if (items[0].find('td').last().hasClass('default-value')) {
                    items[0].find('.default-value').remove();
                    options[4] = '';
                } else {
                    options[4] = i;
                    items[0].parent().find('.default-value').remove();
                    var str = '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                    items[0].find('td').last().after(str);
                    jQuery(spans[i]).find('input').addClass('checked-checkbox');
                }
            } else {
                var spans = jQuery('#'+id).find('.ba-'+type)[0];
                spans = jQuery(spans).find('option');
                spans.removeAttr('selected');
                if (items[0].find('td').last().hasClass('default-value')) {
                    items[0].find('.default-value').remove();
                    options[5] = '';
                } else {
                    options[5] = i;
                    items[0].parent().find('.default-value').remove();
                    var str = '<td class="default-value"><i class="zmdi zmdi-star"></i></td>';
                    items[0].find('td').last().after(str);
                    if (type != 'dropdown') {
                        jQuery(spans[i]).attr('selected', true);
                    } else {
                        jQuery(spans[i + 1]).attr('selected', true);
                    }                    
                }
            }
        }
        options = options.join(';');
        jQuery('#'+id).find('> .ba-options').val(options);
        saveDroppadItems();
    });

    jQuery('#cheсk-options .condition-logic').on('click', function(){
        var items = new Array(),
            id = jQuery('#item-id').val(),
            i;
        jQuery('#cheсk-options .items-list tr').each(function(ind, el){
            if (jQuery(this).find('.list-item').prop('checked')) {
                items.push(jQuery(this));
                i = ind;
            }
        });
        if (items.length == 1) {
            var lastTd = items[0].find('.condition-cell');
            if (lastTd.hasClass('condition-cell')) {
                jQuery('#condition-remove-dialog').modal();
            } else {
                var str = '<div class="droppad_area condition-area" data-condition="'+i+'"></div>';
                jQuery('#'+id).append(str);
                str = '<td class="condition-cell" data-condition="'+i+'"><label class="';
                str += 'button-alignment ba-btn';
                if (jQuery('#'+id).find(' > [data-condition="'+i+'"]').hasClass('selected')) {
                    str += ' active';
                }
                str += '"><span class="ba-tooltip">'+hidden_fields;
                str += '</span><i class="zmdi zmdi-arrow-split"></i></label></td>'
                items[0].find('.price-cell').after(str);
                main();
            }
        } else {
            jQuery('#conditional-notice-dialog').modal();
        }
        conditionShow();
        if (jQuery('#'+id).find('> .condition-area').length > 0) {
            jQuery('#'+id).addClass('conditional-field');
        } else {
            jQuery('#'+id).removeClass('conditional-field');
        }

    });
    jQuery('#cheсk-options .item-delete').on('click', function(){
        var items = new Array();
        jQuery('#cheсk-options .items-list tr').each(function(){
            if (jQuery(this).find('.list-item').prop('checked')) {
                items.push(jQuery(this));
            }
        });
        if (items.length > 0) {
            deleteChecked = true;
            jQuery('#delete-dialog').modal();
        } else {
            jQuery('#select-dialog').modal();
        }
    });
    
    jQuery('#cheсk-options .items-list').on('keyup scroll click', '.label-cell input, .price-cell input', function(){
        checkTable();
    });

    jQuery('.input-type').on('change', function(){
        var value = jQuery(this).val(),
            item = jQuery('#item-id').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val();
        mainOptions = mainOptions.split(';');
        mainOptions[4] = value;
        mainOptions = mainOptions.join(';');
        jQuery('#'+item).find('> .ba-options').val(mainOptions);
        saveDroppadItems();
    });
    
    //add event to make item required
    jQuery('#required').find('[name="required"]').on('click', function(){
        var item = jQuery('#item-id').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val(),
            label = jQuery('#'+item).find('> .label-item').text(),
            type = jQuery('#type-item').val();
        if (type == 'date') {
            mainOptions = mainOptions.split(';');
            if (jQuery(this).prop('checked')) {
                mainOptions[1] = 1;
                if (label != '') {
                    label += ' *';
                    jQuery('#'+item).find('.label-item').text(label);
                }
            } else {
                mainOptions[1] = 0;
                if (label != '') {
                    label = label.substring(0, label.length - 1);
                    jQuery('#'+item).find('.label-item').text(label);
                }
            }
            mainOptions = mainOptions.join(';');
            jQuery('#'+item).find('> .ba-options').val(mainOptions);
        } else if (type == 'upload') {
            mainOptions = mainOptions.split(';');
            if (jQuery(this).prop('checked')) {
                mainOptions[4] = 1;
                if (label != '') {
                    label += ' *';
                    jQuery('#'+item).find('.label-item').text(label);
                }
            } else {
                mainOptions[4] = 0;
                if (label != '') {
                    label = label.substring(0, label.length - 1);
                    jQuery('#'+item).find('.label-item').text(label);
                }
            }
            mainOptions = mainOptions.join(';');
            jQuery('#'+item).find('> .ba-options').val(mainOptions);
        } else {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                if (mainOptions[3] == 1) {
                    mainOptions[3] = 0;
                    mainOptions = mainOptions.join(';');
                    jQuery('#'+item).find('> .ba-options').val(mainOptions);
                    jQuery('#'+item).find('> .label-item').text(label.substring(0, label.length-1));
                } else {
                    mainOptions[3] = 1
                    mainOptions = mainOptions.join(';');
                    jQuery('#'+item).find('> .ba-options').val(mainOptions);
                    if (label != '') {
                        label += ' *';
                    }
                    jQuery('#'+item).find('> .label-item').text(label);
                }
            } else {
                if (label != '') {
                    label += ' *';
                }
                jQuery('#'+item).find('> .ba-options').val(';;;1');
                jQuery('#'+item).find('.label-item').text(label);
            }
        }
        saveDroppadItems();
    });
    
    //function to save option of element
    function requirIt(id) 
    {
        var mainOptions = jQuery('#'+id).find('> .ba-options').val();
        if (mainOptions != '') {
            mainOptions = mainOptions.split(';');
            if (mainOptions[3] == 1) {
                jQuery('#required').find('[name="required"]').attr('checked', true);
            } else {
                jQuery('#required').find('[name="required"]').removeAttr('checked');
            }
        } else {
            jQuery('#required').find('[name="required"]').removeAttr('checked')
        }
    }
    
    /*
        open modal whith location options for map, when click on the button
    */
    jQuery('#map-location').click(function(){
        jQuery('#location-dialog').modal();
        var item = jQuery('#item-id').val(),
            mainoptions = jQuery('#'+item).find('> .ba-options').val(),
            image;
        mainoptions = mainoptions.split(';');
        if (mainoptions[7] != '') {
            image = '../'+mainoptions[7];
        } else {
            image = mainoptions[7];
        }
        jQuery('#mark-description').val(mainoptions[2]);
        jQuery('#marker-position').val(mainoptions[1]);
        //init map
        var item = jQuery('#item-id').val(),
            allOptions = mainoptions[0],
            mark = mainoptions[1],
            description = mainoptions[2];
        var object = {
            center : {
                lat : 42.345573,
                lng : -71.098326
            },
            mapTypeId: "roadmap",
            scrollwheel: true,
            navigationControl: true,
            mapTypeControl: true,
            scaleControl: true,
            draggable: true,
            zoomControl: true, 
            zoom: 14,
        }
        if (allOptions) {
            allOptions = JSON.parse(allOptions);
            if (typeof(allOptions.center) == 'string') {
                allOptions.center = allOptions.center.split(',');
                object.center = {
                    lat : allOptions.center[0]*1,
                    lng : allOptions.center[1]*1
                };
            } else {
                object.center = {
                    lat : allOptions.center.lat,
                    lng : allOptions.center.lng
                };
            }
            object.zoom = allOptions.zoom*1;
            object.mapTypeId = allOptions.mapTypeId;
        }
        var map = new google.maps.Map(document.getElementById('location-map'), object),
            input = document.getElementById('place'),
            autocomplete = new google.maps.places.Autocomplete(input),
            marker = '';
        if (mark != '') {
            mark = JSON.parse(mark);
            var keys = [];
            for (var key in mark) {
                keys.push(key);
            }
            marker = new google.maps.Marker({
                position: {
                    lat : mark[keys[0]]*1,
                    lng : mark[keys[1]]*1
                },
                map: map,
                icon : image
            });
        }
        map.addListener('maptypeid_changed',function(event){
            var flag = 0,
                location = {
                center : {},
                zoom : map.getZoom(),
                mapTypeId : map.getMapTypeId()
            };
            jQuery.each(map.getCenter(), function (i, val) {
                if (flag == 0) {
                    location.center.nb = val();
                } else if (flag == 1) {
                    location.center.ob = val();
                }
                flag++;
            });
            jQuery('#location-options').val(JSON.stringify(location));
        });
        map.addListener('idle',function(event){
            var flag = 0,
                location = {
                center : {},
                zoom : map.getZoom(),
                mapTypeId : map.getMapTypeId()
            };
            jQuery.each(map.getCenter(), function (i, val) {
                if (flag == 0) {
                    location.center.nb = val();
                } else if (flag == 1) {
                    location.center.ob = val();
                }
                flag++;
            });
            jQuery('#location-options').val(JSON.stringify(location));
        });
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
            }
        });
        map.addListener('click', function(event) {
            if (marker != '') {
                marker.setMap(null);
            }
            marker = new google.maps.Marker({
                position: event.latLng,
                map: map,
                icon : image
            });
            var latLng = {
                lat: event.latLng.lat(),
                lng: event.latLng.lng(),
            };
            jQuery('#marker-position').val(JSON.stringify(latLng));
        });
    });
    
    //save the selected parameter map after click on button aply
    jQuery('#aply-location').click(function(){
        var item = jQuery('#item-id').val(),
            location = jQuery('#location-options').val(),
            description = jQuery('#mark-description').val(),
            mark = jQuery('#marker-position').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val(),
            image = mainOptions.split(';'),
            flag = 0,
            allOption = {
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                zoomControl: false,
                disableDefaultUI: true,
                disableDoubleClickZoom: true
            };
        if (image[7] != ''){
            image = '../'+image[7];
        } else {
            image = image[7];
        }
        if (mainOptions) {
            flag = mainOptions.split(';');
            flag = flag[5];
        }
        location = JSON.parse(location);
        allOption.zoom = location.zoom*1;
        allOption.mapTypeId = location.mapTypeId;
        allOption.center = {
            lat : location.center.nb,
            lng : location.center.ob
        };
        mapRecreate(item, allOption, mark, image, description, flag);
        if (mainOptions) {
            mainOptions = mainOptions.split(';')
            jQuery('#'+item).find('> .ba-options').val(JSON.stringify(allOption)+';'+mark+';'+description+';'+
                                                     mainOptions[3]+';'+mainOptions[4]+
                                                     ';'+flag+';'+mainOptions[6]+';'+mainOptions[7]);
        } else {
            jQuery('#'+item).find('> .ba-options').val(JSON.stringify(allOption)+';'+mark+';'+
                                                     description+';;'+flag+';'+mainOptions[6]+';'+mainOptions[7]);
        }
        saveDroppadItems();
        jQuery('#location-dialog').modal('hide');
    });
    
    function mapRecreate(item, allOption, mark, image, description, flag)
    {
        var map = new google.maps.Map(jQuery('#'+item).find('.ba-map')[0], allOption),
            marker = '';
        if (mark) {
            mark = JSON.parse(mark);
            var keys = [];
            for (var key in mark) {
                keys.push(key);
            }
            marker = new google.maps.Marker({
                position: {
                    lat : mark[keys[0]]*1,
                    lng : mark[keys[1]]*1
                },
                map: map,
                icon : image
            });
            if (description != '') {
                var infowindow = new google.maps.InfoWindow({
                    content : description
                });
                if (flag == 1) {
                    infowindow.open(map, marker);
                }
                marker.addListener('click', function(event){
                    infowindow.open(map, marker);
                });
            }
        }
    }
    
    jQuery('#jform_marker_image').on('change', function(){
        var item = jQuery('#item-id').val(),
            options = jQuery('#'+item).find('> .ba-options').val();
        options = options.split(';');
        options[7] = jQuery(this).val();
        options = options.join(';');
        jQuery('#'+item).find('> .ba-options').val(options);
        var allOption = options.split(';'),
            mark = allOption[1],
            image,
            allOptions = allOption[0],
            description = allOption[2],
            flag = 0
        if (allOption[7] != '') {
            image = '../'+allOption[7];
        } else {
            image = allOption[7];
        }
        if (allOption) {
            flag = allOption[5]
        }
        if (allOptions) {
            allOptions = JSON.parse(allOptions);
            allOptions.zoom = allOptions.zoom*1;
            if (typeof(allOptions.center) != 'object') {
                allOptions.center = allOptions.center.split(',')
                allOptions.center = {
                    lat : allOptions.center[0]*1,
                    lng : allOptions.center[1]*1
                };
            }
        } else {
            allOptions = {
                center : {
                    lat : 42.345573,
                    lng : -71.098326
                },
                mapTypeId: "roadmap",
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                zoomControl: false,
                disableDefaultUI: true,
                disableDoubleClickZoom: true,
                zoom: 14,
            }
        }
        mapRecreate(item, allOptions, mark, image, description, flag);
        saveDroppadItems();
    });
    
    //change the height of map
    jQuery('#map-height').on('keyup', function(){
        var height = jQuery('#map-height').val();
        if (height == 0) {
            height = 10;
        }
        var item = jQuery('#item-id').val();
        jQuery('#'+item).find('.ba-map').css('height', height+'px');
        var allOption = jQuery('#'+item).find('> .ba-options').val(),
            allOption = allOption.split(';'),
            mark = allOption[1],
            image;
        if (allOption[7] != '') {
            image = '../'+allOption[7];
        } else {
            image = allOption[7];
        }
        var allOptions = allOption[0],
            description = allOption[2],
            mainOptions = allOption,
            flag;
        if (mainOptions) {
            flag = mainOptions[5]
        }
        if (allOptions) {
            allOptions = JSON.parse(allOptions);
            allOptions.zoom = allOptions.zoom*1;
            if (typeof(allOptions.center) != 'object') {
                allOptions.center = allOptions.center.split(',')
                allOptions.center = {
                    lat : allOptions.center[0]*1,
                    lng : allOptions.center[1]*1
                };
            }
        } else {
            allOptions = {
                center : {
                    lat : 42.345573,
                    lng : -71.098326
                },
                mapTypeId: "roadmap",
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                zoomControl: false,
                disableDefaultUI: true,
                disableDoubleClickZoom: true,
                zoom: 14,
            }
        }
        mapRecreate(item, allOptions, mark, image, description, flag);
        if (mainOptions) {
            mainOptions[4] = height;
            mainOptions = mainOptions.join(';');
            jQuery('#'+item).find('> .ba-options').val(mainOptions);
        } else {
            jQuery('#'+item).find('> .ba-options').val(JSON.stringify(options)+'; ; ; ;'+height+';');
        }
        saveDroppadItems();
    });
    
    jQuery('.map-zooming').on('click', function(){
        var item = jQuery('#item-id').val();
        var mainOptions = jQuery('#'+item).find('> .ba-options').val();
        mainOptions = mainOptions.split(';');
        if (jQuery(this).prop('checked')) {
            mainOptions[8] = 1;
        } else {
            mainOptions[8] = 0;
        }
        mainOptions = mainOptions.join(';');
        jQuery('#'+item).find('> .ba-options').val(mainOptions);
        saveDroppadItems();
    });
    
    jQuery('.map-draggable').on('click', function(){
        var item = jQuery('#item-id').val();
        var mainOptions = jQuery('#'+item).find('> .ba-options').val();
        mainOptions = mainOptions.split(';');
        if (jQuery(this).prop('checked')) {
            mainOptions[9] = 1;
        } else {
            mainOptions[9] = 0;
        }
        mainOptions = mainOptions.join(';');
        jQuery('#'+item).find('> .ba-options').val(mainOptions);
        saveDroppadItems();
    });
    
    //add event to show infobox on load or after click on marker
    jQuery('#map-options').find('[name="infobox"]').on('click', function(){
        var item = jQuery('#item-id').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val(),
            image;
        mainOptions = mainOptions.split(';');
        if (mainOptions[7] != '') {
            image = '../'+mainOptions[7];
        } else {
            image = mainOptions[7];
        }
        if (mainOptions[5] == 1) {
            mainOptions[5] = 0;
        } else {
            mainOptions[5] = 1;
        }
        mainOptions = mainOptions.join(';');
        jQuery('#'+item).find('> .ba-options').val(mainOptions);
        var allOption = jQuery('#'+item).find('> .ba-options').val(),
            allOption = allOption.split(';'),
            mark = allOption[1],
            allOptions = allOption[0],
            description = allOption[2],
            flag = allOption[5];
        if (allOptions) {
            allOptions = JSON.parse(allOptions);
            allOptions.zoom = allOptions.zoom*1;
            if (typeof(allOptions.center) != 'object') {
                allOptions.center = allOptions.center.split(',')
                allOptions.center = {
                    lat : allOptions.center[0]*1,
                    lng : allOptions.center[1]*1
                };
            }
        } else {
            allOptions = {
                center : {
                    lat : 42.345573,
                    lng : -71.098326
                },
                mapTypeId: "roadmap",
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                zoomControl: false,
                disableDefaultUI: true,
                disableDoubleClickZoom: true,
                zoom: 14,
            }
        }
        mapRecreate(item, allOptions, mark, image, description, flag);
        saveDroppadItems();
    });
    
    //add event to add/remove controls of the map
    jQuery('#map-options').find('[name="controls"]').click(function(){
        var item = jQuery('#item-id').val();
        var mainoptions = jQuery('#'+item).find('> .ba-options').val();
        mainoptions = mainoptions.split(';');
        if (mainoptions[6] == 1) {
            mainoptions[6] = 0;
            jQuery(this).removeAttr('checked');
        } else {
            mainoptions[6] = 1;
            jQuery(this).attr('checked', true);
        }
        mainoptions = mainoptions.join(';');
        jQuery('#'+item).find('> .ba-options').val(mainoptions);
        saveDroppadItems();
    });
    
    //make checkbox of the email always checked
    jQuery('#email-checkbox').find('[type="checkbox"]').on('click', function(){
        jQuery(this).attr('checked', true);
    });
    
    /*
        open options for button, hide options for other elements,
        filling default values
    */
    jQuery('#baform-0').click(function(){
        var align = jQuery(this).parent()[0].style.textAlign;
        align = jQuery.trim(align);
        jQuery('#button-otions .button-alignment option').each(function(){
            if (jQuery.trim(jQuery(this).val()) == align) {
                jQuery(this).attr('selected', true);
            }
        });
        jQuery("#myTab").tabs({ active: 0 });
        jQuery('#options > div').hide();
        jQuery('#button-otions, #options').show();
        jQuery('#button-otions').find('[name="name"]').on('keyup', function(){
            var name = jQuery('#button-otions').find('[name="name"]').val();
            jQuery('#baform-0').val(name);
            saveDroppadItems();
        });
        var name = jQuery('#baform-0').val(),
            style = jQuery('#baform-0').attr('style'),
            buttonStyle = style.split(';'),
            width = buttonStyle[0],
            height = buttonStyle[1],
            fontSize = buttonStyle[4],
            fontWeight = buttonStyle[5],
            bgColor = jQuery('#baform-0')[0].style.backgroundColor,
            color = jQuery('#baform-0')[0].style.color,
            borderRadius = jQuery.trim(buttonStyle[6]);
        jQuery('#button-otions').find('[name="name"]').val(name);
        width = jQuery.trim(width);
        height = jQuery.trim(height);
        fontWeight = jQuery.trim(fontWeight);
        fontWeight = fontWeight.substr(12);
        subColor = rgb2hex(bgColor);
        jQuery('#bg-color').minicolors('value', subColor[0]);
        jQuery('#bg-color').minicolors('opacity', subColor[1]);
        subColor = rgb2hex(color);
        jQuery('#text-color').minicolors('value', subColor[0]);
        jQuery('#text-color').minicolors('opacity', subColor[1]);
        jQuery('#width').val(width.substring(6, width.length-1));
        jQuery('#height').val(height.substring(7, height.length-2));
        fontSize = jQuery.trim(fontSize)
        jQuery('#font-size').val(fontSize.substring(10, fontSize.length-2));
        jQuery('[name="font-weight"]').each(function(i, o){
            jQuery(this).removeAttr('checked');
            if (o.value == fontWeight) {
                jQuery(this).attr('checked', true);
            }
        });
        
        jQuery('#radius').val(borderRadius.substring(14,borderRadius.length-2))
    });
    
    jQuery('.ba-total-price').on('click', function(){
        jQuery("#myTab").tabs({ active: 0 });
        jQuery('#options > div').hide();
        jQuery('.total-options, #options').show();
        jQuery('.total-label').val(totalLabel);
        jQuery('.total-alignment option').each(function(){
            if (jQuery(this).val() == totalAlign) {
                jQuery(this).attr('selected', true);
            } else {
                jQuery(this).removeAttr('selected');
            }
        });
    });

    jQuery('.total-label').on('keyup', function(){
        var value = jQuery(this).val();
        totalLabel = value;
        jQuery('.ba-total-price p')[0].firstChild.data = totalLabel+': ';
        saveFormToDataBase();
    });

    jQuery('.total-alignment').on('change', function(){
        totalAlign = jQuery(this).val();
        jQuery('.ba-total-price p').css('text-align', totalAlign);
        saveFormToDataBase();
    });

    jQuery('.submit-embed').on('click', function(){
        jQuery('#embed-modal').modal();
        jQuery('#embed-modal').css('z-index', '1500');
    });
    
    jQuery('#embed-aply').on('click', function(){
        jQuery('#embed-modal').modal('hide');
    });
    
    //add event to change min height of the textarea
    jQuery('#textarea-options').find('input').on('click keyup', function(){
        var min = jQuery(this).val();
        jQuery('.droppad_item').each(function(){
            var me = jQuery(this).find("[class*=ba]")[0];
            var type = jQuery.trim(me.className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
            if (type == 'textarea') {
                jQuery(this).find('textarea').css('min-height', min+'px');
                var option = jQuery(this).find('> .ba-options').val();
                option = option.split(';');
                option[4] = min;
                option = option.join(';');
                jQuery(this).find('> .ba-options').val(option);
            }
        });
        saveDroppadItems();
    });
    
    //add to item the title for discription
    jQuery('#text-description [name="description"]').on('keyup', function(){
        var title = jQuery(this).val();
        title = title.replace(new RegExp(";",'g'), '')
        var item = jQuery('#item-id').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val(),
            type = jQuery('#type-item').val();
        jQuery('#'+item).find('> .label-item').attr('title', title);
        if (type == 'upload') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[1] = title;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(';'+title);
            }    
        }
        if (type == 'textarea' || type == 'textInput' || type == 'chekInline' ||
            type == 'checkMultiple' || type == 'radioInline' || type == 'radioMultiple' ||
            type == 'dropdown' || type == 'selectMultiple' || type == 'address') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[1] = title;
                mainOptions = mainOptions.join(';')
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(';'+title);
            }
        }
        if (type == 'email') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[1] = title;
                mainOptions = mainOptions.join(';')
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(';'+title);
            }
        }
        if (type == 'slider') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[1] = title;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(';'+title);
            }
        }
        saveDroppadItems();
    });
    
    //applying new label to the item
    jQuery("#text-lable").find('[name="label"]').on('keyup', function(){
        var label = jQuery(this).val();
        label = label.replace(new RegExp(";",'g'), '')
        var item = jQuery('#item-id').val(),
            mainOptions = jQuery('#'+item).find('> .ba-options').val(),
            type = jQuery('#type-item').val();
        if (type == 'textarea' || type == 'textInput' || type == 'chekInline' ||
            type == 'checkMultiple' || type == 'radioInline' || type == 'radioMultiple' ||
            type == 'dropdown' || type == 'selectMultiple' || type == 'address') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[0] = label;
                var optionStr = mainOptions.join(';')
                jQuery('#'+item).find('> .ba-options').val(optionStr);
            } else {
                jQuery('#'+item).find('> .ba-options').val(label);
            }
            if (mainOptions[3] == 1) {
                if (label.length > 0) {
                    var newLabel = label+' *'
                } else {
                    var newLabel = "";
                }
                jQuery('#'+item).find('> .label-item').text(newLabel);
            } else {
                jQuery('#'+item).find('> .label-item').text(label);
            }
        }
        if (type == 'email') {
            if (label != '') {
                label += ' *'
            }
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[0] = label;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(label);
            }
            jQuery('#'+item).find('.label-item').text(label);
        }
        if (type == 'slider') {
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[0] = label;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val(label);
            }
            jQuery('#'+item).find('label').text(label);
        }
        if (type == 'date') {
            mainOptions = mainOptions.split(';');
            mainOptions[0] = label;
            if (mainOptions[1] == 1) {
                label += ' *';
            }
            jQuery('#'+item).find('.label-item').text(label);
            mainOptions = mainOptions.join(';');
            jQuery('#'+item).find('> .ba-options').val(mainOptions);
        }
        if (type == 'upload') {
            mainOptions = mainOptions.split(';');
            mainOptions[0] = label
            if (mainOptions[4] == 1) {
                if (label.length > 0) {
                    label += ' *'
                }
            }
            jQuery('#'+item).find('.label-item').text(label);
            mainOptions = mainOptions.join(';')
            jQuery('#'+item).find('> .ba-options').val(mainOptions);            
        }
        saveDroppadItems();
    });
    
    //applying new placeholder to the item
    jQuery('#place-hold').find('[name="place"]').on('keyup', function(){
        var placeHolder = jQuery(this).val();
        placeHolder = placeHolder.replace(new RegExp(";",'g'), '');
        var item = jQuery('#item-id').val(),
            type = jQuery('#type-item').val();
        if (type == 'textarea' || type == 'textInput' || type == 'address') {
            jQuery('#'+item).find('.ba-'+type).attr('placeholder', placeHolder);
            var mainOptions = jQuery('#'+item).find('> .ba-options').val();
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[2] = placeHolder;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val('; ;'+placeHolder);
            }
        }
        if (type == 'email') {
            jQuery('#'+item).find('[placeholder]').attr('placeholder', placeHolder);
            var mainOptions = jQuery('#'+item).find('> .ba-options').val();
            if (mainOptions != '') {
                mainOptions = mainOptions.split(';');
                mainOptions[2] = placeHolder;
                mainOptions = mainOptions.join(';');
                jQuery('#'+item).find('> .ba-options').val(mainOptions);
            } else {
                jQuery('#'+item).find('> .ba-options').val('; ;'+placeHolder);
            }
        }
        saveDroppadItems();
    });
    
    //delete item
    jQuery('#delete-item').click(function(){
        var item = jQuery('#item-id').val();
        deleteTarget = jQuery('#'+item);
        jQuery('#delete-dialog').modal();
        jQuery('#delete-dialog').css('z-index', '1500');
    });
    
    function sortDrop() 
    {
        jQuery(".droppad_area").sortable({
            cancel: null,
            items: '.droppad_item',
            cursorAt: {
                left: 90,
                top: 20
            },
            connectWith: ".droppad_area",
            tolerance: 'pointer',
            stop: function(event, ui){
                var item = jQuery(ui.item);
                if (item.closest('.droppad_area').hasClass('condition-area')) {
                    var type = item.find("[class*=ba]").not('[type=hidden]')[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1];
                    type = jQuery.trim(type)
                    if (type == 'radioMultiple' || type == 'radioInline' || type == 'chekInline' || type == 'checkMultiple') {
                        var options = item.find('> .ba-options').val();
                        options = options.split(';');
                        options[4] = '';
                        options = options.join(';');
                        item.find('input').removeClass('checked-radio').removeClass('checked-checkbox');
                        item.find('> .ba-options').val(options);
                    } else if (type == 'dropdown' || type == 'selectMultiple') {
                        item.find('select option').removeAttr('selected');
                        var options = item.find('> .ba-options').val();
                        options = options.split(';');
                        options[5] = '';
                        options = options.join(';');
                        item.find('> .ba-options').val(options);
                    }
                }
                saveDroppadItems()
            }
        }).disableSelection();
        jQuery(".condition-area").not('.selected').sortable( "option", "disabled", true );
    }
    
    //enable elements to be sortable
    jQuery('#content-section').sortable({
        handle: '.zmdi.zmdi-arrows',
        cursorAt: {
            left: 90,
            top: 20
        },
        tolerance: 'pointer',
        stop: function(event, ui) {
            saveFormColumns ()
        }
    }).disableSelection();
    
    jQuery('#cheсk-options .items-list').sortable({
        stop: function(event, ui) {
            checkTable();
        }
    });

    function checkLastFirst(item)
    {
        if (item[0] == item.parent().find('.items').first()[0]) {
            return 'first'
        } else if (item[0] == item.parent().find('.items').last()[0]) {
            return 'last';
        } else {
            return false;
        }
    }
    
    //function what save form columns and page breaker to data base
    function saveFormColumns ()
    {
        if (formId == '') {
            column = "";
            jQuery('#jform_form_columns').val('');
            jQuery('#content-section').find('.items').each(function(i, element){
                var classStr = jQuery(element).attr("class");
                classStr = classStr.substr(4, 2);
                column = jQuery('#jform_form_columns').val()+'|';
                if(column != '|') {
                    if (jQuery.trim(classStr) == 'k') {
                        var prev = jQuery(this).find('.ba-prev'),
                            prevOpt = prev.find('input[type="hidden"]').val(),
                            prevId = prev.find('.btn-prev').attr('id'),
                            next = jQuery(this).find('.ba-next'),
                            nextOpt = next.find('input[type="hidden"]').val(),
                            nextId = next.find('.btn-next').attr('id'),
                            opt = column+jQuery(element).attr("id")+',span'+classStr;
                        opt += ',' +prevId+ ',' +prevOpt+ ',' +nextId+ ',' +nextOpt;
                        jQuery('#jform_form_columns').val(opt);
                    } else {
                        var pos = checkLastFirst(jQuery(this)),
                            str = column+jQuery(element).attr("id")+',span'+classStr;
                        if (pos) {
                            str += ','+pos
                        }
                        jQuery('#jform_form_columns').val(str);
                    }
                } else {
                    if (jQuery.trim(classStr) == 'k') {
                        var prev = jQuery(this).find('.ba-prev'),
                            prevOpt = prev.find('input[type="hidden"]').val(),
                            prevId = prev.find('.btn-prev').attr('id'),
                            next = jQuery(this).find('.ba-next'),
                            nextOpt = next.find('input[type="hidden"]').val(),
                            nextId = next.find('.btn-next').attr('id'),
                            opt = column+jQuery(element).attr("id")+',span'+classStr;
                        opt += ',' +prevId+ ',' +prevOpt+ ',' +nextId+ ',' +nextOpt;
                        jQuery('#jform_form_columns').val(opt);
                    } else {
                        var pos = checkLastFirst(jQuery(this)),
                            str = jQuery(element).attr("id")+',span'+classStr;
                        if (pos) {
                            str += ','+pos
                        }
                        jQuery('#jform_form_columns').val(str);
                    }
                }
            });
        }
    }
    
    main();
    
    jQuery('#add-layout').on('click', function (){
        addLayout();
    });
    
    //open dialog window
    function addLayout ()
    {
        jQuery('#layout-dialog').modal('show');
    }
    
    //when entering a new value changes width of button 
    jQuery('#width').on('keyup click', function(){
        var style = jQuery('#baform-0').attr('style'),
            buttonStyle = style.split(';'),
            width = jQuery('#width').val();
        jQuery('#baform-0').attr('style', 'width:'+width+'%;'+buttonStyle[1]+';'+
                                 buttonStyle[2]+';'+buttonStyle[3]+';'+buttonStyle[4]+';'
                                 +buttonStyle[5]+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
        saveDroppadItems();
    });
    
    //add event 
    jQuery('#editor').click(function(){
        jQuery('#text-dialog').modal();
        jQuery('#text-dialog').css('z-index', '1500');
    });
    
    //when entering a new value changes height of button
    jQuery('#height').on('keyup click', function(){
        var style = jQuery('#baform-0').attr('style'),
            buttonStyle = style.split(';'),
            height = jQuery('#height').val();
        jQuery('#baform-0').attr('style', buttonStyle[0]+';height:'+height+'px;'+
                                 buttonStyle[2]+';'+buttonStyle[3]+';'+buttonStyle[4]+
                                 ';'+buttonStyle[5]+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
        saveDroppadItems();
    });
    
    //when keyup new font size will be aply changing for button
    jQuery('#font-size').on('keyup click', function(){
        var style = jQuery('#baform-0').attr('style'),
            size = jQuery('#font-size').val(),
            buttonStyle = style.split(';');
        jQuery('#baform-0').attr('style', buttonStyle[0]+';'+buttonStyle[1]+';'+
                                 buttonStyle[2]+';'+buttonStyle[3]+';font-size:'+size+'px;'
                                 +buttonStyle[5]+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
        saveDroppadItems();
    });
    
    //change font weight on click radio button
    jQuery('input[name="font-weight"]').click(function(){
        var weight = jQuery(this).val(),
            style = jQuery('#baform-0').attr('style'),
            buttonStyle = style.split(';');
        jQuery('#baform-0').attr('style', buttonStyle[0]+';'+buttonStyle[1]+';'+
                                 buttonStyle[2]+';'+buttonStyle[3]+';'+buttonStyle[4]+
                                 ';font-weight:'+weight+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
        saveDroppadItems();
    });

    jQuery('#text-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            var style = jQuery('#baform-0').attr('style');
            var buttonStyle = style.split(';');
            jQuery('#baform-0').attr('style', buttonStyle[0]+';'+buttonStyle[1]+';'+
                                     buttonStyle[2]+';color:'+hex+';'+buttonStyle[4]+';'
                                     +buttonStyle[5]+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
            saveDroppadItems();
        }
    });

    jQuery('#bg-color').minicolors({
        opacity : true,
        change : function(hex){
            hex = jQuery(this).minicolors('rgbaString');
            var style = jQuery('#baform-0').attr('style');
            var buttonStyle = style.split(';');
            jQuery('#baform-0').attr('style', buttonStyle[0]+';'+buttonStyle[1]+';background-color:'+
                                     hex+';'+buttonStyle[3]+';'+buttonStyle[4]+';'+
                                     buttonStyle[5]+';'+buttonStyle[6]+';'+buttonStyle[7]+';');
            saveDroppadItems();
        }
    });
    
    //removes conflict with integrated slider
    jQuery('.ba-slider').prop("slide",null);
    //initializes slider
    jQuery('.ba-slider').slider({
        values: [0, 50],
        min: 0,
        max: 50,
        step: 1,
        range: "min"
    });
    
    //add event to change border radius of submit button
    jQuery('#radius').on('keyup click', function(){
        var radius = jQuery("#radius").val();
            var style = jQuery('#baform-0').attr('style');
            var buttonStyle = style.split(';');
            jQuery('#baform-0').attr('style', buttonStyle[0]+'; '+buttonStyle[1]+';'+
                                     buttonStyle[2]+';'+buttonStyle[3]+';'+buttonStyle[4]+';'+
                                     buttonStyle[5]+';border-radius:'+radius+'px;'+buttonStyle[7]+';');
            saveDroppadItems();
    });
    
    //variable what store slider options
    var sliderOptions = {};
    
    //saving options for slider
    jQuery('#min, #max, #step').on('keyup', function(){
        var min = jQuery('#min').val(),
            max = jQuery('#max').val();
        if (jQuery('#min').val() < 0 ) {
            jQuery('#min').val(0);
        }
        if (jQuery('#max').val() < 0 ) {
            jQuery('#max').val(0);
        }
        if (jQuery('#step').val() < 1 ) {
            jQuery('#step').val(1);
        }
        if (max*1 < min*1) {
            jQuery('#min').addClass('ba-alert');
            jQuery('#max').addClass('ba-alert');
        } else {
            jQuery('#min').removeClass('ba-alert');
            jQuery('#max').removeClass('ba-alert');
        }
        sliderOptions.minimum = jQuery('#min').val();
        sliderOptions.maximum = jQuery('#max').val();
        sliderOptions.step = jQuery('#step').val();
        var item = jQuery('#item-id').val();
        var mainOptions = jQuery('#'+item).find('> .ba-options').val();
        jQuery('#'+item).find('input').val(sliderOptions);
        if (!sliderOptions.minimum) {
            sliderOptions.minimum = 0;
        }
        if (!sliderOptions.maximum) {
            sliderOptions.maximum = 50;
        }
        if (!sliderOptions.step) {
            sliderOptions.step = 1;
        }
        var label = jQuery('#text-lable').find('[name="label"]').val();
        jQuery('#'+item).find('label').text(label);
        if (mainOptions != '') {
            mainOptions = mainOptions.split(';');
            jQuery('#'+item).find('> .ba-options').val(mainOptions[0]+';'+mainOptions[1]+';'+sliderOptions.minimum+';'+
                                                     sliderOptions.maximum+';'+sliderOptions.step);
        } else {
            jQuery('#'+item).find('> .ba-options').val('; ;'+sliderOptions.minimum+';'+
                                                     sliderOptions.maximum+';'+sliderOptions.step);
        }
        saveDroppadItems();
    });
    
    //open modal for deleting column
    function canDelete ()
    {
        jQuery('.delete-layout').click(function(event){
            event.preventDefault();
            deleteTarget = jQuery(this).closest('.droppad_area');
            if (deleteTarget.length > 0) {
                deleteTarget = deleteTarget.parent();
            } else {
                deleteTarget = jQuery(this).closest('.break');
            }
            jQuery('#delete-dialog').modal();
            jQuery('#delete-dialog').css('z-index', '1500');
        });
    }
    
    canDelete ();
    //delete item after click on button aply
    jQuery('#delete-aply').click(function(){
        if (!deleteChecked) {
            deleteTarget.remove();
            saveFormColumns();
            saveDroppadItems();
        } else {
            var items = new Array();
            jQuery('#cheсk-options .items-list tr').each(function(ind, el){
                if (jQuery(this).find('.list-item').prop('checked')) {
                    items.push(jQuery(this));
                    jQuery('#'+jQuery('#item-id').val()).find('> [data-condition="'+ind+'"]').remove();
                }
            });
            for (var i = 0; i < items.length; i++) {
                items[i].remove();
            }
            checkTable();
        }
        jQuery('#delete-dialog').modal('hide');
        jQuery('#options > div').hide()
    });
    
    jQuery('#delete-dialog').on('hide', function(){
        deleteChecked = false;
    });
    
    var column = '';
    
    jQuery('.add-column').on('click', function(){
        var num = jQuery(this).attr('data-column');
        addColumn(num);
    });

    jQuery('#fields-editor').on('show', function(){
        jQuery('#fields-editor .forms-list tbody').empty();
        jQuery('#content-section .droppad_item').each(function(){
            var id = jQuery(this).find('> .ba-options').attr('data-id'),
                name = jQuery(this).find('> .ba-options').val(),
                str = '<tr><th class="form-title"><a href="#">',
                type = jQuery.trim(jQuery(this).find("[class*=ba]").not('[type=hidden]')[0].className.match("ba-.*")[0].split(" ")[0].split("-")[1]);
            name = name.split(';');
            if (!id) {
                return;
            }
            if (type == 'email' || type == 'textarea' || type == 'textInput' || type == 'address') {
                if (!name[0]){
                    if (!name[2]) {
                        name = type;
                    } else {
                        name = name[2];
                    }
                } else {
                    name = name[0];
                }
            } else if (type == 'radioInline' || type == 'chekInline' || type == 'checkMultiple' || type == 'radioMultiple'
                       || type == 'dropdown' || type == 'selectMultiple' || type == 'slider' || type == 'date') {
                if (!name[0]){
                    name = type;
                } else {
                    name = name[0];
                }
            } else {
                return;
            }
            str += name+'</a></th><td>'+id+'</td></tr>';
            jQuery('#fields-editor .forms-list tbody').append(str);
        });
    });    
    
    //add new column
    function addColumn (number)
    {
        //count of column
        var n = 12/number,
            str = '<div class="row-fluid">';
        for (var i = 0; i < number; i++) {
            if (number == 1) {
                str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n;
                str += ' droppad_area items"><div class="ba-edit-row"><a class="';
                str += 'zmdi zmdi-arrows"></a><a href="#" class="delete-layout ';
                str += 'zmdi zmdi-close"></a></div></div>';
            }
            if (number == 2) {
                if (i == 1) {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n;
                    str += ' droppad_area items"><span class="ba-left-resize"></span>';
                    str += '<div class="ba-edit-row"><a class="';
                    str += 'zmdi zmdi-arrows"></a><a href="#" class="delete-layout ';
                    str += 'zmdi zmdi-close"></a></div></div>';
                } else {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n+' droppad_area items">';
                    str +='<span class="ba-right-resize"></span></div>';
                }
            }
            if (number == 3) {
                if (i == 2) {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n;
                    str += ' droppad_area items"><span class="ba-left-resize"></span>';
                    str += '<div class="ba-edit-row"><a class="';
                    str += 'zmdi zmdi-arrows"></a><a href="#" class="delete-layout ';
                    str += 'zmdi zmdi-close"></a></div></div>';
                } else {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n+' droppad_area items">';
                    if (i == 1) {
                        str += '<span class="ba-left-resize"></span>';
                    }
                    str += '<span class="ba-right-resize"></span></div>';
                }
            }
            if (number == 4) {
                if (i == 3) {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n;
                    str += ' droppad_area items"><div class="ba-edit-row"><a class="';
                    str += 'zmdi zmdi-arrows"></a><a href="#" class="delete-layout ';
                    str += 'zmdi zmdi-close"></a></div></div>';
                } else {
                    str += '<div id="bacolumn-'+(columnNumber++)+'" class="span'+n+' droppad_area items"></div>';
                }
            }
        }
        str += '</div>';
        jQuery("#content-section").append(str);
        jQuery('#layout-dialog').modal('hide');
        canDelete();
        main();
        makeBaResize();
    }

    var startX,
        rightParent,
        rightWidth,
        leftResize,
        leftWidth,
        deltaX,
        rowWidth,
        minResize,
        maxResize,
        leftClass,
        rightClass,
        maxSpan;

    makeBaResize();

    function makeBaResize()
    {
        jQuery('.ba-right-resize, ba-left-resize').off();
        jQuery(document).off('mousemove.resize mouseup.resize').off
        jQuery('.ba-right-resize').on('mousedown', function(event){
            rightParent = jQuery(this).closest('.droppad_area');
            leftResize = rightParent.next();
            setNewSize('right', event);
        });
        jQuery('.ba-left-resize').on('mousedown', function(event){
            leftResize = jQuery(this).closest('.droppad_area');
            rightParent = leftResize.prev();
            setNewSize('left', event);
        });
    }

    function setNewSize(item, event)
    {
        rightClass = rightParent.attr("class");
        rightClass = rightClass.substr(4, 2);
        leftClass = leftResize.attr("class");
        leftClass = leftClass.substr(4, 2);
        maxSpan = rightClass * 1 + leftClass * 1 - 3;
        rowWidth = rightParent.parent().outerWidth(true);
        startX = event.pageX;
        rightWidth = rightParent.width() - 2;
        leftWidth = leftResize.width() - 2;
        minResize = Math.floor(rowWidth * 23 / 100 - 42);
        maxResize = returnSpanWidth(maxSpan);
        jQuery(document).on('mousemove.resize', function(event){
            if (startX > event.pageX) {
                deltaX = startX - event.pageX;
                if (item = 'right') {
                    rightWidth = rightWidth - deltaX;
                    leftWidth = leftWidth + deltaX;
                } else {
                    leftWidth = rightWidth - deltaX;
                    rightWidth = leftWidth + deltaX;
                }
            } else {
                deltaX = event.pageX - startX;
                if (item = 'right') {
                    rightWidth = rightWidth + deltaX;
                    leftWidth = leftWidth - deltaX;
                } else {
                    leftWidth = rightWidth + deltaX;
                    rightWidth = leftWidth - deltaX;
                }
                
            }
            if (rightWidth < minResize || leftWidth > maxResize) {
                rightWidth = minResize;
                leftWidth = maxResize;
            }
            if (leftWidth < minResize || rightWidth > maxResize) {
                rightWidth = maxResize;
                leftWidth = minResize;
                
            }
            rightParent.width(rightWidth);
            leftResize.width(leftWidth);
            startX = event.pageX;
        });
        jQuery(document).on('mouseup.resize', function(event){
            jQuery(document).off('mouseup.resize');
            jQuery(document).off('mousemove.resize');
            var percent = rightParent.outerWidth() * 100 / rowWidth,
                leftPercent = leftResize.outerWidth() * 100 / rowWidth,
                span;
            rightParent[0].style.width = '';
            leftResize[0].style.width = '';
            percent = Math.round(percent * 100) / 100;
            leftPercent = Math.round(leftPercent * 100) / 100;
            span = getSpanWidth(percent);
            rightParent.removeClass('span'+rightClass);
            rightParent[0].className = 'span'+span+' '+rightParent[0].className;
            span = maxSpan - span + 3;
            leftResize.removeClass('span'+leftClass);
            leftResize[0].className = 'span'+span+' '+leftResize[0].className;
            saveFormColumns();
        });
    }

    function returnSpanWidth(i)
    {
        i = jQuery.trim(i);
        switch(i) {
            case '3' : return Math.floor(rowWidth * 23 / 100 - 42);
                break;
            case '4' : return Math.floor(rowWidth * 31 / 100 - 42);
                break;
            case '5' : return Math.floor(rowWidth * 40 / 100 - 42);
                break;
            case '6' : return Math.floor(rowWidth * 48 / 100 - 42);
                break;
            case '7' : return Math.floor(rowWidth * 57 / 100 - 42);
                break;
            case '8' : return Math.floor(rowWidth * 65 / 100 - 42);
                break;
            case '9' : return Math.floor(rowWidth * 74 / 100 - 42);
                break;
            }
    }

    function getSpanWidth(i)
    {
        if(i < 23.07) {
            return '3';
        } else if (i >= 23.07 && i <= 31.62) {
            if (i - 23.07 < 31.62 - i) {
                return '3';
            } else {
                return '4';
            }
        } else if (i >= 31.62 && i <= 40.17) {
            if (i - 31.62 < 40.17 - i) {
                return '4';
            } else {
                return '5';
            }
        } else if (i >= 40.17 && i <= 48.71) {
            if (i - 40.17 < 48.71 - i) {
                return '5';
            } else {
                return '6';
            }
        } else if (i >= 48.71 && i <= 57.26) {
            if (i - 48.71 < 57.26 - i) {
                return '6';
            } else {
                return '7';
            }
        } else if (i >= 57.27 && i <= 65.81) {
            if (i - 57.26 < 65.81 - i) {
                return '7';
            } else {
                return '8';
            }
        } else if (i >= 65.81 && i <= 74.35) {
            if (i - 65.81 < 74.35 - i) {
                return '8';
            } else {
                return '9';
            }
        } else if (i > 74.35) {
            return '9';
        }
    }
});