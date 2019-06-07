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
 * @method int getId()
 * @method Justselling_Configurator_Model_Optionblacklist setId(int $value)
 * @method string getValue()
 * @method Justselling_Configurator_Model_Optionblacklist setValue(string $value)
 * @method int getChildOptionValueId()
 * @method Justselling_Configurator_Model_Optionblacklist setChildOptionValueId(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionblacklist setOptionId(int $value)
 * @method string getOperator()
 * @method Justselling_Configurator_Model_Optionblacklist setOperator(string $value)
 */
class Justselling_Configurator_Model_Optionblacklist extends Mage_Core_Model_Abstract
{
	/* @var Justselling_Configurator_Model_Template */
	protected $_template;
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionblacklist');
	}
}