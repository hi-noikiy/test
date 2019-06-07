<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'sds_red',
    array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'SDS RED',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => true,
        'unique' => false,
        'is_configurable' => false,
    )
);

$eavModel    = Mage::getModel('eav/entity_setup','core_setup');
$attributeId = $eavModel->getAttributeId('catalog_product', 'sds_red');

foreach($eavModel->getAllAttributeSetIds('catalog_product') as $id) {
    $attributeGroupId = $eavModel->getAttributeGroupId('catalog_product', $id, 'Low Stock Threshold');
    $eavModel->addAttributeToSet('catalog_product', $id, $attributeGroupId, $attributeId);
}

$installer->endSetup();


