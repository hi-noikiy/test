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
 * @method Justselling_Configurator_Model_Optionfontconfiguration setId(int $value)
 * @method int getChooseFontColor()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseFontColor(int $value)
 * @method int getChooseFontSize()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseFontSize(int $value)
 * @method int getChooseFontPos()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseFontPos(int $value)
 * @method int getMaxFontSize()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setMaxFontSize(int $value)
 * @method int getMaxFontAngle()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setMaxFontAngle(int $value)
 * @method int getChooseFont()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseFont(int $value)
 * @method int getChooseTextAlignment()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseTextAlignment(int $value)
 * @method int getMinFontSize()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setMinFontSize(int $value)
 * @method int getMinFontAngle()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setMinFontAngle(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setOptionId(int $value)
 * @method int getChooseFontAngle()
 * @method Justselling_Configurator_Model_Optionfontconfiguration setChooseFontAngle(int $value)
 */
class Justselling_Configurator_Model_Optionfontconfiguration extends Mage_Core_Model_Abstract
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionfontconfiguration');
	}
	
	public function getOptionFontConfiguration($optionid)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('option_id',$optionid);		
		return $collection;
	}
}