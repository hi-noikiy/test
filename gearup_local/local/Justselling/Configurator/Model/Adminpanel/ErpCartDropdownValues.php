<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license      http://www.justselling.de/lizenz
 * @author	   Daniel M�ller
 */

class Justselling_Configurator_Model_Adminpanel_ErpCartDropdownValues
{
	public function toOptionArray()
	{
		return array(
				array(
						'value' => '0',
						'label' => 'No'
				),
				array(
						'value' => '1',
						'label' => Mage::helper('configurator')->__('Yes, if at least one option left')
				),
				array(
						'value' => '2',
						'label' => Mage::helper('configurator')->__('Yes')
				)
		);
	}
}

?>