<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('prolabels/label')}
    CHANGE `product_image_text` `product_image_text` TEXT NULL;
");

$installer->endSetup();