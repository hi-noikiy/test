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
* @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
* @license     http://www.justselling.de/lizenz
*/

$installer = $this;

$installer->startSetup();

$installer->run("



CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/upload')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `fileuploader_product_id` int(11) NOT NULL,
  `fileuploader_template_id` int(11) NOT NULL,
  `status` varchar(45) DEFAULT NULL,
  `file` varchar(512) DEFAULT NULL,
  `min_dpix` int(11) ,
  `min_dpiy` int(11) ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		
ALTER TABLE {$this->getTable('configurator/upload')} ADD `quote_id` INT(11);
ALTER TABLE {$this->getTable('configurator/upload')} ADD `session_id` VARCHAR(512);
ALTER TABLE {$this->getTable('configurator/upload')} ADD `quote_item_id` INT(11);
ALTER TABLE {$this->getTable('configurator/upload')} ADD `order_item_id` INT(11);
ALTER TABLE {$this->getTable('configurator/upload')} ADD `option_id` INT(11);

");
$installer->endSetup();