<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_System_Config_Client extends Varien_Object
{
    static public function toOptionArray()
    {
        return array(
            array('value' => 'enterprise', 'label'=>Mage::helper('eshopsync')->__('Enterprise')),
            array('value' => 'partner', 'label'=>Mage::helper('eshopsync')->__('Partner')),
        );
    }
}
