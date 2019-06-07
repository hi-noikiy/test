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
ALTER TABLE {$this->getTable('configurator/option')} DROP FOREIGN KEY `configurator_option_ibfk_2`;
ALTER TABLE {$this->getTable('configurator/option')} ADD FOREIGN KEY (`parent_id`) REFERENCES {$this->getTable('configurator/option')}(`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
");

$installer->endSetup();