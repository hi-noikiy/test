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
 * @method Justselling_Configurator_Model_Optionfontposition setId(int $value)
 * @method string getPosTitle()
 * @method Justselling_Configurator_Model_Optionfontposition setPosTitle(string $value)
 * @method int getPosY()
 * @method Justselling_Configurator_Model_Optionfontposition setPosY(int $value)
 * @method int getPosX()
 * @method Justselling_Configurator_Model_Optionfontposition setPosX(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Optionfontposition setOptionId(int $value)
 */
class Justselling_Configurator_Model_Optionfontposition extends Mage_Core_Model_Abstract
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optionfontposition');
	}
	
	public function getOptionFontPositions($optionid)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('option_id',$optionid);		
		return $collection;
	}
	public function getPositionByXY($pos_xy)
	{
		$parts = preg_split("/-/",$pos_xy);
		$collection = $this->getCollection();	
		$collection->addFilter('pos_x',$parts[0]);	
		$collection->addFilter('pos_y',$parts[1]);
		return $collection->getFirstItem();
	}
}