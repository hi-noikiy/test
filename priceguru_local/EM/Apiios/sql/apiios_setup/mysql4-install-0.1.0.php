<?php

$installer = $this;
 
$installer->startSetup();

$attribute = Mage::getModel('catalog/resource_eav_attribute');
$att['attribute_code']	=	'featured_product';
$att['is_global']	=	0;
$att['frontend_input']	=	boolean;
$att['default_value_yesno']	=	0;
$att['is_unique']	=	0;
$att['is_required']	=	0;
$att['is_configurable']	=	0;
$att['is_searchable']	=	0;
$att['is_visible_in_advanced_search']	=	0;
$att['is_comparable']	=	0;
$att['is_used_for_promo_rules']	=	1;
$att['is_html_allowed_on_front']	=	1;
$att['is_visible_on_front']	=	0;
$att['used_in_product_listing']	=	1;
$att['used_for_sort_by']	=	0;
$att['frontend_label'][0]	=	'Featured Product';
$att['source_model']	=	'eav/entity_attribute_source_boolean';
$att['is_filterable']	=	0;
$att['is_filterable_in_search']	=	0;
$att['backend_type']	=	int;
$att['default_value']	=	0;
$att['entity_type_id']	=	Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
$att['is_user_defined']	=	1;
$featured	=	$attribute->setData($att)->save()->getId();
$installer->addAttributeToGroup('catalog_product','default','General',$featured);

$installer->endSetup();
