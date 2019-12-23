<?php

	$operations = new RevOperations();

	//set Layer settings
	$contentCSS = $operations->getCaptionsContent();
	$arrAnimations = $operations->getArrAnimations();
	$arrEndAnimations = $operations->getArrEndAnimations();

	$htmlButtonDown = '<div id="layer_captions_down" style="width:30px; text-align:center;padding:0px;" class="revgray button-primary"><i class="eg-icon-down-dir"></i></div>';
	$buttonEditStyles = UniteFunctionsRev::getHtmlLink("javascript:void(0)", "<i class=\"revicon-magic\"></i>Edit Style","button_edit_css","button-primary revblue");
	$buttonEditStylesGlobal = UniteFunctionsRev::getHtmlLink("javascript:void(0)", "<i class=\"revicon-palette\"></i>Edit Global Style","button_edit_css_global","button-primary revblue");

	$arrSplit = $operations->getArrSplit();
	$arrEasing = $operations->getArrEasing();
	$arrEndEasing = $operations->getArrEndEasing();

	$captionsAddonHtml = $htmlButtonDown.$buttonEditStyles.$buttonEditStylesGlobal;

	//set Layer settings
	$layerSettings = new UniteSettingsAdvancedRev();
	$layerSettings->addSection(__ug("Layer Params",REVSLIDER_TEXTDOMAIN),__ug("layer_params",REVSLIDER_TEXTDOMAIN));
	$layerSettings->addSap(__ug("Layer Params",REVSLIDER_TEXTDOMAIN),__ug("layer_params", REVSLIDER_TEXTDOMAIN));
	$layerSettings->addTextBox("layer_caption", __ug("caption_green",REVSLIDER_TEXTDOMAIN), __ug("Style",REVSLIDER_TEXTDOMAIN),array(UniteSettingsRev::PARAM_ADDTEXT=>$captionsAddonHtml,"class"=>"textbox-caption"));

	$addHtmlTextarea = '';
	if($sliderTemplate == "true"){
		$addHtmlTextarea .= UniteFunctionsRev::getHtmlLink("javascript:void(0)", "Insert Meta","linkInsertTemplate","disabled revblue button-primary");
	}
	$addHtmlTextarea .= UniteFunctionsRev::getHtmlLink("javascript:void(0)", "Insert Button","linkInsertButton","disabled revblue button-primary");

	$layerSettings->addTextArea("layer_text", "",__ug("Text / Html",REVSLIDER_TEXTDOMAIN),array("class"=>"area-layer-params",UniteSettingsRev::PARAM_ADDTEXT_BEFORE_ELEMENT=>$addHtmlTextarea));
	$layerSettings->addTextBox("layer_image_link", "",__ug("Image Link",REVSLIDER_TEXTDOMAIN),array("class"=>"text-sidebar-link","hidden"=>true));
	$layerSettings->addSelect("layer_link_open_in",array("same"=>__ug("Same Window",REVSLIDER_TEXTDOMAIN),"new"=>__ug("New Window",REVSLIDER_TEXTDOMAIN)),__ug("Link Open In",REVSLIDER_TEXTDOMAIN),"same",array("hidden"=>true));

	$layerSettings->addSelect("layer_animation",$arrAnimations,__ug("Start Animation",REVSLIDER_TEXTDOMAIN),"fade");
	$layerSettings->addSelect("layer_easing", $arrEasing, __ug("Start Easing",REVSLIDER_TEXTDOMAIN),"Power3.easeInOut");
	$params = array("unit"=>__ug("ms",REVSLIDER_TEXTDOMAIN));
	$paramssplit = array("unit"=>__ug(" ms (keep it low i.e. 1- 200)",REVSLIDER_TEXTDOMAIN));
	$layerSettings->addTextBox("layer_speed", "","Start Duration",$params);
	$layerSettings->addTextBox("layer_splitdelay", "10","Split Delay",$paramssplit);
	$layerSettings->addSelect("layer_split", $arrSplit, __ug("Split Text per",REVSLIDER_TEXTDOMAIN),"none");
	$layerSettings->addCheckbox("layer_hidden", false,__ug("Hide Under Width",REVSLIDER_TEXTDOMAIN));

	$params = array("hidden"=>true);
	$layerSettings->addTextBox("layer_link_id", "",__ug("Link ID",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_link_class", "",__ug("Link Classes",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_link_title", "",__ug("Link Title",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_link_rel", "",__ug("Link Rel",REVSLIDER_TEXTDOMAIN),$params);

	//scale for img
	$textScaleX = __ug("Width",REVSLIDER_TEXTDOMAIN);
	$textScaleProportionalX = __ug("Width/Height",REVSLIDER_TEXTDOMAIN);
	$params = array("attrib_text"=>"data-textproportional='".$textScaleProportionalX."' data-textnormal='".$textScaleX."'", "hidden"=>false);
	$layerSettings->addTextBox("layer_scaleX", "",__ug("Width",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_scaleY", "",__ug("Height",REVSLIDER_TEXTDOMAIN),array("hidden"=>false));
	$layerSettings->addCheckbox("layer_proportional_scale", false,__ug("Scale Proportional",REVSLIDER_TEXTDOMAIN),array("hidden"=>false));

	$arrParallaxLevel = array(
							'-' => __ug('No Movement', REVSLIDER_TEXTDOMAIN),
							'1' => __ug('1', REVSLIDER_TEXTDOMAIN),
							'2' => __ug('2', REVSLIDER_TEXTDOMAIN),
							'3' => __ug('3', REVSLIDER_TEXTDOMAIN),
							'4' => __ug('4', REVSLIDER_TEXTDOMAIN),
							'5' => __ug('5', REVSLIDER_TEXTDOMAIN),
							'6' => __ug('6', REVSLIDER_TEXTDOMAIN),
							'7' => __ug('7', REVSLIDER_TEXTDOMAIN),
							'8' => __ug('8', REVSLIDER_TEXTDOMAIN),
							'9' => __ug('9', REVSLIDER_TEXTDOMAIN),
							'10' => __ug('10', REVSLIDER_TEXTDOMAIN),
							);
	$layerSettings->addSelect("parallax_level", $arrParallaxLevel, __ug("Level",REVSLIDER_TEXTDOMAIN),"nowrap", array("hidden"=>false));


	//put left top
	$textOffsetX = __ug("OffsetX",REVSLIDER_TEXTDOMAIN);
	$textX = __ug("X",REVSLIDER_TEXTDOMAIN);
	$params = array("attrib_text"=>"data-textoffset='".$textOffsetX."' data-textnormal='".$textX."'");
	$layerSettings->addTextBox("layer_left", "",__ug("X",REVSLIDER_TEXTDOMAIN),$params);

	$textOffsetY = __ug("OffsetY",REVSLIDER_TEXTDOMAIN);
	$textY = __ug("Y",REVSLIDER_TEXTDOMAIN);
	$params = array("attrib_text"=>"data-textoffset='".$textOffsetY."' data-textnormal='".$textY."'");
	$layerSettings->addTextBox("layer_top", "",__ug("Y",REVSLIDER_TEXTDOMAIN),$params);

	$layerSettings->addTextBox("layer_align_hor", "left",__ug("Hor Align",REVSLIDER_TEXTDOMAIN),array("hidden"=>true));
	$layerSettings->addTextBox("layer_align_vert", "top",__ug("Vert Align",REVSLIDER_TEXTDOMAIN),array("hidden"=>true));

	$para = array("unit"=>__ug("&nbsp;(example: 50px, auto)",REVSLIDER_TEXTDOMAIN), 'hidden'=>true);
	$layerSettings->addTextBox("layer_max_width", "auto",__ug("Max Width",REVSLIDER_TEXTDOMAIN),$para);
	$layerSettings->addTextBox("layer_max_height", "auto",__ug("Max Height",REVSLIDER_TEXTDOMAIN),$para);
	
	$layerSettings->addTextBox("layer_2d_rotation", "0",__ug("2D Rotation",REVSLIDER_TEXTDOMAIN),array("hidden"=>false, 'unit'=>'&nbsp;(-360 - 360)'));
	$layerSettings->addTextBox("layer_2d_origin_x", "50",__ug("Rotation Origin X",REVSLIDER_TEXTDOMAIN),array("hidden"=>false, 'unit'=>'%&nbsp;(-100 - 200)'));
	$layerSettings->addTextBox("layer_2d_origin_y", "50",__ug("Rotation Origin Y",REVSLIDER_TEXTDOMAIN),array("hidden"=>false, 'unit'=>'%&nbsp;(-100 - 200)'));

	//advanced params
	$arrWhiteSpace = array("normal"=>__ug("Normal",REVSLIDER_TEXTDOMAIN),
						"pre"=>__ug("Pre",REVSLIDER_TEXTDOMAIN),
						"nowrap"=>__ug("No-wrap",REVSLIDER_TEXTDOMAIN),
						"pre-wrap"=>__ug("Pre-Wrap",REVSLIDER_TEXTDOMAIN),
						"pre-line"=>__ug("Pre-Line",REVSLIDER_TEXTDOMAIN));


	$layerSettings->addSelect("layer_whitespace", $arrWhiteSpace, __ug("White Space",REVSLIDER_TEXTDOMAIN),"nowrap", array("hidden"=>true));


	$layerSettings->addSelect("layer_slide_link", $arrSlideLinkLayers, __ug("Link To Slide",REVSLIDER_TEXTDOMAIN),"nothing");

	$params = array("unit"=>__ug("px",REVSLIDER_TEXTDOMAIN),"hidden"=>true);
	$layerSettings->addTextBox("layer_scrolloffset", "0",__ug("Scroll Under Slider Offset",REVSLIDER_TEXTDOMAIN),$params);

	$layerSettings->addButton("button_change_image_source", __ug("Change Image Source",REVSLIDER_TEXTDOMAIN),array("hidden"=>true,"class"=>"button-primary revblue"));
	$layerSettings->addTextBox("layer_alt", "","Alt Text",array("hidden"=>true, "class"=>"area-alt-params"));
	$layerSettings->addButton("button_edit_video", __ug("Edit Video",REVSLIDER_TEXTDOMAIN),array("hidden"=>true,"class"=>"button-primary revblue"));



	$params = array("unit"=>__ug("ms",REVSLIDER_TEXTDOMAIN));
	$paramssplit = array("unit"=>__ug(" ms (keep it low i.e. 1- 200)",REVSLIDER_TEXTDOMAIN));
	$params_1 = array("unit"=>__ug("ms",REVSLIDER_TEXTDOMAIN), 'hidden'=>true);
	$layerSettings->addTextBox("layer_endtime", "",__ug("End Time",REVSLIDER_TEXTDOMAIN),$params_1);
	$layerSettings->addTextBox("layer_endspeed", "",__ug("End Duration",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_endsplitdelay", "10","End Split Delay",$paramssplit);
	$layerSettings->addSelect("layer_endsplit", $arrSplit, __ug("Split Text per",REVSLIDER_TEXTDOMAIN),"none");
	$layerSettings->addSelect("layer_endanimation",$arrEndAnimations,__ug("End Animation",REVSLIDER_TEXTDOMAIN),"auto");
	$layerSettings->addSelect("layer_endeasing", $arrEndEasing, __ug("End Easing",REVSLIDER_TEXTDOMAIN),"nothing");
	$params = array("unit"=>__ug("ms",REVSLIDER_TEXTDOMAIN));

	//advanced params
	$arrCorners = array("nothing"=>__ug("No Corner",REVSLIDER_TEXTDOMAIN),
						"curved"=>__ug("Sharp",REVSLIDER_TEXTDOMAIN),
						"reverced"=>__ug("Sharp Reversed",REVSLIDER_TEXTDOMAIN));
	$params = array();
	$layerSettings->addSelect("layer_cornerleft", $arrCorners, __ug("Left Corner",REVSLIDER_TEXTDOMAIN),"nothing",$params);
	$layerSettings->addSelect("layer_cornerright", $arrCorners, __ug("Right Corner",REVSLIDER_TEXTDOMAIN),"nothing",$params);
	$layerSettings->addCheckbox("layer_resizeme", true,__ug("Responsive Through All Levels",REVSLIDER_TEXTDOMAIN),$params);

	$params = array();
	$layerSettings->addTextBox("layer_id", "",__ug("ID",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_classes", "",__ug("Classes",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_title", "",__ug("Title",REVSLIDER_TEXTDOMAIN),$params);
	$layerSettings->addTextBox("layer_rel", "",__ug("Rel",REVSLIDER_TEXTDOMAIN),$params);

	//Loop Animation
	$arrAnims = array("none"=>__ug("Disabled",REVSLIDER_TEXTDOMAIN),
						"rs-pendulum"=>__ug("Pendulum",REVSLIDER_TEXTDOMAIN),
						"rs-rotate"=>__ug("Rotate",REVSLIDER_TEXTDOMAIN),						
						"rs-slideloop"=>__ug("Slideloop",REVSLIDER_TEXTDOMAIN),
						"rs-pulse"=>__ug("Pulse",REVSLIDER_TEXTDOMAIN),
						"rs-wave"=>__ug("Wave",REVSLIDER_TEXTDOMAIN)
						);

	$params = array();
	$layerSettings->addSelect("layer_loop_animation", $arrAnims, __ug("Animation",REVSLIDER_TEXTDOMAIN),"none",$params);
	$layerSettings->addTextBox("layer_loop_speed", "2",__ug("Speed",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("&nbsp;(0.00 - 10.00)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_startdeg", "-20",__ug("Start Degree",REVSLIDER_TEXTDOMAIN));
	$layerSettings->addTextBox("layer_loop_enddeg", "20",__ug("End Degree",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("&nbsp;(-720 - 720)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_xorigin", "50",__ug("x Origin",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("%",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_yorigin", "50",__ug("y Origin",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("% (-250% - 250%)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_xstart", "0",__ug("x Start Pos.",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("px",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_xend", "0",__ug("x End Pos.",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("px (-2000px - 2000px)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_ystart", "0",__ug("y Start Pos.",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("px",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_yend", "0",__ug("y End Pos.",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("px (-2000px - 2000px)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_zoomstart", "1",__ug("Start Zoom",REVSLIDER_TEXTDOMAIN));
	$layerSettings->addTextBox("layer_loop_zoomend", "1",__ug("End Zoom",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("&nbsp;(0.00 - 20.00)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_angle", "0",__ug("Angle",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("° (0° - 360°)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addTextBox("layer_loop_radius", "10",__ug("Radius",REVSLIDER_TEXTDOMAIN),array("unit"=>__ug("px (0px - 2000px)",REVSLIDER_TEXTDOMAIN)));
	$layerSettings->addSelect("layer_loop_easing", $arrEasing, __ug("Easing",REVSLIDER_TEXTDOMAIN),"Power3.easeInOut");

	self::storeSettings("layer_settings",$layerSettings);

	//store settings of content css for editing on the client.
	self::storeSettings("css_captions_content",$contentCSS);

?>