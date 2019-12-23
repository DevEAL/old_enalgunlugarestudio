<?php

	//set Slide settings
	$arrTransitions = $operations->getArrTransition();
	$arrPremiumTransitions = $operations->getArrTransition(true);
	$defaultTransition = $operations->getDefaultTransition();

	$arrSlideNames = array();
	if(isset($slider) && $slider->isInited())
		$arrSlideNames = $slider->getArrSlideNames();

	$slideSettings = new UniteSettingsAdvancedRev();

	//title
	$params = array("description"=>__ug("The title of the slide, will be shown in the slides list.",REVSLIDER_TEXTDOMAIN),"class"=>"medium");
	$slideSettings->addTextBox("title",__ug("Slide",REVSLIDER_TEXTDOMAIN),__ug("Slide Title",REVSLIDER_TEXTDOMAIN), $params);

	//state
	$params = array("description"=>__ug("The state of the slide. The unpublished slide will be excluded from the slider.",REVSLIDER_TEXTDOMAIN));
	$slideSettings->addSelect("state",array("published"=>__ug("Published",REVSLIDER_TEXTDOMAIN),"unpublished"=>__ug("Unpublished",REVSLIDER_TEXTDOMAIN)),__ug("State",REVSLIDER_TEXTDOMAIN),"published",$params);

	if(isset($slider) && $slider->isInited()){
		$isWpmlExists = UniteWpmlRev::isWpmlExists();
		$useWpml = $slider->getParam("use_wpml","off");

		if($isWpmlExists && $useWpml == "on"){
			$arrLangs = UniteWpmlRev::getArrLanguages();
			$params = array("description"=>__ug("The language of the slide (uses WPML plugin).",REVSLIDER_TEXTDOMAIN));
			$slideSettings->addSelect("lang",$arrLangs,__ug("Language",REVSLIDER_TEXTDOMAIN),"all",$params);
		}
	}

	$params = array("description"=>__ug("If set, slide will be visible after the date is reached",REVSLIDER_TEXTDOMAIN));
	$slideSettings->addDatePicker("date_from","",__ug("Visible from",REVSLIDER_TEXTDOMAIN), $params);

	$params = array("description"=>__ug("If set, slide will be visible till the date is reached",REVSLIDER_TEXTDOMAIN));
	$slideSettings->addDatePicker("date_to","",__ug("Visible until",REVSLIDER_TEXTDOMAIN), $params);

	$slideSettings->addHr("");

	//transition
	$params = array("description"=>__ug("The appearance transitions of this slide.",REVSLIDER_TEXTDOMAIN),"minwidth"=>"250px");
	$slideSettings->addChecklist("slide_transition",$arrTransitions,__ug("Transitions",REVSLIDER_TEXTDOMAIN),$defaultTransition,$params);

	//slot amount
	$params = array("description"=>__ug("The number of slots or boxes the slide is divided into. If you use boxfade, over 7 slots can be juggy.",REVSLIDER_TEXTDOMAIN)
		,"class"=>"small","datatype"=>"number"
	);
	$slideSettings->addTextBox("slot_amount","7",__ug("Slot Amount",REVSLIDER_TEXTDOMAIN), $params);

	//rotation:
	$params = array("description"=>__ug("Rotation (-720 -> 720, 999 = random) Only for Simple Transitions.",REVSLIDER_TEXTDOMAIN)
		,"class"=>"small","datatype"=>"number"
	);
	$slideSettings->addTextBox("transition_rotation","0",__ug("Rotation",REVSLIDER_TEXTDOMAIN), $params);

	//transition speed
	$params = array("description"=>__ug("The duration of the transition (Default:300, min: 100 max 2000). ",REVSLIDER_TEXTDOMAIN)
		,"class"=>"small","datatype"=>"number"
	);
	$slideSettings->addTextBox("transition_duration","300",__ug("Transition Duration",REVSLIDER_TEXTDOMAIN), $params);
	

	if(!isset($sliderDelay))
		$sliderDelay = 0;

	//delay
	$params = array("description"=>__ug("A new delay value for the Slide. If no delay defined per slide, the delay defined via Options (",REVSLIDER_TEXTDOMAIN). $sliderDelay .__ug("ms) will be used",REVSLIDER_TEXTDOMAIN)
		,"class"=>"small","datatype"=>UniteSettingsRev::DATATYPE_NUMBEROREMTY
	);
	$slideSettings->addTextBox("delay","",__ug("Delay",REVSLIDER_TEXTDOMAIN), $params);

	$params = array("description"=>__ug("",REVSLIDER_TEXTDOMAIN)
		,"class"=>"small"
	);
	
	$slideSettings->addRadio("save_performance", array("on"=>__ug("On",REVSLIDER_TEXTDOMAIN),"off"=>__ug("Off",REVSLIDER_TEXTDOMAIN)), __ug("Save Performance",REVSLIDER_TEXTDOMAIN),"off", $params);
	
	$slideSettings->addHr("");

	//-----------------------

	//enable link
	$slideSettings->addSelect_boolean("enable_link", __ug("Enable Link",REVSLIDER_TEXTDOMAIN), false, __ug("Enable",REVSLIDER_TEXTDOMAIN),__ug("Disable",REVSLIDER_TEXTDOMAIN));

	$slideSettings->startBulkControl("enable_link", UniteSettingsRev::CONTROL_TYPE_SHOW, "true");

		//link type
		$slideSettings->addRadio("link_type", array("regular"=>__ug("Regular",REVSLIDER_TEXTDOMAIN),"slide"=>__ug("To Slide",REVSLIDER_TEXTDOMAIN)), __ug("Link Type",REVSLIDER_TEXTDOMAIN),"regular");

		//link
		$params = array('id'=>'rev_link', "description"=>__ug("A link on the whole slide pic (use %link% or %meta:somemegatag% in template sliders to link to a post or some other meta)",REVSLIDER_TEXTDOMAIN));
		$slideSettings->addTextBox("link","",__ug("Slide Link",REVSLIDER_TEXTDOMAIN), $params);

		//link target
		$params = array("description"=>__ug("The target of the slide link",REVSLIDER_TEXTDOMAIN));
		$slideSettings->addSelect("link_open_in",array("same"=>__ug("Same Window",REVSLIDER_TEXTDOMAIN),"new"=>__ug("New Window")),__ug("Link Open In",REVSLIDER_TEXTDOMAIN),"same",$params);

		//num_slide_link
		$arrSlideLink = array();
		$arrSlideLink["nothing"] = __ug("-- Not Chosen --",REVSLIDER_TEXTDOMAIN);
		$arrSlideLink["next"] = __ug("-- Next Slide --",REVSLIDER_TEXTDOMAIN);
		$arrSlideLink["prev"] = __ug("-- Previous Slide --",REVSLIDER_TEXTDOMAIN);

		$arrSlideLinkLayers = $arrSlideLink;
		$arrSlideLinkLayers["scroll_under"] = __ug("-- Scroll Below Slider --",REVSLIDER_TEXTDOMAIN);

		foreach($arrSlideNames as $slideNameID=>$arr){
			$slideName = $arr["title"];
			$arrSlideLink[$slideNameID] = $slideName;
			$arrSlideLinkLayers[$slideNameID] = $slideName;
		}

		$slideSettings->addSelect("slide_link", $arrSlideLink, "Link To Slide","nothing");

		$params = array("description"=>"The position of the link related to layers");
		$slideSettings->addRadio("link_pos", array("front"=>"Front","back"=>"Back"), "Link Position","front",$params);

		//$slideSettings->addHr("link_sap");

	$slideSettings->endBulkControl();

		$slideSettings->addControl("link_type", "slide_link", UniteSettingsRev::CONTROL_TYPE_ENABLE, "slide");
		$slideSettings->addControl("link_type", "link", UniteSettingsRev::CONTROL_TYPE_DISABLE, "slide");
		$slideSettings->addControl("link_type", "link_open_in", UniteSettingsRev::CONTROL_TYPE_DISABLE, "slide");

	//-----------------------

	$slideSettings->addHr("");

	$params = array("description"=>__ug("Slide Thumbnail. If not set - it will be taken from the slide image.",REVSLIDER_TEXTDOMAIN));
	$slideSettings->addImage("slide_thumb", "",__ug("Thumbnail",REVSLIDER_TEXTDOMAIN) , $params);

	//$params = array("description"=>__ug("Apply to full width mode only. Centering vertically slide images.",REVSLIDER_TEXTDOMAIN));
	//$slideSettings->addCheckbox("fullwidth_centering", false, __ug("Full Width Centering",REVSLIDER_TEXTDOMAIN), $params);


	//add background type (hidden)
	$slideSettings->addTextBox("background_type","image",__ug("Background Type",REVSLIDER_TEXTDOMAIN), array("hidden"=>true));

	//store settings
	
	$slideSettings->addHr("");
	
	$params = array("description"=>__ug('Adds a unique class to the li of the Slide like class="rev_special_class" (add only the classnames, seperated by space)',REVSLIDER_TEXTDOMAIN));
	$slideSettings->addTextBox("class_attr","",__ug("Class",REVSLIDER_TEXTDOMAIN), $params);
	
	$params = array("description"=>__ug('Adds a unique ID to the li of the Slide like id="rev_special_id" (add only the id)',REVSLIDER_TEXTDOMAIN));
	$slideSettings->addTextBox("id_attr","",__ug("ID",REVSLIDER_TEXTDOMAIN), $params);
	
	$params = array("description"=>__ug('Adds a unique Attribute to the li of the Slide like attr="rev_special_attr" (add only the attribute)',REVSLIDER_TEXTDOMAIN));
	$slideSettings->addTextBox("attr_attr","",__ug("Attribute",REVSLIDER_TEXTDOMAIN), $params);
	
	$params = array("description"=>__ug('Add as many attributes as you wish here. (i.e.: data-layer="firstlayer" data-custom="somevalue")',REVSLIDER_TEXTDOMAIN));
	$slideSettings->addTextArea("data_attr","",__ug("Custom Fields",REVSLIDER_TEXTDOMAIN), $params);
	
	self::storeSettings("slide_settings",$slideSettings);

?>
