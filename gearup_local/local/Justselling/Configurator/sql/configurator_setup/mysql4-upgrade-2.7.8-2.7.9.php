

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
  ALTER TABLE {$this->getTable('configurator/option_font_configuration')} CHANGE `choose_text_alignement` `choose_text_alignment` INT DEFAULT NULL;
  ALTER TABLE {$this->getTable('configurator/option')} CHANGE `text_alignement` `text_alignment` INT(11) DEFAULT 1;
");
$installer->endSetup();
