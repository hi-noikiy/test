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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method int getId()
 * @method Justselling_Configurator_Model_Valuechildstatus setId(int $value)
 * @method int getIsRequire()
 * @method Justselling_Configurator_Model_Valuechildstatus setIsRequire(int $value)
 * @method string getPrice()
 * @method Justselling_Configurator_Model_Valuechildstatus setPrice(string $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Valuechildstatus setStatus(string $value)
 * @method int getMaxValue()
 * @method Justselling_Configurator_Model_Valuechildstatus setMaxValue(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Valuechildstatus setOptionId(int $value)
 * @method int getOptionValueId()
 * @method Justselling_Configurator_Model_Valuechildstatus setOptionValueId(int $value)
 * @method int getMinValue()
 * @method Justselling_Configurator_Model_Valuechildstatus setMinValue(int $value)
 */

class Justselling_Configurator_Model_Valuechildstatus extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/valuechildstatus');
	}

}