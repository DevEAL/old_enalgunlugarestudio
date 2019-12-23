DROP TABLE IF EXISTS `#__baforms_forms`;
CREATE TABLE `#__baforms_forms` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(40) NOT NULL,
    `title_settings` text NOT NULL,
    `form_settings` text NOT NULL,
    `ordering` int(11) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `alow_captcha` varchar(40) NOT NULL,
    `display_title` tinyint(1) NOT NULL DEFAULT 1,
    `sent_massage` text NOT NULL,
    `error_massage` text NOT NULL,
    `redirect_url` varchar(255) NOT NULL,
    `email_recipient` varchar(255) NOT NULL,
    `email_subject` varchar(255) NOT NULL,
    `email_body` text NOT NULL,
    `sender_name` varchar(255) NOT NULL,
    `sender_email` varchar(255) NOT NULL,
    `reply_subject` varchar(255) NOT NULL,
    `reply_body` text NOT NULL,
    `display_popup` tinyint(1) NOT NULL DEFAULT 0,
    `button_lable` varchar(255) NOT NULL,
    `button_position` varchar(40) NOT NULL,
    `button_bg` varchar(40) NOT NULL,
    `button_color` varchar(40) NOT NULL,
    `button_font_size` int(5) NOT NULL DEFAULT 14,
    `button_border` int(5) NOT NULL DEFAULT 3,
    `button_weight` varchar(10) NOT NULL DEFAULT 'normal',
    `display_submit` tinyint(1) NOT NULL DEFAULT 1,
    `submit_embed` text NOT NULL,
    `message_bg_rgba` varchar(255) NOT NULL,
    `message_color_rgba` varchar(255) NOT NULL,
    `dialog_color_rgba` varchar(255) NOT NULL,
    `add_sender_email` varchar(255) NOT NULL DEFAULT 0,
    `copy_submitted_data` tinyint(1) NOT NULL DEFAULT 0,
    `modal_width` varchar(255) NOT NULL DEFAULT '500',
    `display_total` tinyint(1) NOT NULL DEFAULT 0,
    `currency_code` varchar(255) NOT NULL,
    `currency_symbol` varchar(255) NOT NULL,
    `payment_methods` varchar(255) NOT NULL,
    `return_url` varchar(255) NOT NULL,
    `cancel_url` varchar(255) NOT NULL,
    `paypal_email` varchar(255) NOT NULL,
    `payment_environment` varchar(255) NOT NULL,
    `seller_id` varchar(255) NOT NULL,
    `skrill_email` varchar(255) NOT NULL,
    `webmoney_purse` varchar(255) NOT NULL,
    `check_ip` tinyint(1) NOT NULL DEFAULT 0,
    `payu_api_key` varchar(255) NOT NULL,
    `payu_merchant_id` varchar(255) NOT NULL,
    `payu_account_id` varchar(255) NOT NULL,
    `button_type` varchar(255) NOT NULL,
    `email_letter` mediumtext NOT NULL,
    `display_cart` tinyint(1) NOT NULL DEFAULT 0,
    `currency_position` varchar(255) NOT NULL DEFAULT 'before',
    `multiple_payment` tinyint(1) NOT NULL DEFAULT 0,
    `custom_payment` varchar(255) NOT NULL DEFAULT 'Custom Payment',
    `mailchimp_api_key` varchar(255) NOT NULL,
    `mailchimp_list_id` varchar(255) NOT NULL,
    `mailchimp_fields_map` text NOT NULL,
    `stripe_api_key` varchar(255) NOT NULL,
    `stripe_image` varchar(255) NOT NULL,
    `stripe_name` varchar(255) NOT NULL,
    `stripe_description` varchar(255) NOT NULL,
    `theme_color` varchar(255) NOT NULL DEFAULT '#009ddc',
    `email_options` text NOT NULL,
    `mollie_api_key` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__baforms_columns`;
CREATE TABLE `#__baforms_columns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `settings` text NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__baforms_items`;
CREATE TABLE `#__baforms_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `column_id` int(11) NOT NULL,
    `settings` text NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__baforms_submissions`;
CREATE TABLE `#__baforms_submissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(40) NOT NULL,
    `mesage` text NOT NULL,
    `date_time` varchar(40) NOT NULL,
    `submission_state` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__baforms_reference`;
CREATE TABLE `#__baforms_reference` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `value` varchar(40) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__baforms_api`;
CREATE TABLE `#__baforms_api` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service` varchar(255) NOT NULL,
    `key` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__baforms_api` (`service`, `key`) VALUES
('google_maps', '');

INSERT INTO `#__baforms_forms` (`id`, `title`, `title_settings`, `form_settings`, `ordering`, `published`, `alow_captcha`, `display_title`, `sent_massage`, `error_massage`, `redirect_url`, `email_recipient`, `email_subject`, `email_body`, `sender_name`, `sender_email`, `reply_subject`, `reply_body`, `display_popup`, `button_lable`, `button_position`, `button_bg`, `button_color`) VALUES
(1, 'Job Application Form', 'font-size:26px; font-weight:normal; text-align:left; color:#111111', '/16px/#333333/35px/13px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 94%;background-color: #fafafa;border: 1px solid #f3f3f3;border-radius: 2px/normal', 0, 1, '0', 0, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 0, '', '', '', ''),
(2, 'Hotel Booking Form', 'font-size:21px;font-weight:bold;text-align:left; color:#111111;', '/14px/#333333/40px/13px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 94%;background-color: #fafafa;border: 1px solid #f3f3f3;border-radius: 6px/normal', 0, 1, '0', 1, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 0, '', '', '', ''),
(3, 'Bug Report Form', 'font-size:26px;font-weight:bold; text-align:left;color:#111111;', '/14px/#333333/30px/14px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 94%;background-color: #fafafa;border: 1px solid #e6e6e6;border-radius: 2px/bold', 0, 1, '0', 1, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 0, '', '', '', ''),
(4, 'Feedback Form', 'font-size:22px;font-weight:bold;text-align:center; color:#111111;', '/13px/#333333/30px/13px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 30%;background-color: #fafafa;border: 1px solid #e8e8e8;border-radius: 10px/normal', 0, 1, '0', 1, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 1, 'Submit Feedback', 'bottom', '#eba102', '#f2f2f2'),
(5, 'Pre-sales Questions', 'font-size:22px;font-weight:bold;text-align:center; color:#111111;', '/13px/#333333/30px/13px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 30%;background-color: #fafafa;border: 1px solid #fafafa;border-radius: 10px/bold', 0, 1, '0', 0, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 1, 'Pre-sales Questions', 'right', '#02ebcc', '#fafafa'),
(6, 'Contact US', 'font-size:26px;font-weight:bold;text-align:center; color:#111111;', '/16px/#333333/35px/13px/#999999/#ffffff/border: 1px solid #f3f3f3/2px/width: 94%;background-color: #fafafa;border: 1px solid #f3f3f3;border-radius: 2px/normal', 0, 1, '0', 0, 'Your message was sent successfully', 'Your message could not be sent', '', 'email-1@email.com, email-2@email.com...', 'You have a new submission', 'Hello, You have a new submission from:', 'John Doe', 'email@email.com', 'Thank you for your submission!', 'Thank you for filling form! Your information has been successfully submitted. We will process its contents and get back to you as soon as possible.', 0, '', '', '', '');

INSERT INTO `#__baforms_columns` (`form_id`, `settings`) VALUES
(3, 'bacolumn-3,span6 '),
(3, 'bacolumn-4,span6 '),
(3, 'bacolumn-1,span12'),
(6, 'bacolumn-6,span12'),
(6, 'bacolumn-2,span6 '),
(6, 'bacolumn-3,span6 '),
(6, 'bacolumn-4,span12'),
(4, 'bacolumn-1,span12'),
(2, 'bacolumn-6,span6 '),
(2, 'bacolumn-7,span6 '),
(2, 'bacolumn-8,span4 '),
(2, 'bacolumn-9,span4 '),
(2, 'bacolumn-10,span4 '),
(2, 'bacolumn-4,span12'),
(2, 'bacolumn-12,span6 '),
(2, 'bacolumn-13,span6 '),
(2, 'bacolumn-14,span12'),
(1, 'bacolumn-6,span12'),
(1, 'bacolumn-2,span6 '),
(1, 'bacolumn-3,span6 '),
(1, 'bacolumn-4,span12'),
(5, 'bacolumn-1,span12');

INSERT INTO `#__baforms_items` (`form_id`, `column_id`, `settings`) VALUES
(3, 2, 'button_-_Submit_-_width:40%;  height:45px;  background-color:#02eb8e;  color: #fafafa;  font-size:14px;  font-weight:bold; border-radius:30px;border: none;_-_text-align: left; z-index: 134;'),
(3, 2, 'bacolumn-3_-_baform-1_-_textInput_-_Full Name;;;1'),
(3, 2, 'bacolumn-4_-_baform-3_-_email_-_Email *;;;0'),
(3, 2, 'bacolumn-1_-_baform-4_-_dropdown_-_Select a Product;;"iPhone\\niPad\\niPod";0'),
(3, 2, 'bacolumn-1_-_baform-9_-_radioMultiple_-_Classification;;"Security: Potential security exposures\\nCrash: Bugs which cause a machine to crash\\nPower: Issues pertaining to power consumption and battery life\\nPerformance: Issues that reduce the performance or responsiveness of an application\\nUI/Usability: A cosmetic issue or an issue with the usability of an application";0'),
(3, 2, 'bacolumn-1_-_baform-7_-_textarea_-_Steps to Reproduce;;;0;120'),
(3, 2, 'bacolumn-1_-_baform-8_-_radioInline_-_Reproducibility;;"Always\\nSometimes\\nRarely\\nUnable\\nI didn'),
(3, 2, 'bacolumn-1_-_baform-10_-_upload_-_Upload Screen;;5;jpg, jpeg, png, pdf, doc'),
(6, 2, 'button_-_Submit_-_width:30%;height:50px;background-color:#02eb6b;color: #fafafa;font-size:14px;font-weight:bold;border-radius:3px;border: none;_-_text-align: left; z-index: 157;'),
(6, 2, 'bacolumn-6_-_baform-10_-_map_-_{"scrollwheel":false,"navigationControl":false,"mapTypeControl":false,"scaleControl":false,"draggable":false,"zoomControl":false,"disableDefaultUI":true,"disableDoubleClickZoom":true,"zoom":14,"mapTypeId":"roadmap","center":"40.72951339999999,-73.99646089999999"};{"k":40.728856405183194,"D":-73.99579256772995};New York University, New York, NY, USA;100;250;1;1;'),
(6, 2, 'bacolumn-2_-_baform-1_-_textInput_-_First Name;Please enter your First Name;Please enter your First Name;1'),
(6, 2, 'bacolumn-2_-_baform-3_-_textInput_-_Phone;;+1 892 925 7801;1'),
(6, 2, 'bacolumn-3_-_baform-2_-_textInput_-_Last Name;Please enter your last name;Please enter your last name;1'),
(6, 2, 'bacolumn-3_-_baform-4_-_email_-_Email *;;@'),
(6, 2, 'bacolumn-4_-_baform-11_-_textarea_-_Your comments;;;0;120'),
(4, 2, 'button_-_Submit Feedback_-_width:100%;height:50px;background-color:#eba102;color: #fafafa;font-size:16px;font-weight:bold;border-radius:3px;border: none;_-_text-align: center; z-index: 129;'),
(4, 2, 'bacolumn-1_-_baform-1_-_dropdown_-_Choose a Product;;"Mac mini\\nMacBook\\nMacBook Air\\nMacBook Pro\\niMac\\nMac Pro";1'),
(4, 2, 'bacolumn-1_-_baform-6_-_slider_-_Slider;;0;50;1'),
(4, 2, 'bacolumn-1_-_baform-3_-_radioInline_-_Rate It;;"Excellent\\nGood\\nBad";1'),
(4, 2, 'bacolumn-1_-_baform-5_-_textarea_-_Your comments are welcome;;;0;120'),
(2, 2, 'button_-_Book Now!_-_width:40%;   height:50px;   background-color:#00ff6a;   color: #fafafa;   font-size:14px;   font-weight:bold;   border-radius:6px;border: none;_-_text-align: right; z-index: 153;'),
(2, 2, 'bacolumn-6_-_baform-1_-_date_-_Check-in Date'),
(2, 2, 'bacolumn-7_-_baform-2_-_date_-_Check-out Date'),
(2, 2, 'bacolumn-8_-_baform-4_-_dropdown_-_Number of guests;;"1\\n2\\n3\\n4\\n5";0'),
(2, 2, 'bacolumn-9_-_baform-5_-_dropdown_-_Beds;;"1\\n2\\n3\\n4\\n5\\n6";0'),
(2, 2, 'bacolumn-10_-_baform-6_-_dropdown_-_Baths;;"1\\n2\\n3\\n4\\n5\\n6";0'),
(2, 2, 'bacolumn-4_-_baform-3_-_slider_-_Select Price Range, per night;;500;1500;10'),
(2, 2, 'bacolumn-12_-_baform-7_-_textInput_-_First Name;;Please enter your First Name;1'),
(2, 2, 'bacolumn-12_-_baform-9_-_email_-_Email *;;@'),
(2, 2, 'bacolumn-13_-_baform-8_-_textInput_-_Last Name;;Please enter your Last Name;1'),
(2, 2, 'bacolumn-13_-_baform-11_-_textInput_-_Address;;Please enter your Address;1'),
(2, 2, 'bacolumn-14_-_baform-12_-_dropdown_-_Country;;"Netherlands\\nBelgium\\nFrance\\nSpain\\nGibraltar\\nPortugal\\nLuxembourg\\nIreland\\nIceland\\nAlbania\\nMalta\\nCyprus\\nFinland";0'),
(2, 2, 'bacolumn-14_-_baform-13_-_textarea_-_Special Request;;;0;120'),
(1, 2, 'button_-_Submit_-_width:70%;  height:50px; background-color: #02adea; color: #fafafa; font-size:14px; font-weight:bold; border-radius:3px;border: none;_-_text-align: center; z-index: 146;'),
(1, 2, 'bacolumn-6_-_baform-8_-_htmltext_-_<i>Thank you for your interest in working with us. Send your application by filling out the Job Application Form.</i>'),
(1, 2, 'bacolumn-2_-_baform-1_-_textInput_-_First Name;Please enter your First Name;Please enter your First Name;1'),
(1, 2, 'bacolumn-2_-_baform-3_-_textInput_-_Phone;;+1 892 925 7801;1'),
(1, 2, 'bacolumn-3_-_baform-2_-_textInput_-_Last Name;Please enter your last name;Please enter your last name;1'),
(1, 2, 'bacolumn-3_-_baform-4_-_email_-_Email *;;@'),
(1, 2, 'bacolumn-4_-_baform-5_-_textInput_-_Address;;New York. 350 Fifth Avenue, 34th floor.;1'),
(1, 2, 'bacolumn-4_-_baform-9_-_dropdown_-_What position are you applying for?;;"Systems analyst\\nProgrammer\\nDBA\\nProject lead\\nSystem admin\\nIT manager\\nNetwork admin\\nReporting specialist\\nTechnician\\nHelp desk analyst";1'),
(1, 2, 'bacolumn-4_-_baform-6_-_slider_-_Salary in $;;10000;50000;5000'),
(1, 2, 'bacolumn-4_-_baform-7_-_upload_-_Upload CV;;5;pdf, doc'),
(5, 2, 'button_-_Submit Question_-_width:100%;height:50px;background-color:#02ebcc;color: #fafafa;font-size:14px;font-weight:bold;border-radius:3px;border: none;_-_text-align: center; z-index: 124;'),
(5, 2, 'bacolumn-1_-_baform-7_-_textInput_-_Name;;;1'),
(5, 2, 'bacolumn-1_-_baform-6_-_email_-_Email *;;;0'),
(5, 2, 'bacolumn-1_-_baform-5_-_textarea_-_Message;;;0;120');