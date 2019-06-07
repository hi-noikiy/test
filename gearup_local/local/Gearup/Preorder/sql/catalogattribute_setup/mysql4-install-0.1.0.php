<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->addAttribute("catalog_product", "preorderdate",  array(
    "type"     => "datetime",
    "backend"  => "eav/entity_attribute_backend_datetime",
    "frontend" => "",
    "label"    => "Pre Order Date",
    "input"    => "date",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => true,
    "unique"     => false,
    "note"       => ""
	));
$installer->endSetup();

$installer->addAttributeToSet(
    'catalog_product', 'Default', 'Inventory', 'preorderdate'
);	 