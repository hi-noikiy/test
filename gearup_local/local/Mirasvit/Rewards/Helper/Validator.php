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



class Mirasvit_Rewards_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    /**
     * Tests for store integrity.
     * @return array
     */
    public function testMagentoCrc()
    {
        $filter = array(
            'app/code/core/Mage/Core',
            'app/code/core/Mage/Review',
            'js',
        );

        return Mage::helper('mstcore/validator_crc')->testMagentoCrc($filter);
    }

    /**
     * Tests for extension integrity
     * @return array
     */
    public function testMirasvitCrc()
    {
        $modules = array('Rewards');

        return Mage::helper('mstcore/validator_crc')->testMirasvitCrc($modules);
    }

    /**
     * Tests for ISpeed Cache
     * @return array
     */
    public function testISpeedCache()
    {
        $result = self::SUCCESS;
        $title = 'My_Ispeed';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('My_Ispeed')) {
            $result = self::INFO;
            $description[] = 'Extension My_Ispeed is installed. Please, go to the Configuration > Settings > ' .
                'I-Speed > General Configuration and add \'rewards\' to the list of Ignored URLs. ' .
                'Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }

    /**
     * Tests for Varnish Cache
     * @return array
     */
    public function testMgtVarnishCache()
    {
        $result = self::SUCCESS;
        $title = 'Mgt_Varnish';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('Mgt_Varnish')) {
            $result = self::INFO;
            $description[] = 'Extension Mgt_Varnish is installed. Please, go to the Configuration > Settings > '.
                'MGT-COMMERCE.COM > Varnish and add \'rewards\' to the list of Excluded Routes. Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }

    /**
     * Performs structural test of database with types check.
     *
     * @return array
     */
    public function testTableStructure()
    {
        $structure = array(
            'customer/entity' => array(),
            'core/store' => array(),
            'rewards/earning_rule' => array(
                'earning_rule_id' => 'int(11)',
                'name' => 'varchar(255)',
                'description' => 'text',
                'is_active' => 'int(11)',
                'active_from' => 'timestamp',
                'active_to' => 'timestamp',
                'type' => 'varchar(255)',
                'conditions_serialized' => 'text',
                'actions_serialized' => 'text',
                'earning_style' => 'varchar(255)',
                'earn_points' => 'int(11)',
                'monetary_step' => 'float',
                'qty_step' => 'int(11)',
                'points_limit' => 'int(11)',
                'behavior_trigger' => 'varchar(255)',
                'sort_order' => 'int(11)',
                'is_stop_processing' => 'tinyint(1)',
                'param1' => 'varchar(255)',
                'history_message' => 'text',
                'email_message' => 'text',
                'transfer_to_group' => 'smallint(5)',
            ),
            'rewards/earning_rule_customer_group' => array(
                'earning_rule_customer_group_id' => 'int(11)',
                'customer_group_id' => 'smallint(5) unsigned',
                'earning_rule_id' => 'int(11)',
            ),
            'rewards/earning_rule_product' => array(
                'earning_rule_product_id' => 'int(11)',
                'earning_rule_id' => 'int(11)',
                'er_product_id' => 'int(10) unsigned',
                'er_website_id' => 'smallint(5) unsigned',
                'er_customer_group_id' => 'smallint(5) unsigned',
            ),
            'rewards/earning_rule_website' => array(
                'earning_rule_website_id' => 'int(11)',
                'website_id' => 'smallint(5) unsigned',
                'earning_rule_id' => 'int(11)',
            ),
            'rewards/notification_rule' => array(
                'notification_rule_id' => 'int(11)',
                'name' => 'varchar(255)',
                'is_active' => 'int(11)',
                'active_from' => 'timestamp',
                'active_to' => 'timestamp',
                'conditions_serialized' => 'text',
                'actions_serialized' => 'text',
                'sort_order' => 'int(11)',
                'is_stop_processing' => 'tinyint(1)',
                'type' => 'varchar(255)',
                'message' => 'text',
            ),
            'rewards/notification_rule_customer_group' => array(
                'notification_rule_customer_group_id' => 'int(11)',
                'customer_group_id' => 'smallint(5) unsigned',
                'notification_rule_id' => 'int(11)',
            ),
            'rewards/notification_rule_website' => array(
                'notification_rule_website_id' => 'int(11)',
                'website_id' => 'smallint(5) unsigned',
                'notification_rule_id' => 'int(11)',
            ),
            'rewards/purchase' => array(
                'purchase_id' => 'int(11)',
                'quote_id' => 'int(11)',
                'order_id' => 'int(11)',
                'spend_points' => 'int(11)',
                'spend_amount' => 'double',
                'spend_min_points' => 'int(11)',
                'spend_max_points' => 'int(11)',
                'earn_points' => 'int(11)',
            ),
            'rewards/rate' => array(
                'rate_id' => 'int(11)',
                'rate_from' => 'decimal(6,2)',
                'rate_to' => 'decimal(6,2)',
                'direction' => 'varchar(255)',
                'website_id' => 'smallint(5) unsigned',
            ),
            'rewards/rate_customer_group' => array(
                'rate_customer_group_id' => 'int(11)',
                'rate_id' => 'int(11)',
                'customer_group_id' => 'smallint(5) unsigned',
            ),
            'rewards/referral' => array(
                'referral_id' => 'int(11)',
                'customer_id' => 'int(10) unsigned',
                'new_customer_id' => 'int(10) unsigned',
                'email' => 'varchar(255)',
                'name' => 'varchar(255)',
                'status' => 'varchar(255)',
                'store_id' => 'smallint(5) unsigned',
                'last_transaction_id' => 'int(11)',
                'points_amount' => 'int(11)',
                'created_at' => 'timestamp',
                'quote_id' => 'int(11)',
            ),
            'rewards/spending_rule' => array(
                'spending_rule_id' => 'int(11)',
                'name' => 'varchar(255)',
                'description' => 'text',
                'is_active' => 'int(11)',
                'active_from' => 'timestamp',
                'active_to' => 'timestamp',
                'type' => 'varchar(255)',
                'conditions_serialized' => 'text',
                'actions_serialized' => 'text',
                'spending_style' => 'varchar(255)',
                'spend_points' => 'int(11)',
                'monetary_step' => 'double',
                'spend_min_points' => 'varchar(255)',
                'spend_max_points' => 'varchar(255)',
                'sort_order' => 'int(11)',
                'is_stop_processing' => 'tinyint(1)',
            ),
            'rewards/spending_rule_customer_group' => array(
                'spending_rule_customer_group_id' => 'int(11)',
                'customer_group_id' => 'smallint(5) unsigned',
                'spending_rule_id' => 'int(11)',
            ),
            'rewards/spending_rule_website' => array(
                'spending_rule_website_id' => 'int(11)',
                'website_id' => 'smallint(5) unsigned',
                'spending_rule_id' => 'int(11)',
            ),
            'rewards/transaction' => array(
                'transaction_id' => 'int(11)',
                'customer_id' => 'int(10) unsigned',
                'amount' => 'int(11)',
                'amount_used' => 'int(11)',
                'comment' => 'text',
                'code' => 'varchar(255)',
                'is_expired' => 'tinyint(1)',
                'is_expiration_email_sent' => 'tinyint(1)',
                'expires_at' => 'timestamp',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ),
        );

        $dbCheck = $this->dbCheckTables(array_keys($structure));
        if ($dbCheck[0] != self::SUCCESS) {
            return array(self::FAILED, 'Database Structure', $dbCheck[2]);
        }

        $title = 'Database Structure';
        $description = array();
        foreach (array_keys($structure) as $tableName) {
            // Pass 0: If table record has empty array - check is not performed
            if (!count($structure[$tableName])) {
                continue;
            }

            // Pass 1: Check for missing fields (sqlResult can not be reset for some reason)
            foreach (array_keys($structure[$tableName]) as $field) {
                $exists = false;
                $sqlResult = $this->_dbConn()->query('DESCRIBE '.$this->_dbRes()->getTableName($tableName).';');
                foreach ($sqlResult as $sqlRow) {
                    if (!$exists && $sqlRow['Field'] == $field) {
                        $exists = true;
                    }
                }
                if (!$exists) {
                    $description[] = $this->_dbRes()->getTableName($tableName).' has missing field: '.$field;
                }
            }

            // Pass 2: Check for types and alteration
            $sqlResult = $this->_dbConn()->query('DESCRIBE '.$this->_dbRes()->getTableName($tableName).';');
            foreach ($sqlResult as $sqlRow) {
                if (array_key_exists($sqlRow['Field'], $structure[$tableName])) {
                    if ($sqlRow['Type'] != $structure[$tableName][$sqlRow['Field']]) {
                        $description[] = $this->_dbRes()->getTableName($tableName).' has different structure, field '.
                            $sqlRow['Field'].' has type '.$sqlRow['Type'];
                    }
                } else {
                    $description[] = $this->_dbRes()->getTableName($tableName).' was altered and has custom field '.
                        $sqlRow['Field'];
                }
            }
        }

        return (count($description)) ?
            array(self::FAILED, $title, array_merge($description, array('Contact Mirasvit Support.'))) :
            array(self::SUCCESS, $title, $description);
    }

    /**
     * Plz, add only tests for our BUGS into this function.
     * Always check that bug is present in current configuration of extension.
     *
     * Critical bugs should return FAILED
     * less critical bugs should return INFO
     * small bugs should not be here
     *
     * @return array
     */
    public function testKnownIssues()
    {
        $result = self::SUCCESS;
        $description = array();
        $title = 'Test for Known Issues ';
        if (class_exists('Mirasvit_Rewards_Helper_Code')) {
            $version = Mage::helper('rewards/code')->_version();
            $buildNumber = Mage::helper('rewards/code')->_build();
            $title .= '(Version: ' . $version . '.' . $buildNumber . ')';
        } else {
            $result = self::FAILED;
            $description[] = 'License error. Please contact Mirasvit Support.';
        }

        return array($result, $title, $description);
    }

    /**
     * Tests for conflicting extensions.
     * @return array
     */
    public function testKnownConflicts()
    {
        $result = self::SUCCESS;

        $title = 'Test for Known Conflicting Extensions ';
        if (class_exists('Mirasvit_Rewards_Helper_Code')) {
            $version = Mage::helper('rewards/code')->_version();
            $buildNumber = Mage::helper('rewards/code')->_build();
            $title .= '(Version: '.$version.'.'.$buildNumber.')';
        }
        $description = array();

        if (Mage::helper('mstcore')->isModuleInstalled('ProxiBlue_GiftPromo')) {
            $result = self::INFO;
            $description[] = 'ProxiBlue_GiftPromo Module is installed and conflicts with Mirasvit RWP. To disable it, '.
                'rename the file app/etc/modules/ProxiBlue_GiftPromo.xml to app/etc/modules/ProxiBlue_GiftPromo.xml.bak'
                . ' and flush the cache.';
        }
        return array($result, $title, $description);
    }
}
