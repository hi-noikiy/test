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
 * @method Justselling_Configurator_Model_Pricelistvalue setId(int $value)
 * @method string getPrice()
 * @method Justselling_Configurator_Model_Pricelistvalue setPrice(string $value)
 * @method string getValue()
 * @method Justselling_Configurator_Model_Pricelistvalue setValue(string $value)
 * @method int getOptionValueId()
 * @method Justselling_Configurator_Model_Pricelistvalue setOptionValueId(int $value)
 * @method string getOperatorValuePrice()
 * @method Justselling_Configurator_Model_Pricelistvalue setOperatorValuePrice(string $value)
 * @method string getOperator()
 * @method Justselling_Configurator_Model_Pricelistvalue setOperator(string $value)
 */

class Justselling_Configurator_Model_Pricelistvalue extends Mage_Core_Model_Abstract
{
	/**
	 * 
	 * Template
	 * @var Justselling_Configurator_Model_Template
	 */
	protected $_template;
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/pricelistvalue');
	}
}