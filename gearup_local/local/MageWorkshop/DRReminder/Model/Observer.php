<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRReminder_Model_Observer
{

    public function initCreateReviewReminder($observer)
    {
        if(Mage::getStoreConfig('drreminder/settings/remind_enable')) {
            $order = $observer->getEvent()->getData('order');
            Mage::helper('drreminder')->createReviewReminder($order);
        }
    }

    public function checkIfModuleEnabled($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        $helper->checkIfModuleEnabled(
            $moduleContainer,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_MODULE_NAME,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_XML_PATH_MODULE_ENABLE
        );
    }

    public function enableModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->enableModule(
            $moduleConfig,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_MODULE_NAME,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_XML_PATH_MODULE_ENABLE
        );
    }

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_MODULE_NAME,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_PACKAGE_FILE,
            MageWorkshop_DRReminder_Helper_Data::DRREMINDER_UNINSTALL_PATH
        );
    }

}
