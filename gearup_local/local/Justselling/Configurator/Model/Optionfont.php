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
 * @method Justselling_Configurator_Model_Optionfont setId(int $value)
 * @method int getFontId()
 * @method Justselling_Configurator_Model_Optionfont setFontId(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionfont setOptionId(int $value)
 */
class Justselling_Configurator_Model_Optionfont extends Mage_Core_Model_Abstract
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionfont');
	}
	
	public function getOptionFonts($optionid)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('option_id',$optionid);		
		return $collection;
	}
}