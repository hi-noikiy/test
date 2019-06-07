<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */


class  MageWorkshop_ReviewWall_Model_Observer
{

    const MODULE_NAME = 'MageWorkshop_ReviewWall';

    public function checkIfModuleEnabled($observer)
    {
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        if ($moduleContainer->getModule() == self::MODULE_NAME) {
            $moduleContainer->setEnabled('noOption');
        }
    }

    public function uninstallModule(Varien_Event_Observer $observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_ReviewWall_Helper_Data::REVIEWWALL_MODULE_NAME,
            MageWorkshop_ReviewWall_Helper_Data::REVIEWWALL_PACKAGE_FILE,
            MageWorkshop_ReviewWall_Helper_Data::REVIEWWALL_UNINSTALL_PATH
        );
    }

    public function checkCmsView()
    {
        $layout = Mage::app()->getLayout();
        /** @var Mage_Cms_Block_Page $block */
        if ($block = $layout->getBlock('cms_page')) {
            $content = $block->getPage()->getContent();

            if (strpos($content, 'reviewwall/widget_wall') !== false) {
                /** @var Mage_Page_Block_Html_Head $head */
                if ($head = $layout->getBlock('head')) {
                    /** @var MageWorkshop_ReviewWall_Helper_JSCSSManager $helper */
                    $helper = Mage::helper('reviewwall/jSCSSManager');
                    $helper->getCSSandJS($head);
                }
            }
        }
    }
}
