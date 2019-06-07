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
    DROP TABLE IF EXISTS {$this->getTable('configurator/uploadstatus')};
    CREATE TABLE {$this->getTable('configurator/uploadstatus')} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cachekey` varchar(100) NOT NULL,
            `status` varchar(20) NOT NULL,
            `message` varchar(200) NOT NULL,
            `iterationcount` int(5) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
    );
");
$installer->endSetup();