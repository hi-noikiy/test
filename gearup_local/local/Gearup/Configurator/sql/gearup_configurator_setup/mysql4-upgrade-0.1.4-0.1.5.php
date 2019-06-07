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
$installer->run("CREATE TABLE {$this->getTable('gearup_configurator_events')} (
  `event_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
ALTER TABLE {$this->getTable('gearup_configurator_events')}
  ADD PRIMARY KEY (`event_name`);
");
$installer->endSetup();