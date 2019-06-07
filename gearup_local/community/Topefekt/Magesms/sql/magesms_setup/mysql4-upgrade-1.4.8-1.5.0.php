<?php
/**
 * Mage SMS - SMS notification & SMS marketing
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the BSD 3-Clause License
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/BSD-3-Clause
 *
 * @category    TOPefekt
 * @package     TOPefekt_Magesms
 * @copyright   Copyright (c) 2012-2017 TOPefekt s.r.o. (http://www.mage-sms.com)
 * @license     http://opensource.org/licenses/BSD-3-Clause
 */
$iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->getConnection() ->addColumn($iddb18dc4afa6663cf07a52c741943ff87cbe3896->getTable('magesms_hooks'),'system', 'BOOLEAN NOT NULL'); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->getConnection() ->dropColumn($iddb18dc4afa6663cf07a52c741943ff87cbe3896->getTable('magesms_hooks'), 'status'); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('magesms_birthdaymessages_template')}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_time` time NOT NULL,
  `delay` tinyint(4) NOT NULL DEFAULT '0',
  `smstext` text NOT NULL,
  `active` tinyint(3) NOT NULL,
  `mutation` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"); if (version_compare(Mage::getVersion(), '1.6', '<')) { include_once dirname(__FILE__).'/../../data/magesms_setup/data-upgrade-1.4.8-1.5.0.php'; } $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 