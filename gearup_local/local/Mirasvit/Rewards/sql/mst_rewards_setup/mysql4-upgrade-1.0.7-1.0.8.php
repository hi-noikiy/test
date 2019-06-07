<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_rewards');
if ($version == '1.0.8') {
    return;
} elseif ($version != '1.0.7') {
    die('Please, run migration Rewards 1.0.7');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = "
       DROP TABLE IF EXISTS `{$this->getTable('rewards/earning_rule_queue')}`;
    ";
    $installer->run($sql);
}
$sql = "

CREATE TABLE `{$this->getTable('rewards/earning_rule_queue')}` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Queue Id',
  `customer_id` int(11) NOT NULL COMMENT 'Customer Id',
  `website_id` int(11) NOT NULL COMMENT 'Website Id',
  `rule_type` varchar(50) NOT NULL COMMENT 'Rule Type',
  `rule_code` text NOT NULL COMMENT 'Rule Code',
  `is_processed` int(11) NOT NULL DEFAULT '0' COMMENT 'Website Id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Updated At',
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='rewards_earning_rule_queue';

";
$installer->run($sql);

$installer->endSetup();
