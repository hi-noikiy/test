<?php

namespace Ktpl\SalesOrder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';
        $orderGridTable = 'sales_order_grid';

if (version_compare($context->getVersion(), '1.0.2', '<')) {

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'po',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Po'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'shipping_notes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'comment' =>'Shipping Notes'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'terms',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Terms'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'binno',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Bin No'
                ]
        );

        //Order table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'po',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Po'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'shipping_notes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'comment' =>'Shipping Notes'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'terms',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Terms'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'binno',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Bin No'
                ]
            );
}else if(version_compare($context->getVersion(), '1.0.3', '<')){
    //Added New coloumn : tax_code,order_type
    //quote Table
    $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'tax_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Tax Code'
                ]
            );
     $setup->getConnection()->addColumn(
                $setup->getTable($quote),
                'order_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Order Type'
                ]
            );

    }else if(version_compare($context->getVersion(), '1.0.4', '<')){
        //order Table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'tax_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Tax Code'
                ]
            );
        $setup->getConnection()->addColumn(
                $setup->getTable($orderTable),
                'order_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Order Type'
                ]
            );
    }else if(version_compare($context->getVersion(), '1.0.5', '<')){
        //order Table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'ship_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' =>'Ship Date'
                ]
            );
    }else if(version_compare($context->getVersion(), '1.0.6', '<')){
        //order Table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderGridTable),
                'ship_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' =>'Ship Date'
                ]
            );
    }else if(version_compare($context->getVersion(), '1.0.7', '<')){
        //order Table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'samples',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Samples'
                ]
            );
    }
    else if(version_compare($context->getVersion(), '1.0.8', '<')){
        //order Table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'business_developement',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Business Developement'
                ]
            );
    }



        $setup->endSetup();
    }
}