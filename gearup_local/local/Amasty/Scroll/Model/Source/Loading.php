<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */  
class Amasty_Scroll_Model_Source_Loading extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amscroll');
		return array(
			array('value' => 'none',   'label' => $hlp->__('None - module is disabled')),
			array('value' => 'auto',   'label' => $hlp->__('Automatic - on page scroll')),
			array('value' => 'button', 'label' => $hlp->__('Button - on button click')),
		);
	}
}