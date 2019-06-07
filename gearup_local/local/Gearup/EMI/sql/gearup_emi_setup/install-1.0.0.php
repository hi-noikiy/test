<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * EMI module install script
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
$this->startSetup();
$table = $this->getConnection()
    ->newTable($this->getTable('gearup_emi/banks'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Bank ID'
    )
    ->addColumn(
        'terms_condition',
        Varien_Db_Ddl_Table::TYPE_TEXT, '64k',
        array(),
        'Terms & Conditions'
    )
    ->addColumn(
        'image',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(),
        'Image'
    )
    ->addColumn(
        'title',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,
        ),
        'Title'
    )
    ->addColumn(
        'options',
        Varien_Db_Ddl_Table::TYPE_TEXT, '64k',
        array(),
        'Options'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(),
        'Enabled'
    )
    ->addColumn(
        'meta_title',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(),
        'Meta title'
    )
    ->addColumn(
        'meta_keywords',
        Varien_Db_Ddl_Table::TYPE_TEXT, '64k',
        array(),
        'Meta keywords'
    )
    ->addColumn(
        'meta_description',
        Varien_Db_Ddl_Table::TYPE_TEXT, '64k',
        array(),
        'Meta description'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Bank Modification Time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Bank Creation Time'
    ) 
    ->setComment('Bank Table');
$this->getConnection()->createTable($table);
$table = $this->getConnection()
    ->newTable($this->getTable('gearup_emi/banks_store'))
    ->addColumn(
        'banks_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable'  => false,
            'primary'   => true,
        ),
        'Bank ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Store ID'
    )
    ->addIndex(
        $this->getIdxName(
            'gearup_emi/banks_store',
            array('store_id')
        ),
        array('store_id')
    )
    ->addForeignKey(
        $this->getFkName(
            'gearup_emi/banks_store',
            'banks_id',
            'gearup_emi/banks',
            'entity_id'
        ),
        'banks_id',
        $this->getTable('gearup_emi/banks'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $this->getFkName(
            'gearup_emi/banks_store',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $this->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Banks Manager  To Store Linkage Table');
$this->getConnection()->createTable($table);
$this->endSetup();
