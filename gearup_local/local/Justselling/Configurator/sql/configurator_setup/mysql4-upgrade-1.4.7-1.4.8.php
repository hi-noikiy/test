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

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_group')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `sort_order` tinyint NOT NULL DEFAULT 0,
  `group_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_configurator_option_group_configurator_template` (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('configurator/option_group')}
  ADD CONSTRAINT `configurator_option_group_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES {$this->getTable('configurator/template')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


");

$installer->endSetup();