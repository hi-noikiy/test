<?php

$installer = $this;

$installer->startSetup();


$attributeId = $installer->getAttributeId('catalog_product','addons_popup_special_price');

if($attributeId){
    $installer->removeAttribute('catalog_product',$attributeId);
}

$installer->addAttribute('catalog_product', 'addons_popup_special_price', array(
        'input'                     =>  'price',
        'type'                      =>  'decimal',
        'user_defined'              =>  true,
        'label'                     =>  'Addons Popup Special Price',
        'global'                     =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'                   =>  true,
        'is_required'               =>  '0',
        'is_comparable'             =>  '0',
        'is_searchable'             =>  '0',
        'is_unique'                 =>  '0',
        'is_configurable'           =>  '0',
        'unique'                    =>  false,
        'used_in_product_listing'   =>  true,
        'required'                  =>  false,
        'searchable'                =>  false,
        'comparable'                =>  false,
        'visible_on_front'          =>  false,
    )
);
$installer->updateAttribute('catalog_product', 'addons_popup_special_price', 'apply_to', 'configurable,simple,grouped,bundle,virtual,downloadable');

$installer->addAttributeToSet('catalog_product','Default','Price','addons_popup_special_price');

$installer->addAttributeToGroup('catalog_product','Default','Prices','addons_popup_special_price');


$installer->endSetup();