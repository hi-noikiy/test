<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_ConfigController extends Mage_Core_Controller_Front_Action
{
	public function blacklistAction() {
		session_write_close();
		$params = $this->getRequest()->getParams();
		if (!isset($params['templateid'])) {
			Js_Log::log("Can't load config because no template-id was given!", "configurator", Zend_Log::ERR, true);
			return false;
		}
		$templateid = $params['templateid'];


		$blacklistJson = Mage::helper('configurator/config')->getBlacklistJson($templateid);

		if($blacklistJson){
			$this->getResponse()->setBody($blacklistJson);
			return true;
		}else{
			$this->getResponse()->setBody("");
			return false;
		}
	}
}