<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
$installer = $this;

$installer->startSetup();

$catalogProductEntityTable = Mage::getSingleton("core/resource")->getTableName('catalog_product_entity');

$installer->run("
ALTER TABLE  {$this->getTable('configurator/option')} ADD  `product_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL ,ADD INDEX (  `product_id` );
ALTER TABLE  {$this->getTable('configurator/option')} ADD FOREIGN KEY (  `product_id` ) REFERENCES  `{$catalogProductEntityTable}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE ;
");
$installer->endSetup();