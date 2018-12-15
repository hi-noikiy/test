<?php
    /* @var $installer Mage_Customer_Model_Entity_Setup */
    $installer = $this;
    $installer->startSetup();
    /* @var $addressHelper Mage_Customer_Helper_Address */
    $addressHelper = Mage::helper('customer/address');
    $store         = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
    /* @var $eavConfig Mage_Eav_Model_Config */
    $eavConfig = Mage::getSingleton('eav/config');
 
    // update customer address user defined attributes data
    $attributes = array(
        'latitude'           => array(
            'label'    => 'Latitude',
            'backend_type'     => 'varchar',
            'frontend_input'    => 'text',
            'is_user_defined'   => 1,
            'is_system'         => 0,
            'is_visible'        => 1,
            'sort_order'        => 140,
            'is_required'       => 0,
            'multiline_count'   => 0,
            'validate_rules'    => array(
                'max_text_length'   => 255,
                
            ),
        ),
    );
    $attributes1 = array(
        'longitude'           => array(
            'label'    => 'Longitude',
            'backend_type'     => 'varchar',
            'frontend_input'    => 'text',
            'is_user_defined'   => 1,
            'is_system'         => 0,
            'is_visible'        => 1,
            'sort_order'        => 145,
            'is_required'       => 0,
            'multiline_count'   => 0,
            'validate_rules'    => array(
                'max_text_length'   => 255,
                
            ),
        ),
    );
 
    foreach ($attributes as $attributeCode => $data) {
        $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
        $attribute->setWebsite($store->getWebsite());
        $attribute->addData($data);
            $usedInForms = array(
                'adminhtml_customer_address',
                
            );
            $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();
    }
    foreach ($attributes1 as $attributeCode => $data) {
        $attribute1 = $eavConfig->getAttribute('customer_address', $attributeCode);
        $attribute1->setWebsite($store->getWebsite());
        $attribute1->addData($data);
            $usedInForms = array(
                'adminhtml_customer_address',
                
            );
            $attribute1->setData('used_in_forms', $usedInForms);
        $attribute1->save();
    }
 
    $installer->run("
         ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `longitude` VARCHAR(255) AFTER `fax`,
             ADD `latitude` VARCHAR(255) AFTER `fax`;
        ");
    $installer->endSetup();
?>