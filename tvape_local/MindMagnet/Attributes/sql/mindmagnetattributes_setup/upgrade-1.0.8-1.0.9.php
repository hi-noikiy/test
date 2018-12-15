<?php
$installer = $this;

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'in_the_press', 'visible_on_front' , 1);
$installer->updateAttribute('catalog_product', 'how_to_videos', 'visible_on_front' , 1);
$installer->updateAttribute('catalog_product', 'in_the_box_extra', 'visible_on_front' , 1);
$installer->updateAttribute('catalog_product', 'webrotate_path', 'visible_on_front' , 1);

$installer->endSetup();