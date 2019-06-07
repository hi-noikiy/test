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

class MageWorkshop_DRCategoryRatings_Model_Uninstall
{
    public function clearDatabaseInformation()
    {
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $setup->startSetup();
        $coreResource = Mage::getSingleton('core/resource');
        $coreResourceTable = $coreResource->getTableName('core/resource');
        $catalogSetup = Mage::getResourceModel('catalog/setup','catalog_setup');
        
        try {
            $setup->deleteTableRow($coreResourceTable,'code','drcategoryratings_setup');
            $catalogSetup->removeAttribute(Mage_Catalog_Model_Category::ENTITY, MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $setup->endSetup();
    }
}