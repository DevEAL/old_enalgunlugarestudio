<?php
/**
 * @package Helix3 Framework
 * @author AgeThemes http://www.agethemes.com
 * @copyright Copyright (c) 2010 - 2015 AgeThemes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

class Helix3FeatureFooter {

	private $helix3;

	public function __construct($helix3){
		$this->helix3 = $helix3;
		$this->position = $this->helix3->getParam('copyright_position');
	}

	public function renderFeature() {

		if($this->helix3->getParam('enabled_copyright')) {

			$output = '';

			if($this->helix3->getParam('copyright')) $output .= '<span class="sp-copyright"> ' . $this->helix3->getParam('copyright') . '</span>';
			
			return $output;
		}
		
	}    
}