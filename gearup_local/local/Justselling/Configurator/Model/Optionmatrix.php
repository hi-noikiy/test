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
 * @method Justselling_Configurator_Model_Optionmatrix setId(int $value)
 * @method string getMatrix()
 * @method Justselling_Configurator_Model_Optionmatrix setMatrix(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionmatrix setOptionId(int $value)
 */
class Justselling_Configurator_Model_Optionmatrix extends Mage_Core_Model_Abstract
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionmatrix');
	}
	
	public function loadByOptionId($option_id) {
		$collection = Mage::getModel("configurator/optionmatrix")->getCollection();
		$collection->addFieldToFilter("option_id", $option_id);
		
		if ($collection->getFirstItem())
				return $collection->getFirstItem();
		else
				return $this;
	}
}
