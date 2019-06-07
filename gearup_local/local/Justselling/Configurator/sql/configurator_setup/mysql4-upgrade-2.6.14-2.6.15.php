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

	ALTER TABLE {$this->getTable('configurator/postpricerule')} ADD FOREIGN KEY ( `template_id` )
		REFERENCES {$this->getTable('configurator/template')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


");
$installer->endSetup();