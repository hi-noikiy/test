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

/**
 * Class MageWorkshop_CommentOnReview_Model_Observer
 */
class MageWorkshop_DRCategoryRatings_Model_Observer
{

    public function checkIfModuleEnabled($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        $helper->checkIfModuleEnabled(
            $moduleContainer,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_MODULE_NAME,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_XML_PATH_MODULE_ENABLE
        );
    }

    public function enableModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->enableModule(
            $moduleConfig,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_MODULE_NAME,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_XML_PATH_MODULE_ENABLE
        );
    }

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_MODULE_NAME,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_PACKAGE_FILE,
            MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_UNINSTALL_PATH
        );
    }
    
    public function setProductId()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        Mage::getSingleton('admin/session')->setData('dr_product_id', $id);
    }
    
    /**
     * @param Varien_Event_Observer $observer
     */
    public function toggleTab(Varien_Event_Observer $observer)
    {
        if (!Mage::app()->getRequest()->getParam('store')) {
            /** @var Mage_Adminhtml_Block_Catalog_Category_Tabs $tabManager */
            $tabManager = $observer->getData('tabs');
            /** @var Mage_Eav_Model_Entity_Attribute_Group $group */
            $group = Mage::getModel('eav/entity_attribute_group');
            $group->load(MageWorkshop_DRCategoryRatings_Helper_Data::DRCATEGORYRATINGS_GROUP_NAME, 'attribute_group_name');
            $tabManager->removeTab('group_' . $group->getId());
        }
    }
}