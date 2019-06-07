<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license      http://www.justselling.de/lizenz
 * @author	   Daniel Mueller
 */

class Justselling_Configurator_Model_Adminpanel_Observer extends Mage_Core_Model_Abstract
{
	public function admin_session_user_login_success($observer) {
		$obj = new Justselling_Configurator_Block_Loading;
		$obj->checkLicense();
	}

    public function afterSaveSystemConfig($observer) {
        $controllerAction = $observer->getControllerAction();
        $request = $controllerAction->getRequest();
        $params = $request->getParams();
        $section = $params["section"];

        if ($section === 'productconfigurator') {
            $cache = Mage::app()->getCache();
            $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF"));
        }
    }
}
