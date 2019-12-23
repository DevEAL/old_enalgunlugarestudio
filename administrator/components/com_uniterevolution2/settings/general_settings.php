<?php
	
	$generalSettings = new UniteSettingsRev();
	
	$generalSettings->addSelect("role", 
								array(UniteBaseAdminClassRev::ROLE_ADMIN => __ug("To Admin",REVSLIDER_TEXTDOMAIN),
									  UniteBaseAdminClassRev::ROLE_EDITOR =>__ug("To Editor, Admin",REVSLIDER_TEXTDOMAIN),
									  UniteBaseAdminClassRev::ROLE_AUTHOR =>__ug("Author, Editor, Admin",REVSLIDER_TEXTDOMAIN)),									  
									  __ug("View Plugin Permission",REVSLIDER_TEXTDOMAIN), 
									  UniteBaseAdminClassRev::ROLE_ADMIN, 
									  array("description"=>__ug("The role of user that can view and edit the plugin",REVSLIDER_TEXTDOMAIN)));

	$generalSettings->addRadio("includes_globally", 
							   array("on"=>__ug("On",REVSLIDER_TEXTDOMAIN),"off"=>__ug("Off",REVSLIDER_TEXTDOMAIN)),
							   __ug("Include RevSlider libraries globally",REVSLIDER_TEXTDOMAIN),
							   "on",
							   array("description"=>__ug("Add css and js includes only on all pages. Id turned to off they will added to pages where the rev_slider shortcode exists only. This will work only when the slider added by a shortcode.",REVSLIDER_TEXTDOMAIN)));
	
	$generalSettings->addTextBox("pages_for_includes", "",__ug("Pages to include RevSlider libraries",REVSLIDER_TEXTDOMAIN),
								  array("description"=>__ug("Specify the page id's that the front end includes will be included in. Example: 2,3,5 also: homepage,3,4",REVSLIDER_TEXTDOMAIN)));
									  
	$generalSettings->addRadio("js_to_footer", 
							   array("on"=>__ug("On",REVSLIDER_TEXTDOMAIN),"off"=>__ug("Off",REVSLIDER_TEXTDOMAIN)),
							   __ug("Put JS Includes To Footer",REVSLIDER_TEXTDOMAIN),
							   "off",
							   array("description"=>__ug("Putting the js to footer (instead of the head) is good for fixing some javascript conflicts.",REVSLIDER_TEXTDOMAIN)));
	
	$generalSettings->addRadio("show_dev_export", 
							   array("on"=>__ug("On",REVSLIDER_TEXTDOMAIN),"off"=>__ug("Off",REVSLIDER_TEXTDOMAIN)),
							   __ug("Enable Markup Export option",REVSLIDER_TEXTDOMAIN),
							   "off",
							   array("description"=>__ug("This will enable the option to export the Slider Markups to copy/paste it directly into websites.",REVSLIDER_TEXTDOMAIN)));
		
	$generalSettings->addRadio("enable_logs", 
							   array("on"=>__ug("On",REVSLIDER_TEXTDOMAIN),"off"=>__ug("Off",REVSLIDER_TEXTDOMAIN)),
							   __ug("Enable Logs",REVSLIDER_TEXTDOMAIN),
							   "off",
							   array("description"=>__ug("Enable console logs for debugging.",REVSLIDER_TEXTDOMAIN)));
	//transition
	/*$operations = new RevOperations();
	$arrTransitions = $operations->getArrTransition();
	$arrPremiumTransitions = $operations->getArrTransition(true);
	
	$arrTransitions = array_merge($arrTransitions, $arrPremiumTransitions);
	$params = array("description"=>"<br>".__ug("The default appearance transitions of slides.",REVSLIDER_TEXTDOMAIN),"minwidth"=>"450px");
	$generalSettings->addRadio("slide_transition_default",$arrTransitions,__ug("Default Transition",REVSLIDER_TEXTDOMAIN),"random",$params);
	*/
	//--------------------------
	
	//get stored values
	$operations = new RevOperations();
	$arrValues = $operations->getGeneralSettingsValues();
	$generalSettings->setStoredValues($arrValues);
	
	self::storeSettings("general", $generalSettings);

?>