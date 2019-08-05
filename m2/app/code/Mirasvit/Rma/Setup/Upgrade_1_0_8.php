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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Upgrade_1_0_8 implements UpgradeInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_status'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_reason'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_condition'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_resolution'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_field'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_rule'),
            'name',
            'name',
            'VARCHAR(65535)'
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_rule'),
            'email_subject',
            'email_subject',
            'VARCHAR(65535)'
        );
    }
}