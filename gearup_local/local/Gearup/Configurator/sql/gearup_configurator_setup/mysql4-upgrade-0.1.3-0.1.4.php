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
$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator_order_item')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) UNSIGNED NOT NULL,
  `parent_item_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `sku` text NOT NULL,
  `title` text NOT NULL,
  `price` double,
  `sds` BOOLEAN NOT NULL DEFAULT FALSE,
  `dxbs` BOOLEAN NOT NULL DEFAULT FALSE,
  `part_no` text,
  `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_con_order_id FOREIGN KEY (order_id)
		REFERENCES sales_flat_order(entity_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
  CONSTRAINT fk_con_item_id FOREIGN KEY (parent_item_id)
		REFERENCES sales_flat_order_item(item_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");
$installer->endSetup();