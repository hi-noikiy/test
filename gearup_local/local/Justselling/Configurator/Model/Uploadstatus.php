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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

/**
 * @method string getMessage()
 * @method Justselling_Configurator_Model_Uploadstatus setMessage(string $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Uploadstatus setId(int $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Uploadstatus setStatus(string $value)
 * @method string getCachekey()
 * @method Justselling_Configurator_Model_Uploadstatus setCachekey(string $value)
 * @method int getIterationcount()
 * @method Justselling_Configurator_Model_Uploadstatus setIterationcount(int $value)
 */

class Justselling_Configurator_Model_Uploadstatus extends Mage_Core_Model_Abstract
{

	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/uploadstatus');
	}

	public function getByCachekey($uploadcachekey){
		$uploadstatus = Mage::getModel('configurator/uploadstatus')->getCollection()->addFieldToFilter('cachekey', $uploadcachekey)->getFirstItem();

		if(!$uploadstatus->getId()){
			$uploadstatus->setCachekey($uploadcachekey);
			$uploadstatus->setStatus('loading');
			$uploadstatus->save();
		}
		return $uploadstatus;
	}

}