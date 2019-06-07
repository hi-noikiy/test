<?php

$installer = $this;
$installer->startSetup();

$categoryEntityTypeId = $this->getEntityTypeId('catalog_category');
$installer->updateAttribute($categoryEntityTypeId,'category_deal','frontend_label', 'SDS');

$installer->endSetup();


