<?php
$installer = $this;
$installer->startSetup();
$ln_filter = $this->getTable('ln_filter');
$ln_option = $this->getTable('ln_option');

$installer->run("
DROP TABLE IF EXISTS `{$ln_filter}`;
CREATE TABLE `{$ln_filter}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `attribute_id` smallint(5) DEFAULT NULL,
  `attribute_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `display_as` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `store_id` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `{$ln_option}`;
CREATE TABLE `{$ln_option}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(10) DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

");
$installer->endSetup();