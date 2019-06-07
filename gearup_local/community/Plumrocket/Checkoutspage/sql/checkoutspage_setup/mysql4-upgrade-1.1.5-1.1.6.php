<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */



$installer = $this;

$setup = Mage::getModel('eav/entity_setup', 'core_setup');
$entityTypeId = $setup->getEntityTypeId('catalog_product');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

$setup->addAttribute('catalog_product', 'pr_next_order_coupon', array(
    'type'              => 'int',
    'label'             => 'Next Order Coupon',
    'input'             => 'select',
    'source'           	=> 'checkoutspage/system_config_nextordercoupon',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => 0,
    'required'          => 0,
    'user_defined'      => 0,
    'default'           => 0,
    'position'          => 250,
));

$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'pr_next_order_coupon', '260');
$setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'pr_next_order_coupon', '260');

try {
    $installer->run("ALTER TABLE `{$this->getTable('sales_flat_order')}` ADD  `pr_next_order_rule_id` int(11) NOT NULL");
} catch (Exception $e) { }

$installer->endSetup();