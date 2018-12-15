<?php
$installer = $this;
$installer->startSetup();

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
    'email/bankdetails.phtml',
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
