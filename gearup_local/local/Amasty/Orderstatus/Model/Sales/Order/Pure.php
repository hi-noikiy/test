<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderstatus
 */
$autoloader = Varien_Autoload::instance();
if (Mage::helper('core')->isModuleEnabled('Amasty_Orderattr')) {
    $autoloader->autoload('Amasty_Orderstatus_Model_Sales_Order_Orderattr');
} elseif (Mage::helper('core')->isModuleEnabled('Amasty_Orderattach')) {
    $autoloader->autoload('Amasty_Orderstatus_Model_Sales_Order_Orderattach');
} elseif (Mage::helper('core')->isModuleEnabled('Amasty_Deliverydate')) {
    $autoloader->autoload('Amasty_Orderstatus_Model_Sales_Order_Deliverydate');
} elseif (Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')) {
    $autoloader->autoload('Amasty_Orderstatus_Model_Sales_Order_AdjustWare');
} else {
    class Amasty_Orderstatus_Model_Sales_Order_Pure extends Mage_Sales_Model_Order {}
}