<?php

$this->startSetup();
$this->removeAttribute('catalog_category','advertise');
$this->addAttribute('catalog_category', 'advertise', array(
						'type'                       => 'text',
                        'label'                      => 'Left advertising',
                        'input'                      => 'textarea',
                        'required'                   => false,
                        'sort_order'                 => 12,
                        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'            => true,
                        'is_html_allowed_on_front'   => true,
                        'group'                      => 'Advertising',
));
$this->endSetup();