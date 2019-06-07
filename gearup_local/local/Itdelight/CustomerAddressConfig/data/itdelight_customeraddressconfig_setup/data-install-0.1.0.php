<?php
/**
 * Created by PhpStorm.
 * User: nastuho
 * Date: 15.11.18
 * Time: 19:07
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

Mage::getSingleton('eav/config')->getAttribute('customer_address', 'city')->setIsRequired(1)->save();
Mage::getSingleton('eav/config')->getAttribute('customer_address', 'street')->setIsRequired(1)->save();

$installer->endSetup();