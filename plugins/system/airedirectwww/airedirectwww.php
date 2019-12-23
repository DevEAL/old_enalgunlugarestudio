<?php
/**
 * @version     $Id$ 1.0.4 0
 * @package     aiRedirectWww
 * @copyright   Copyright (C)2012 Alex Dobrin. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
 
jimport( 'joomla.plugin.plugin' );

class plgSystemAiRedirectWww extends JPlugin {
	function plgSystemAiRedirectWww( $subject, $params ) {
		parent::__construct( $subject, $params );
	}

	/**
	 * Redirects the page to the domain with or without www, depending on it's parameters
	 */
	function onAfterRoute()	{
		// get the parameter to check localhost or not
		$check_localhost = (int)$this->params->get('check_localhost', 1);
		// get the parameter to check when to redirect
		$redirect_site = (int)$this->params->get('redirect_site', 0);
		// check if the plugin was not deactivated
		$noaiwww = JRequest::GetInt('noaiwww');
		// if $noaiwww is not set check the cookies
		if($noaiwww == 0) {
			$noaiwww = JRequest::getVar('airedirectwww_noaiwww', 0, 'cookie', 'int');
		}
		// $noaiwww was defined record it in a cookie
		if($noaiwww != 0) {
			JRequest::setVar('airedirectwww_noaiwww', $noaiwww, 'cookie');
			setcookie( 'airedirectwww_noaiwww', $noaiwww, time()+(int)$this->params->get('cookie_lifetime', 300), '/');
		}
		// check if the backend is displayed
		$app = JFactory::getApplication();
		$backend = $app->getClientId();
		// if the plugin was not deactivated, the page is in backend or front-end 
		// as required by redirect_site, check the domain and make the redirection
		if( $noaiwww == 0 && ( ($redirect_site == 1 && !$backend ) || ($redirect_site == 2 && $backend) || $redirect_site == 3 ) ) {
			// get the current host
			$uri = JURI::getInstance();
			$currentHost = strtolower(trim($uri->getHost()));
			if($check_localhost && ( $currentHost == 'localhost' || $currentHost == '127.0.0.1' )) {
				// do not redirect the page
			} else {
				// read if the page should be redirected to the domain with or without wwww
				$with_www = (int)$this->params->get('with_www', 1);
				// initalize the link to redirect the page to
				$url_redirect = '';
				if( $with_www ) {
					// if the domain with www is required and it is not used set the redirect url to the domain with www
					if(substr($currentHost,0,4) != 'www.') {
						$uri->setHost('www.'.$currentHost);
						$url_redirect = $uri->toString();
					}
				} else {
					// if the domain without www is required and it is not used set the redirect url to the domain without www
					if(substr($currentHost,0,4) == 'www.') {
						$uri->setHost(substr($currentHost,4));
						$url_redirect = $uri->toString();
					}
				}
				// if redirect url was set make the redirection
				if( strlen($url_redirect) > 0) {
					// get the parameter to set the type of redirect
					$moved_redirect = (int)$this->params->get('moved_redirect', 0);
					$moved_redirect = $moved_redirect == 1;
					// redirect
					$app->redirect($url_redirect, '', 'message', $moved_redirect);
				}
			}
		}
	}
}
