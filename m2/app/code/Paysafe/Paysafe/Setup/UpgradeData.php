<?php
namespace Paysafe\Paysafe\Setup;
 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $installer = $setup;

        if (version_compare($context->getVersion(), '2.0.01', '<')) {
            $installer->updateTableRow(
                    $installer->getTable('core_config_data'),
                    'path',
                    'payment/paysafe_general/recurring',
                    'value',
                    '0'
                );
        }
         $setup->endSetup();
    }
}