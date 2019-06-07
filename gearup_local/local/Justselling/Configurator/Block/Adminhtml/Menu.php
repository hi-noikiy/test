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
 * @copyright   Copyright © 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu
{
	
	public function getMenuArray() {
		$parentArr = parent::getMenuArray();
		
		if (Mage::getSingleton('core/session')->getEdition()=="B" || Mage::getSingleton('core/session')->getEdition()=="P")
			unset($parentArr['configurator']['children']["fontmanagement"]);
		if (Mage::getSingleton('core/session')->getEdition()=="B")
			unset($parentArr['configurator']['children']["singleproductjobs"]);
				
		return $parentArr;
	}
}