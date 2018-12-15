<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'in_the_box_display_main_image',
    array(
        'group'                      => 'In the box',
        'type'                       => 'int',
        'input'                      => 'select',
        'source'                     => 'eav/entity_attribute_source_table',
        'label'                      => 'Display main product image',
        'backend'                    => 'eav/entity_attribute_backend_array',
        'default'                    => '2',
        'option'                     => array(
            'values' => array(
                1 => 'no',
                2 => 'yes',
            )
        ),
        'visible'                    => 1,
        'required'                   => 0,
        'user_defined'               => 1,
        'searchable'                 => 0,
        'filterable'                 => 0,
        'comparable'                 => 0,
        'visible_on_front'           => 0,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front'   => 0,
        'is_configurable'            => 0,
        'global'                     => 0,
        'sort_order'                 => '1000',
        'note'                       => 'Display current product in the box section'
    ));
$model = Mage::getModel('eav/entity_attribute')
    ->load($installer->getAttributeId('catalog_product', 'in_the_box_display_main_image'));
$model->setDefaultValue($model->getSource()->getOptionId('yes'))
    ->save();
$installer->endSetup();
