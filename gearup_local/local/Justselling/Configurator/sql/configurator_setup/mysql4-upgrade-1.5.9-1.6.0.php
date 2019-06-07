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
CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_font_color')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `color_code` varchar(32) NOT NULL,
  `color_title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_font_configuration')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `min_font_size` int(11) NOT NULL,
  `max_font_size` int(11) NOT NULL,
  `min_font_angle` int(11) NOT NULL,
  `max_font_angle` int(11) NOT NULL,
  `choose_font` int(11) NOT NULL,
  `choose_font_size` int(11) NOT NULL,
  `choose_font_angle` int(11) NOT NULL,
  `choose_font_color` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('configurator/option')} ADD `font_pos_x` INT DEFAULT NULL;
ALTER TABLE {$this->getTable('configurator/option')} ADD `font_pos_y` INT DEFAULT NULL;
");

$installer->endSetup();