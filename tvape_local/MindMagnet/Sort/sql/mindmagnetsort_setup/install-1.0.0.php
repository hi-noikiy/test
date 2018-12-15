<?php
$installer = $this;

$installer->startSetup();

try {
    // Add an extra column to the catalog_eav_attribute-table:
    $this->getConnection()->addColumn(
        $this->getTable('catalog/eav_attribute'),
        'mmsort_image',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'comment'   => 'Image'
        )
    );
    $this->getConnection()->addColumn(
        $this->getTable('eav/attribute'),
        'mmsort_description',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'comment'   => 'Description'
        )
    );

    // Add new category attributes
    $entityTypeId     = $installer->getEntityTypeId('catalog_category');
    $attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

    //Add group to entity & set
    $installer->addAttributeGroup('catalog_category', $attributeSetId, 'Sort Options');

    $installer->addAttribute('catalog_category', 'mmsort_enabled',  array(
        'type'     => 'int',
        'label'    => 'Enable sort',
        'input'    => 'select',
        'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => 1,
        'group'             => 'Sort Options',
        'source'            => 'eav/entity_attribute_source_boolean'
    ));

    $installer->addAttribute('catalog_category', 'mmsort_attributes',  array(
        'type'     => 'text',
        'label'    => 'Attributes to sort by',
        'input'    => 'text',
        'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => 'price,value,popularity',
        'group'             => 'Sort Options'
    ));

    $attributeId = $installer->getAttributeId($entityTypeId, 'mmsort_attributes');
    $installer->run("
        INSERT INTO `{$installer->getTable('catalog_category_entity_text')}`
        (`entity_type_id`, `attribute_id`, `entity_id`, `value`)
            SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, 'price,value,popularity'
                FROM `{$installer->getTable('catalog_category_entity')}`;
        ");

    $attributeId = $installer->getAttributeId($entityTypeId, 'mmsort_enabled');
    $installer->run("
        INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
        (`entity_type_id`, `attribute_id`, `entity_id`, `value`)
            SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
                FROM `{$installer->getTable('catalog_category_entity')}`;
        ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();