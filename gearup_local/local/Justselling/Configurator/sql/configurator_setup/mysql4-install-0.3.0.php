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
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
$installer = $this;

$installer->startSetup();

$coreStoreTable = Mage::getSingleton("core/resource")->getTableName('core_store');
$catalogProductOptionTable = Mage::getSingleton("core/resource")->getTableName('catalog_product_option');

$installer->run("

--
-- Tabellenstruktur für Tabelle `configurator_child_option_status`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/child_option_status')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `child_option_id` int(11) NOT NULL,
  `status` varchar(45) DEFAULT NULL,
  `is_combi` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_configurator_option_has_configurator_option_configurator_o2` (`child_option_id`),
  KEY `fk_configurator_option_has_configurator_option_configurator_o1` (`option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_option`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `is_require` tinyint(4) NOT NULL,
  `max_characters` int(11) NOT NULL,
  `min_value` int(11) NOT NULL,
  `max_value` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `price` float NOT NULL,
  `operator` varchar(10) DEFAULT NULL,
  `alt_title` varchar(255) NOT NULL,
  `operator_value_price` varchar(10) NOT NULL DEFAULT 'none',
  `decimal_place` tinyint(4) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  KEY `fk_conf_option_conf_template1` (`template_id`),
  KEY `fk_conf_option_conf_option1` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_option_value`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_value')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `price` float NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `thumbnail_size_x` int(11) DEFAULT NULL,
  `thumbnail_size_y` int(11) DEFAULT NULL,
  `thumbnail_alt` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_size_x` int(11) DEFAULT NULL,
  `image_size_y` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_conf_option_value_conf_option1` (`option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_option_value_blacklist`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_value_blacklist')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_value_id` int(11) NOT NULL,
  `child_option_value_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_configurator_option_value_has_configurator_option_value_co2` (`child_option_value_id`),
  KEY `option_value_id` (`option_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_option_value_child_option_status`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_value_child_option_status')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_value_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `price` float DEFAULT '0',
  `min_value` int(11) DEFAULT NULL,
  `max_value` int(11) DEFAULT NULL,
  `is_require` tinyint(4) DEFAULT '0',
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_configurator_option_value_has_configurator_option_configur2` (`option_id`),
  KEY `fk_configurator_option_value_has_configurator_option_configur1` (`option_value_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_pricelist`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/pricelist')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `operator` varchar(10) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_configurator_pricelist_configurator_option1` (`option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_pricelist_value`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/pricelist_value')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_value_id` int(11) NOT NULL,
  `operator` varchar(10) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `operator_value_price` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_configurator_pricelist_value_configurator_option_value1` (`option_value_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_product_option_template`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/product_option_template')} (
  `catalog_product_option_option_id` int(10) unsigned NOT NULL,
  `conf_template_id` int(11) NOT NULL,
  `store_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`catalog_product_option_option_id`,`conf_template_id`,`store_id`),
  KEY `fk_catalog_product_option_has_conf_template_conf_template1` (`conf_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `configurator_template`
--

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/template')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");
$installer->endSetup();

$installer->startSetup();
$installer->run("
--
-- Constraints der exportierten Tabellen
--


--
-- Constraints der Tabelle `configurator_child_option_status`
--
ALTER TABLE {$this->getTable('configurator/child_option_status')}
  ADD CONSTRAINT `fk_configurator_option_has_configurator_option_configurator_o1` FOREIGN KEY (`option_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_configurator_option_has_configurator_option_configurator_o2` FOREIGN KEY (`child_option_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_option`
--
ALTER TABLE {$this->getTable('configurator/option')}
  ADD CONSTRAINT `configurator_option_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES {$this->getTable('configurator/template')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `configurator_option_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_option_value`
--
ALTER TABLE {$this->getTable('configurator/option_value')}
  ADD CONSTRAINT `configurator_option_value_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_option_value_blacklist`
--
ALTER TABLE {$this->getTable('configurator/option_value_blacklist')}
  ADD CONSTRAINT `configurator_option_value_blacklist_ibfk_1` FOREIGN KEY (`option_value_id`) REFERENCES {$this->getTable('configurator/option_value')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `configurator_option_value_blacklist_ibfk_2` FOREIGN KEY (`child_option_value_id`) REFERENCES {$this->getTable('configurator/option_value')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_option_value_child_option_status`
--
ALTER TABLE {$this->getTable('configurator/option_value_child_option_status')}
  ADD CONSTRAINT `fk_configurator_option_value_has_configurator_option_configur1` FOREIGN KEY (`option_value_id`) REFERENCES {$this->getTable('configurator/option_value')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_configurator_option_value_has_configurator_option_configur2` FOREIGN KEY (`option_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_pricelist`
--
ALTER TABLE  {$this->getTable('configurator/pricelist')}
  ADD CONSTRAINT `fk_configurator_pricelist_configurator_option1` FOREIGN KEY (`option_id`) REFERENCES {$this->getTable('configurator/option')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_pricelist_value`
--
ALTER TABLE {$this->getTable('configurator/pricelist_value')}
  ADD CONSTRAINT `fk_configurator_pricelist_value_configurator_option_value1` FOREIGN KEY (`option_value_id`) REFERENCES {$this->getTable('configurator/option_value')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `configurator_product_option_template`
--
ALTER TABLE {$this->getTable('configurator/product_option_template')}
  ADD CONSTRAINT `configurator_product_option_template_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES {$coreStoreTable} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_catalog_product_option_has_conf_template_catalog_product_o1` FOREIGN KEY (`catalog_product_option_option_id`) REFERENCES {$catalogProductOptionTable} (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_catalog_product_option_has_conf_template_conf_template1` FOREIGN KEY (`conf_template_id`) REFERENCES {$this->getTable('configurator/template')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

");
$installer->endSetup();