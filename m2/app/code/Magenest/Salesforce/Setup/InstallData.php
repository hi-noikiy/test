<?php
namespace Magenest\Salesforce\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    /**
     * Installs data for a module
     * Set default value is_connected is 0 (disconnect)
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => \Magenest\Salesforce\Model\Connector::XML_PATH_SALESFORCE_IS_CONNECTED,
            'value' => 0,
        ];
        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
    }
}