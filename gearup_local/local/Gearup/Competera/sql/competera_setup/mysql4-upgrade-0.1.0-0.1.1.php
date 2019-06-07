<?php
 /**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

/**
 * Create table 'competera/custom_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('competera/customprice'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Product Id')
    ->addColumn('custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
       'nullable'  => false,
       'default'   => '0.0000',
       ), 'Custom Price')
    ->setComment('Custom Competera Price');
$installer->getConnection()->createTable($table);

$installer->endSetup();