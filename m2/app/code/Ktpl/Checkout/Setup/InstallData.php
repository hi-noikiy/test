<?php
/**
 * Copyright � Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\Checkout\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $_customerSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->_customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
            $eavSetup = $this->_customerSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute('customer_address', 'dob', array(
                'type' => 'static',
                'input' => 'date',
                'label' => 'Dob',
                'frontend' => \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                'global' => 1,
                'validate_rules' => '{"input_validation":"date"}',
                'visible' => 1,
                'required' => 0,
                'user_defined' => 1,
                'system'=>0,
                'group'=>'General',
                'visible_on_front' => 1,
            ));
            $eavSetup->getEavConfig()->getAttribute('customer_address','dob')
                   ->setUsedInForms(array('adminhtml_customer_address','customer_address_edit','customer_register_address'))
                   ->save();
    }
}
