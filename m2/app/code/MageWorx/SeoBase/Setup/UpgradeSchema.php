<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addHreflangIdentifierToCmsPage($setup);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->addDefaultValueForMetaRobots($setup);
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->updateCategoryLnMetaRobotsSetting($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function updateCategoryLnMetaRobotsSetting(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('core_config_data'),
            ['path' => 'mageworx_seo/base/robots/category_ln_pages_robots'],
            "path = 'mageworx_seo/base/robots/category_filter_to_noindex'"
        );

        $setup->getConnection()->update(
            $setup->getTable('core_config_data'),
            ['value' => 'NOINDEX, FOLLOW'],
            "path = 'mageworx_seo/base/robots/category_ln_pages_robots' AND value = '1'"
        );

        $setup->getConnection()->update(
            $setup->getTable('core_config_data'),
            ['value' => ''],
            "path = 'mageworx_seo/base/robots/category_ln_pages_robots' AND value = '0'"
        );
    }

    /**
     * Add Hreflang Identifier column
     * @param SchemaSetupInterface $setup
     */
    private function addHreflangIdentifierToCmsPage(SchemaSetupInterface $setup)
    {
         $setup->getConnection()->addColumn(
             $setup->getTable('cms_page'),
             'mageworx_hreflang_identifier',
             [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Added by MageWorx for Hreflang URLs',
                    'after'     => 'identifier'
                ]
         );
    }

    /**
     * Add default value for the meta robots column
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addDefaultValueForMetaRobots(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->modifyColumn(
            $setup->getTable('cms_page'),
            'meta_robots',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable'  => false,
                'length'    => 255,
                'comment'   => 'Meta Robots (added by MageWorx SeoBase)',
                'default'   => '',
                'after'     => 'meta_description'
            ]
        );
    }
}
