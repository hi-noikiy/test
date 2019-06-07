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
 
class Justselling_Configurator_Model_Mysql4_Optionfontconfiguration_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{	
	public function _construct()
	{
		$this->_init('configurator/optionfontconfiguration');		
	}
}