<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Uninstall constructor.
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     */
    function __construct(
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tablesToDrop = [
            'amasty_affiliate_lifetime',
            'amasty_affiliate_links',
            'amasty_affiliate_transaction',
            'amasty_affiliate_account',
            'amasty_affiliate_banner',
            'amasty_affiliate_program',
            'amasty_affiliate_coupon'
        ];

        foreach ($tablesToDrop as $table) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }

        $installer->endSetup();
    }
}
