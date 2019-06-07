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
    DROP TABLE IF EXISTS {$this->getTable('configurator/singleproduct_job')};
    CREATE TABLE {$this->getTable('configurator/singleproduct_job')} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `job_id` int(11) NOT NULL DEFAULT 0,
			`template_id` int(11) NOT NULL DEFAULT 0,
            `config` text,
            `status` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
    );
        
");
$installer->endSetup();