<?php
/**
 * MageWorkshop
 * Copyright (C) 2017 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRCategoryRatings
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

// remembering old current store
$currentStore = Mage::app()->getStore();

if (!$currentStore->isAdmin()) {
    // switching to admin store
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
}

$installer->startSetup();
$connection = $installer->getConnection();

/** @var Mage_Core_Model_Resource_Store_Collection $stores */
$stores = Mage::getModel('core/store')->getCollection();

$connection->beginTransaction();

try {
    $ratingsAvailable = Mage::getResourceModel('catalog/eav_attribute')
        ->loadByCode(
            Mage_Catalog_Model_Category::ENTITY,
            MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE
        );
    
    if (!$ratingsAvailable->getId()) {
        /** @var Mage_Catalog_Model_Resource_Setup $setupModel */
        $setupModel = Mage::getResourceModel('catalog/setup', 'core_setup');
        $setupModel->addAttribute(
            Mage_Catalog_Model_Category::ENTITY,
            MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE,
            array(
                'type'                       => 'text',
                'label'                      => 'Available Ratings',
                'input'                      => 'multiselect',
                'source'                     => 'drcore/category_attribute_source_ratings',
                'backend'                    => 'drcore/category_attribute_backend_ratings',
                'default'                    => NULL,
                'input_renderer'             => 'drcore/adminhtml_catalog_category_helper_rating_available',//definition of renderer
                'visible_on_front'           => true,
                'sort_order'                 => 30,
                'required'                   => 0,
                'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                'group'                      => MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_GROUP_NAME,
            )
        );
    }
    
    foreach($stores as $store) {
        $ratingIds = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->setStoreFilter($store->getId())
            ->getAllIds();
        
        $obj = new Varien_Object(array(
            'entity_id'                                                     => $store->getRootCategoryId(),
            MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE   => implode(',', $ratingIds),
            'store_id'                                                      => $store->getId()
        ));
    
        /* @var $resource Mage_Catalog_Model_Resource_Category */
        $resource = Mage::getModel('catalog/category')->getResource();
        $resource->saveAttribute($obj, MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE);
    }
    $connection->commit();

} catch (Exception $e) {
    $connection->rollBack();
    Mage::logException($e);
}

$installer->endSetup();

// switching back to old current store
Mage::app()->setCurrentStore($currentStore->getId());
