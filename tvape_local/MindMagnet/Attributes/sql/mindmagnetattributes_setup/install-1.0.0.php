<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');



$installer->startSetup();

$setup->addAttributeGroup('catalog_product', 'Default', 'Technical Specs', 10);


$setup->addAttribute('catalog_product', 'origins', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'text',
    'label' => 'Origins',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'battery_life', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'text',
    'label' => 'Battery life',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'heat_time', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'text',
    'label' => 'Heat time',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'heat_style', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Heat style',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$setup->addAttribute('catalog_product', 'temperature', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Temperature',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'compatibility', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Compatibility',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$setup->addAttribute('catalog_product', 'versions', array(
    'group' => 'Technical Specs',
    'input' => 'multiselect',
    'type' => 'varchar',
    'label' => 'Versions',
    'backend' => 'eav/entity_attribute_backend_array',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'option' => array (
                'value' => array(
                    '3_2016' => array('3/2016 (current)'),
                    '11_2015' => array('11/2015'),
                    '2_2015' => array('2/2015'),
                )
    ),
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'materials', array(
    'group' => 'Technical Specs',
    'input' => 'multiselect',
    'type' => 'varchar',
    'label' => 'Materials',
    'backend' => 'eav/entity_attribute_backend_array',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'option' => array (
        'value' => array(
            'anodized_aluminum_shell' => array('Anodized Aluminum Shell'),
            'ceramic_heating_chamber' => array('Ceramic Heating Chamber'),
        )
    ),
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$setup->addAttribute('catalog_product', 'key_features', array(
    'group' => 'Technical Specs',
    'input' => 'multiselect',
    'type' => 'varchar',
    'label' => 'Key Features',
    'backend' => 'eav/entity_attribute_backend_array',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'option' => array (
        'value' => array(
            'illuminated_heating_chamber' => array('Illuminated Heating Chamber'),
            'hidden_mouthpiece_storage' => array('Hidden Mouthpiece Storage'),
        )
    ),
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$installer->endSetup();