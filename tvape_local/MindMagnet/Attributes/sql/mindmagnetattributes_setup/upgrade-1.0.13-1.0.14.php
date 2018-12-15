<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$groupName = 'In the box';

$entityTypeId = $installer->getEntityTypeId('catalog_product');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

$attributeId = $installer->getAttributeId($entityTypeId, 'in_the_box_extra');
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);


$installer->updateAttribute('catalog_product', 'in_the_box_extra', 'label' ,'In the box - Images');
$installer->updateAttribute('catalog_product', 'in_the_box_extra', 'note' ,'Use , separator for images. Example: image1, image2, image3');


$installer->endSetup();