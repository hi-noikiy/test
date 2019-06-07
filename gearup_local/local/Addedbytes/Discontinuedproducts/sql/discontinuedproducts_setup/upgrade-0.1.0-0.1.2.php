<?php

$installer = $this;
$installer->startSetup();

$groupName = 'Discontinued Products';

$installer->addAttribute(
    'catalog_product',
    'discontinued_alternatives',
    array(
        'label'                         => 'Discontinued Alternative Products',
        'type'                          => 'varchar',
        'required'                      => false,
        'input'                         => 'text',
        'source'                        => '',
        'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'default'                       => 'none',
        'visible_on_front'              => 1,
        'position'                      => 1,
        'group'                         => $groupName,
        'sort_order'                    => 22,
        'note'                          => 'If you wish to define specific products to be displayed, instead of using a category as recommended, please list their SKUs here, separated by a comma. If you define products here, they will be shown before any products from the "Discontinued Category".'
    )
);

// Move these attributes to their own group
foreach ($installer->getAllAttributeSetIds('catalog_product') as $setId) {
    $groupId = $installer->getAttributeGroupId($installer->getEntityTypeId('catalog_product'), $setId, $groupName);
    $installer->addAttributeToGroup('catalog_product', $setId, $groupId, 'discontinued_product', 21);
    $installer->addAttributeToGroup('catalog_product', $setId, $groupId, 'discontinued_category', 23);
}

// Add more information to category item
$installer->updateAttribute('catalog_product', 'discontinued_category', 'note', 'When a product is discontinued, and no longer available to buy, then available products from the category you select here will be displayed at the top of the page as alternatives.');

$installer->endSetup();
