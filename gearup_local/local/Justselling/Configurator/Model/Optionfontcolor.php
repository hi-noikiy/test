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
 * @method Justselling_Configurator_Model_Optionfontcolor setId(int $value)
 * @method string getColorTitle()
 * @method Justselling_Configurator_Model_Optionfontcolor setColorTitle(string $value)
 * @method string getColorCode()
 * @method Justselling_Configurator_Model_Optionfontcolor setColorCode(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionfontcolor setOptionId(int $value)
 */
class Justselling_Configurator_Model_Optionfontcolor extends Mage_Core_Model_Abstract
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionfontcolor');
	}
	
	public function getOptionFontColors($optionid)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('option_id',$optionid);		
		return $collection;
	}
	public function getColorByCode($color_code)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('color_code',$color_code);	
		return $collection->getFirstItem();
	}
}