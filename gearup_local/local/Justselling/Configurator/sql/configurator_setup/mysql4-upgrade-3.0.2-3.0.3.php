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
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('configurator/rules')};
    CREATE TABLE {$this->getTable('configurator/rules')} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `template_id` int(11) NOT NULL,
            `scope` varchar(20) NOT NULL,
            `appliedfor` varchar(20) NOT NULL,
            `operatorvalue` varchar(10) NOT NULL,
            `value` int(11) NOT NULL,
            `message` varchar(300),
            `when_executed` varchar(20),
            PRIMARY KEY (`id`)
    );
");
$installer->endSetup();
