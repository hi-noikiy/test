<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class Aheadworks\StoreCredit\Setup\InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $setupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $setupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $setupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        /**
         * Install eav entity types to the eav/entity_type table
         */
        $attributes = [
            'aw_use_store_credit' => ['type' => Table::TYPE_INTEGER],
            'aw_store_credit_amount' => ['type' => Table::TYPE_DECIMAL],
            'base_aw_store_credit_amount' => ['type' => Table::TYPE_DECIMAL]
        ];

        foreach ($attributes as $attributeCode => $attributeParams) {
            $quoteSetup->addAttribute('quote', $attributeCode, $attributeParams);
            $quoteSetup->addAttribute('quote_address', $attributeCode, $attributeParams);

            $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
            $salesSetup->addAttribute('invoice', $attributeCode, $attributeParams);
            $salesSetup->addAttribute('creditmemo', $attributeCode, $attributeParams);
        }

        $salesSetup->addAttribute('order', 'base_aw_store_credit_invoiced', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_store_credit_invoiced', ['type' => Table::TYPE_DECIMAL]);

        $salesSetup->addAttribute('order', 'base_aw_store_credit_refunded', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_store_credit_refunded', ['type' => Table::TYPE_DECIMAL]);

        $salesSetup->addAttribute('creditmemo', 'base_aw_store_credit_refunded', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_store_credit_refunded', ['type' => Table::TYPE_DECIMAL]);

        $salesSetup->addAttribute('order', 'base_aw_store_credit_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_store_credit_reimbursed', ['type' => Table::TYPE_DECIMAL]);

        $salesSetup->addAttribute('creditmemo', 'base_aw_store_credit_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_store_credit_reimbursed', ['type' => Table::TYPE_DECIMAL]);
    }
}
