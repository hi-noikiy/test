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
 * @method Justselling_Configurator_Model_Font setId(int $value)
 * @method string getTitle()
 * @method Justselling_Configurator_Model_Font setTitle(string $value)
 * @method int getOrder()
 * @method Justselling_Configurator_Model_Font setOrder(int $value)
 * @method string getFontFile()
 * @method Justselling_Configurator_Model_Font setFontFile(string $value)
 * @method int getFontType()
 * @method Justselling_Configurator_Model_Font setFontType(int $value)
 */

class Justselling_Configurator_Model_Font extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/font');
	}
	
	public function toFontArray()
	{
		$collection = Mage::getModel("configurator/font")->getCollection();
		
		$result = array( null => "-- ".Mage::helper('configurator')->__("None")." --" );
		
		foreach($collection->getItems() as $item) {
			$result[$item->id] = $item->title." ".$this->getTypeString($item->font_type);
		}
		
		return $result;
	}
	
	public function getTypeString($type) {
        if ($type) {
            $typestring = array(0=>'Regular' ,1=>'Italic',2=>'Bold',3=>'Bold-Italic');
            return $typestring[$type];
        }
        return false;
	}
}