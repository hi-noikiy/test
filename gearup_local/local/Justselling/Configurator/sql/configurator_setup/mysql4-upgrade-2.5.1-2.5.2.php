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

		--
		-- Tabellenstruktur für Tabelle `configurator_optionblacklist`
		--

		CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_blacklist')} (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`option_id` int(11) NOT NULL,
		`operator` varchar(10) DEFAULT NULL,
		`value` varchar(128) DEFAULT NULL,
		`child_option_value_id` int(11) NOT NULL,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		
");
$installer->endSetup();
		
?>