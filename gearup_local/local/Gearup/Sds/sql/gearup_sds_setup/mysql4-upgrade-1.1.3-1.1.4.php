<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'dxbsp',
    array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'DXBSP',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
    )
);

$eavModel    = Mage::getModel('eav/entity_setup','core_setup');
$attributeId = $eavModel->getAttributeId('catalog_product', 'dxbsp');

foreach($eavModel->getAllAttributeSetIds('catalog_product') as $id) {
    $attributeGroupId = $eavModel->getAttributeGroupId('catalog_product', $id, 'General');
    $eavModel->addAttributeToSet('catalog_product', $id, $attributeGroupId, $attributeId);
}

$installer->endSetup();


