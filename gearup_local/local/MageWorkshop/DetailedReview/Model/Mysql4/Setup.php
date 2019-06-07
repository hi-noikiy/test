<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Model_Mysql4_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
{
    protected function _upgradeData($oldVersion, $newVersion)
    {
        parent::_upgradeData($oldVersion, $newVersion);
        /** @var MageWorkshop_Core_Helper_Data $helper */
        $helper = Mage::helper('drcore');
        $helper->clearCacheAfterInstall()
               ->reindexDataAfterInstall();
        Mage::getModel('detailedreview/sort')->refreshAllIndices();
        return $this;
    }
}

