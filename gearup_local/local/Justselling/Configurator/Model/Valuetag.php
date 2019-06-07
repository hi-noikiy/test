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
 * @method Justselling_Configurator_Model_Valuetag setId(int $value)
 * @method string getTag()
 * @method Justselling_Configurator_Model_Valuetag setTag(string $value)
 * @method int getOptionValueId()
 * @method Justselling_Configurator_Model_Valuetag setOptionValueId(int $value)
 */

class Justselling_Configurator_Model_Valuetag extends Mage_Core_Model_Abstract
{	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/valuetag');
	}

	public function loadByOptionValueIdAndTag($option_value_id, $tag) {
		$collection = Mage::getModel("configurator/valuetag")->getCollection();
		$collection->addFieldToFilter("tag", $tag);
		$collection->addFieldToFilter("option_value_id", $option_value_id);
		if ($collection->getFirstItem()->getId()) {
			$this->load($collection->getFirstItem()->getId());
			return $this;
		}
		return NULL;
	}
	
}