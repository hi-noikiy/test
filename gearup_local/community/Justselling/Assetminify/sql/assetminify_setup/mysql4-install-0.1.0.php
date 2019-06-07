<?php

$installer = $this;
$installer->startSetup();
$configData = Mage::getModel('core/config_data')->load('assetminify/settings/enabled', 'path');
$configData->setPath('assetminify/settings/enabled')
    ->setValue(0)
    ->save();
$installer->endSetup();
