<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();
/** @var Mage_Eav_Model_Entity_Setup $eav */
$eav = Mage::getModel('eav/entity_setup', 'write');
$connection->beginTransaction();

try {
    
    /*
     * Update scope of popularity_by_sells, popularity_by_reviews, popularity_by_rating attributes
     * from Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
     * to Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
     */
    $eav->updateAttribute(
        Mage_Catalog_Model_Product::ENTITY,
        'popularity_by_sells',
        'is_global',
        Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
    );
    
    $eav->updateAttribute(
        Mage_Catalog_Model_Product::ENTITY,
        'popularity_by_reviews',
        'is_global',
        Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
    );
    
    $eav->updateAttribute(
        Mage_Catalog_Model_Product::ENTITY,
        'popularity_by_rating',
        'is_global',
        Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
    );
    
    // =======================================================================================
    
    $connection->commit();

} catch (Exception $e) {
    Mage::logException($e);
    $connection->rollBack();
}

$installer->endSetup();