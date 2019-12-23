<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.folder');
 
class plgSystemBaforms extends JPlugin
{
    public function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
    }

    function onAfterInitialise()
    {
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $path = JPATH_ROOT . '/components/com_baforms/helpers/baforms.php';
            $dir = $this->checkOverride();
            if ($dir) {
                $path = $dir;
            }
            JLoader::register('baformsHelper', $path);
        }
    }
    
    public function checkOverride()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('template')
            ->from('#__template_styles')
            ->where('`client_id`=0')
            ->where('`home`=1');
        $db->setQuery($query);
        $template = $db->loadResult();
        $path = JPATH_ROOT. '/templates/' .$template. '/html/com_baforms';
        if (JFolder::exists($path)) {
            if (JFolder::exists($path. '/helpers')) {
                $file = JFolder::files($path. '/helpers', 'baforms.php');
                if (!empty($file)) {
                    return $path. '/helpers/baforms.php';
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        
    }
    
    function onBeforeCompileHead()
    {
        $app = JFactory::getApplication();
        $doc = JFactory::getDocument();
        if ($app->isSite() && $doc->getType() == 'html') {
            $loaded = JLoader::getClassList();
            if (isset($loaded['baformshelper'])) {
                $a_id = $app->input->get('a_id');
                if (empty($a_id)) {
                    baformsHelper::addStyle();
                }
            }
        }
    }
    
    function onAfterRender()
    {
        $app = JFactory::getApplication();
        $a_id = $app->input->get('a_id');
        $doc = JFactory::getDocument();
        if ($app->isSite() && empty($a_id) && $doc->getType() == 'html') {
            $loaded = JLoader::getClassList();
            if (isset($loaded['baformshelper'])) {
                $html = JResponse::getBody();
                $pos = strpos($html, '</head>');
                $head = substr($html, 0, $pos);
                $body = substr($html, $pos);
                $html = $head.$this->getContent($body);
                JResponse::setBody($html);
            }
        }
    }
    
    function getContent($body)
    {
        $regex = '/\[forms ID=+(.*?)\]/i';
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $index => $match) {
                $form = explode(',', $match[1]);
                $formId = $form[0];
                if (isset($formId)) {
                    if (baformsHelper::checkForm($formId)) {
                        $doc = JFactory::getDocument();
                        $form = baformsHelper::drawHTMLPage($formId);
                        if (!array_key_exists(JURI::root() . 'components/com_baforms/assets/js/ba-form.js', $doc->_scripts)) {
                            $form = $this->drawScripts($formId).$form;
                        }
                        $pop = $this->getType($formId);
                        if ($pop['button_type'] == 'link' && $pop['display_popup'] == 1) {
                            $body = @preg_replace("|\[forms ID=".$formId."\]|", '<a style="display:none" class="baform-replace">[forms ID='.$formId.']</a>', $body, 1);
                            $body = $body.$form;
                        } else {
                            $body = @preg_replace("|\[forms ID=".$formId."\]|", addcslashes($form, '\\$'), $body, 1);
                        }
                    }
                }
                }
        }
        return $body;
    }

    public function getType($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("display_popup, button_type");
        $query->from("#__baforms_forms");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadAssoc();
        return $items;
    }
    
    public function drawScripts($id)
    {
        $doc = JFactory::getDocument();
        $scripts = $doc->_scripts;
        $array = array();
        $map = true;
        foreach ($scripts as $key=>$script) {
            if (strpos($key, 'maps.googleapis.com/maps/api/js?libraries=places')) {
                $map = false;
            }
            $key = explode('/', $key);
            $array[] = end($key);
        }
        $html = '';
        if (!in_array('jquery.min.js', $array) && !in_array('jquery.js', $array)) {
            $html .= '<script type="text/javascript" src="' .JUri::root(true). '/media/jui/js/jquery.min.js"></script>';
        }
        $captcha = baformsHelper::getCaptcha($id);
        if ($captcha != '0') {
            $captch = JCaptcha::getInstance($captcha);
            $captch->initialise($captcha);
        }
        $elements = baformsHelper::getElement($id);
        foreach ($elements as $element) {
            $element = explode('_-_', $element->settings);
            if ($element[2] == 'map' || $element[2] == 'address') {
                if ($map) {
                    $api_key = baformsHelper::getMapsKey();
                    $src = 'https://maps.googleapis.com/maps/api/js?libraries=places&key='.$api_key;
                    $html .= '<script type="text/javascript" src="'.$src.'"></script>';
                }
            }
            if ($element[2] == 'date') {
                $html .= '<script type="text/javascript" src="'.JUri::root(true) . '/media/system/js/calendar.js"></script>';
                $html .= '<script type="text/javascript" src="'.JUri::root(true) . '/media/system/js/calendar-setup.js"></script>';
                $html .= '<script type="text/javascript">'.$this->setCalendar().'</script>';
                $html .= '<link rel="stylesheet" href="' .JUri::root(true) . '/media/system/css/calendar-jos.css">';
            }
            if ($element[2] == 'slider') {
                $html .= '<script type="text/javascript" src="'.JUri::root() . 'components/com_baforms/libraries/bootstrap-slider/bootstrap-slider.js"></script>';
            }
        }
        $html .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">';
        $html .= '<script type="text/javascript" src="' .JUri::root() . 'components/com_baforms/libraries/modal/ba_modal.js"></script>';
        $html .= '<link rel="stylesheet" href="' .JURI::root() . 'components/com_baforms/assets/css/ba-style.css">';
        $html .= '<script type="text/javascript" src="' .JURI::root() . 'components/com_baforms/assets/js/ba-form.js"></script>';
        
        return $html; 
    }

    public function setCalendar()
    {
        $_DN = array('SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY',
                                'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');
        $_SDN = array('SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN');
        $_MN = array('JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY',
                             'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER');
        $_SMN = array('JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT',
                              'MAY_SHORT', 'JUNE_SHORT', 'JULY_SHORT', 'AUGUST_SHORT',
                              'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT');
        $today = " " . JText::_('JLIB_HTML_BEHAVIOR_TODAY') . " ";
        $_TT = array('INFO' => JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR'),
                      'ABOUT' => "DHTML Date/Time Selector\n"
                      . "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n"
                      . "For latest version visit: http://www.dynarch.com/projects/calendar/\n"
                      . "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details."
                      . "\n\n" . JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION')
                      . JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT')
                      . JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT')
                      . JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE'),
                      'ABOUT_TIME' => "\n\n"
                      . "Time selection:\n"
                      . "- Click on any of the time parts to increase it\n"
                      . "- or Shift-click to decrease it\n"
                      . "- or click and drag for faster selection.",
                      'PREV_YEAR' => JText::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU'),
                      'PREV_MONTH' => JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU'),
                      'GO_TODAY' => JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY'),
                      'NEXT_MONTH' => JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU'),
                      'SEL_DATE' => JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE'),
                      'DRAG_TO_MOVE' => JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE'),
                      'PART_TODAY' => $today,
                      'DAY_FIRST' => JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'),
                      'WEEKEND' => JFactory::getLanguage()->getWeekEnd(),
                      'CLOSE' => JText::_('JLIB_HTML_BEHAVIOR_CLOSE'),
                      'TODAY' => JText::_('JLIB_HTML_BEHAVIOR_TODAY'),
                      'TIME_PART' => JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE'),
                      'DEF_DATE_FORMAT' => "%Y-%m-%d",
                      'TT_DATE_FORMAT' => JText::_('JLIB_HTML_BEHAVIOR_TT_DATE_FORMAT'),
                      'WK' => JText::_('JLIB_HTML_BEHAVIOR_WK'),
                      'TIME' => JText::_('JLIB_HTML_BEHAVIOR_TIME')
        );

        return 'jQuery(document).ready(function(){Calendar._DN = ' . json_encode($_DN) . ';'
            . ' Calendar._SDN = ' . json_encode($_SDN) . ';'
            . ' Calendar._FD = 0;'
            . ' Calendar._MN = ' . json_encode($_MN) . ';'
            . ' Calendar._SMN = ' . json_encode($_SMN) . ';'
            . ' Calendar._TT = ' . json_encode($_TT) . ';});';
    }
}