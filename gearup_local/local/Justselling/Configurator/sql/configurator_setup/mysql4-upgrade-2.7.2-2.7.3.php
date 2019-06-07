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
    DROP TABLE IF EXISTS {$this->getTable('configurator/jobprocessor_job')};
    CREATE TABLE {$this->getTable('configurator/jobprocessor_job')} (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `status` varchar(20) NOT NULL,
            `model` varchar(100) NOT NULL,
            `params` text NULL DEFAULT NULL,
            `params_public` text NULL DEFAULT NULL,
            `started_at` datetime NULL DEFAULT NULL,
            `processes_active` smallint UNSIGNED DEFAULT 0,
            `processes_max` smallint UNSIGNED DEFAULT 1,
            `count_total` int UNSIGNED NOT NULL DEFAULT 0,
            `count_done` int UNSIGNED NOT NULL DEFAULT 0,
            `count_problems` int UNSIGNED NOT NULL DEFAULT 0,
            `paused_at` datetime NULL DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `created_by` varchar(50) NOT NULL,
            `canceled_at` datetime NULL DEFAULT NULL,
            `canceled_by` varchar(50) NULL DEFAULT NULL,
            `canceled_why` varchar(255) NULL DEFAULT NULL,
            `finished_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
    );
        
");
$installer->endSetup();