<?php
/**
 * path magento_root/app/code/local/Atwix/Addallowed/sql/atwix_addallowed/install-1.0.0.php
 */
 
$installer = $this;
 
$installer->startSetup();
 
/**
 * Adding allowed blocks to Mage_Core_Model_Email_Template_Filter after SUPEE-6788 install
 */
$allowedBlocksArray = array(
    'cms/block',
    'custom/block_type'
);
 
foreach ($allowedBlocksArray as $item) {
    try {
        Mage::getModel('admin/block')->setData('block_name', $item)
            ->setData('is_allowed', 1)
            ->save()
        ;
    } catch(Exception $e) {
        Mage::log($e->getMessage(), null, 'atwix_add_allowed.log', true);
    }
}
 
/**
 * Adding allowed blocks to Mage_Core_Model_Email_Template_Filter after SUPEE-6788 install
 */
$allowedConfigArray = array(
    'custom/config/path',
);
 
foreach ($allowedConfigArray as $item) {
    try {
        Mage::getModel('admin/variable')->setData('variable_name', $item)
            ->setData('is_allowed', 1)
            ->save()
        ;
    } catch(Exception $e) {
        Mage::log($e->getMessage(), null, 'atwix_add_allowed.log', true);
    }
}
 
$installer->endSetup();