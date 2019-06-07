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
		-- Tabellenstruktur für Tabelle `configurator_option_matrixvalue`
		--

		CREATE TABLE IF NOT EXISTS {$this->getTable('configurator/option_matrix')} (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`option_id` int(11) NOT NULL,
		`matrix` varchar(64000) DEFAULT NULL,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

		ALTER TABLE  {$this->getTable('configurator/option')} ADD  `matrix_dimension_x` VARCHAR(256) DEFAULT NULL;
		ALTER TABLE  {$this->getTable('configurator/option')} ADD  `matrix_operator_x` VARCHAR(32)  DEFAULT NULL;
		ALTER TABLE  {$this->getTable('configurator/option')} ADD  `matrix_dimension_y` VARCHAR(256)  DEFAULT NULL;
		ALTER TABLE  {$this->getTable('configurator/option')} ADD  `matrix_operator_y` VARCHAR(32)  DEFAULT NULL;
		ALTER TABLE  {$this->getTable('configurator/option')} ADD  `matrix_csv_delimiter` VARCHAR(1)  DEFAULT ',';
		
		");
$installer->endSetup();

?>