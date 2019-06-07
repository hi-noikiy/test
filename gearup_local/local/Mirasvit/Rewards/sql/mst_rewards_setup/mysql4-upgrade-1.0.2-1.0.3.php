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
if ($version == '1.0.3') {
    return;
} elseif ($version != '1.0.2') {
    die('Please, run migration Rewards 1.0.2');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('rewards/transaction')}` ADD COLUMN `amount_used` INT(11) after amount;

update `{$this->getTable('rewards/transaction')}` set is_expiration_email_sent=0 where is_expiration_email_sent is null;
alter table `{$this->getTable('rewards/transaction')}` MODIFY is_expiration_email_sent TINYINT(1) NOT NULL DEFAULT 0;
";
$installer->run($sql);

/*                                    **/

$installer->endSetup();
