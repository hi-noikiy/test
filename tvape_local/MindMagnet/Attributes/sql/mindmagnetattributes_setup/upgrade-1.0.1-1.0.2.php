<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->addAttribute('catalog_product', 'type_section', array(
    'group' => 'General',
    'input' => 'multiselect',
    'type' => 'varchar',
    'label' => 'Type Section',
    'backend' => 'eav/entity_attribute_backend_array',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'option' => array (
        'value' => array(
            'herbs' => array('herbs'),
            'e-liquid' => array('e-liquid'),
            'wax' => array('wax'),
        )
    ),
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();