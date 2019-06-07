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

		ALTER TABLE  {$this->getTable('configurator/template')} ADD  `design` VARCHAR(4096) DEFAULT 'a:1:{s:16:\"more_info_design\";s:7:\"fade_in\";}';
		UPDATE {$this->getTable('configurator/template')} SET `design` = 'a:1:{s:16:\"more_info_design\";s:7:\"fade_in\";}';

		");
$installer->endSetup();

?>
