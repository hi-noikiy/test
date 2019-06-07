<?php
$installer = $this;
$installer->startSetup();

$installer->updateAttribute('catalog_category','category_deal','label', 'SDS');

$installer->endSetup();