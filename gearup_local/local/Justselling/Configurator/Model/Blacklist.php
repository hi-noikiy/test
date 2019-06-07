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
 * @method Justselling_Configurator_Model_Blacklist setId(int $value)
 * @method int getChildOptionId()
 * @method Justselling_Configurator_Model_Blacklist setChildOptionId(int $value)
 * @method int getChildOptionValueId()
 * @method Justselling_Configurator_Model_Blacklist setChildOptionValueId(int $value)
 * @method int getOptionValueId()
 * @method Justselling_Configurator_Model_Blacklist setOptionValueId(int $value)
 */
class Justselling_Configurator_Model_Blacklist extends Mage_Core_Model_Abstract
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
		$this->_init('configurator/blacklist');
	}
	
	public function loadByOptionValueIdAndChildOptionId($option_value_id, $child_option_id) {
		$collection = Mage::getModel("configurator/blacklist")->getCollection();
		$collection->addFieldToFilter("child_option_id", $child_option_id);
		$collection->addFieldToFilter("option_value_id", $option_value_id);
		if ($collection->getFirstItem()->getId()) {
			$this->load($collection->getFirstItem()->getId());
			return $this;
		}
		return NULL;
	}
	
	public function loadByOptionValueIdAndChildOptionValueId($option_value_id, $child_option_value_id) {
		$collection = Mage::getModel("configurator/blacklist")->getCollection();
		$collection->addFieldToFilter("child_option_value_id", $child_option_value_id);
		$collection->addFieldToFilter("option_value_id", $option_value_id);
		if ($collection->getFirstItem()->getId()) {
			$this->load($collection->getFirstItem()->getId());
			return $this;
		}
		return NULL;
	}
}