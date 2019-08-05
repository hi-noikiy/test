<?php
namespace Krish\CriticReview\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('krish_review')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('krish_review'))
                ->addColumn(
                    'review_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true]
                )
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->addColumn('review', Table::TYPE_TEXT, '2M', ['default' => ''], 'Review')
                ->addColumn('author_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Author Name')
                ->addColumn('visibility', Table::TYPE_SMALLINT, null,['nullable' => false], 'Visibility')
                ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
                ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
                ->setComment('Critic Review Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('krish_product_attachment_rel')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('krish_product_attachment_rel'))
                ->addColumn('review_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true])
                ->addColumn('product_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true], 'Magento Product Id')
                ->addForeignKey(
                    $installer->getFkName(
                        'krish_review',
                        'review_id',
                        'krish_product_attachment_rel',
                        'review_id'
                    ),
                    'review_id',
                    $installer->getTable('krish_review'),
                    'review_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'krish_product_attachment_rel',
                        'review_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Krish Product Attachment relation table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
