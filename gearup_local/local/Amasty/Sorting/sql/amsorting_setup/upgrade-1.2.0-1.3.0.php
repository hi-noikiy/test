<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */
$installer = $this;
$installer->startSetup();
$table = $installer->getTable('amsorting/toprated');

$installer->run("
CREATE TABLE {$table} (
  `id`          int(10)     unsigned NOT NULL,
  `store_id`    smallint(5) unsigned NOT NULL,
  `toprated`    int(10)     unsigned NOT NULL,
  KEY `toprated_idx` (`id`, `store_id`)
) ENGINE=MyISAM;");

$installer->endSetup();