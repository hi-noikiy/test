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
ALTER TABLE {$this->getTable('configurator/option')} ADD `font_size` INT DEFAULT NULL;
ALTER TABLE {$this->getTable('configurator/option')} ADD `font_angle` INT DEFAULT NULL;
ALTER TABLE {$this->getTable('configurator/option')} ADD `font_color` VARCHAR(10) DEFAULT NULL;
");
$installer->endSetup();