<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Shopby/active')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Shopby');
} else {
    class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends Hatimeria_Elastic_Block_Catalog_Product_List_Toolbar {}
}
