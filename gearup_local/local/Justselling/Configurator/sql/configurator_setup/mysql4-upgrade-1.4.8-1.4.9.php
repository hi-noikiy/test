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

ALTER TABLE {$this->getTable('configurator/option')} ADD `option_group_id` int(11);

ALTER TABLE {$this->getTable('configurator/option')} ADD INDEX `fk_configurator_option_group_configurator_option` (option_group_id);
ALTER TABLE {$this->getTable('configurator/option')}
  ADD CONSTRAINT `configurator_option_ibfk_5` FOREIGN KEY (`option_group_id`) REFERENCES {$this->getTable('configurator/option_group')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();