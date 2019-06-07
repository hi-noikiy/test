<?php
$installer = $this;

$installer->startSetup();

$installer->run("
                    ALTER TABLE {$this->getTable('prolabels/label')}
                        ADD COLUMN `product_image_popuptext` TEXT NULL AFTER `product_image_text`;
                ");

$installer->endSetup();