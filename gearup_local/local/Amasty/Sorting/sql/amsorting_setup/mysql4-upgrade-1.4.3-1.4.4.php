<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


if (Mage::helper('core')->isModuleEnabled('Mana_Seo')) {
    $additionalOrdersPath = 'mana/seo/additional_toolbar_orders';
    $additionalToolbars = Mage::getStoreConfig($additionalOrdersPath) ? Mage::getStoreConfig($additionalOrdersPath) : '';
    $additionalToolbars .= implode(',', array_keys(Mage::helper('amsorting')->getMethodModels()));
    Mage::getConfig()->saveConfig(
        $additionalOrdersPath, $additionalToolbars
    );
}
