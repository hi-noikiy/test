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
 * @method Justselling_Configurator_Model_Pricelist setId(int $value)
 * @method string getValue()
 * @method Justselling_Configurator_Model_Pricelist setValue(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Pricelist setOptionId(int $value)
 * @method string getOperator()
 * @method Justselling_Configurator_Model_Pricelist setOperator(string $value)
 */

class Justselling_Configurator_Model_Pricelist extends Mage_Core_Model_Abstract
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
		$this->_init('configurator/pricelist');
	}
	
	function getPrice() {
		$price = $this->price;
		
		$option = Mage::getModel('configurator/option')->load($this->getOptionId());
		$price = Mage::helper('configurator')->getDiscountPrice($option, $price);

		return $price;
	}
	
}