<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\CanadaPost\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /**
         * Add attributes to the eav/attribute table
         */



        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'ship_req_signature',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Require signature on delivery?',
                'note' => 'This ensures Canada Post Rates Requests including this product will force Require Signature where applicable.',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'ship_req_proof_of_age',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Require proof of age on delivery?',
                'note' => 'This ensures Canada Post will require proof of age on delivery.',
                'input' => 'select',
                'class' => '',
                'source' => '\CollinsHarper\CanadaPost\Model\Source\ProofOfAge',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
           //     'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );


        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'hs_tariff_code',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => ' Harmonized System Codes (HS Code)',
                'note' => 'This is the HS code used for custom labels, please check with your logistics department for assistance.',
                'input' => 'text',
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );




        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'origin_province',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Province of Origin',
                'note' => 'The origin Province of the product, related to customs.',
                'input' => 'select',
                'class' => '',
                'source' => 'CollinsHarper\CanadaPost\Model\Source\Region\Calist',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
             //   'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );



        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'restrict_shipping_methods',
            [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Restrict Shipping Method',
                'note' => 'Restrict the allowed shipping methods this product can be sent via (Canada Post).',
                'input' => 'multiselect',
                'class' => '',
                'source' => '\CollinsHarper\CanadaPost\Model\Source\Method\Lists',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
           //     'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allowed_shipping_methods',
            [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Allowed Shipping Method',
                'note' => 'List of allowed shipping methods this product can be sent through (Canada Post).',
                'input' => 'multiselect',
                'class' => '',
                'source' => 'CollinsHarper\CanadaPost\Model\Source\Method\Lists',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
           //     'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,bundle,grouped,configurable',
                'used_in_product_listing' => true
            ]
        );

    }
}