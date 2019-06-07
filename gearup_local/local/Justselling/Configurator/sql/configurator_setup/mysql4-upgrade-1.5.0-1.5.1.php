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

$installer->run("

CREATE TABLE IF NOT EXISTS `configurator_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) NOT NULL,
  `activation` tinyint NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'not activated',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();