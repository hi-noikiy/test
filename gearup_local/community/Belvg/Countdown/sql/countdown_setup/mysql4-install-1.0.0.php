<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('belvg_countdown')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `entity_id` int(11) unsigned NOT NULL,
  `entity_type` varchar(10) NOT NULL,
  `entity_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `expire_datetime_off` datetime NOT NULL default '0000-00-00 00:00:00',
  `expire_datetime_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `countdown_off` tinyint(1) unsigned NOT NULL default '0',
  `countdown_sub` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

");

$installer->endSetup();

