<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Customer attribute
$entityTypeId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute(
    "customer", "admitad_uid", array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Admitad Uid",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default"  => "",
    "frontend" => "",
    "unique"   => false,
    "note"     => "Admitad Uid",
    )
);

$setup->addAttribute(
    "customer", "admitad_uid_lifetime", array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Admitad Uid Lifetime",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default"  => "",
    "frontend" => "",
    "unique"   => false,
    "note"     => "Admitad Uid Lifetime",
    )
);

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'admitad_uid'
);

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'admitad_uid_lifetime'
);

$usedInForms = array();
$usedInForms[] = "adminhtml_customer";

/** @var Mage_Eav_Model_Config $attribute */
$attribute = Mage::getSingleton("eav/config");
$admitadUidAttribute = $attribute->getAttribute("customer", "admitad_uid");

$admitadUidAttribute->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 0)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$admitadUidAttribute->save();

$admitadUidLifetimeAttribute = $attribute->getAttribute("customer", "admitad_uid_lifetime");

$admitadUidLifetimeAttribute->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 0)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$admitadUidLifetimeAttribute->save();

$installer->endSetup();