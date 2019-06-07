<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_Core_Model_Observer
{

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_Core_Helper_Data::CORE_MODULE_NAME,
            MageWorkshop_Core_Helper_Data::CORE_PACKAGE_FILE,
            MageWorkshop_Core_Helper_Data::CORE_UNINSTALL_PATH
        );
    }

    /**
     * Check DetailedReview Admin Configuration
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSystemConfigChangedSectionDetailedreview(Varien_Event_Observer $observer)
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $detailedReviewHelper */
        $detailedReviewHelper = Mage::helper('detailedreview');
        /** @var MageWorkshop_Core_Helper_Data $drCoreHelper */
        $drCoreHelper = Mage::helper('drcore');

        /** @var Mage_Adminhtml_Model_Config_Data $configDataModel */
        $configDataModel = Mage::getSingleton('adminhtml/config_data');

        $facebookPingResult = $drCoreHelper->pingDetailedReviewFacebook();

        if (isset($facebookPingResult['error'])) {

            Mage::getConfig()->saveConfig(
                'detailedreview/social_share_optios/share_review_to_facebook',
                '0',
                $configDataModel->getScope(),
                $configDataModel->getScopeId()
            );

            Mage::log(
                'MageWorkshop_DetailedReview Facebook: ' . $facebookPingResult['error']['message'],
                Zend_Log::ERR,
                'exception.log'
            );

            Mage::getSingleton('core/session')->addError(
                $detailedReviewHelper->__(
                    'Facebook configuration could not be enabled. Incorrect "Facebook App ID Key" or "Facebook App Secret Key".'
                )
            );
        }

        /** @var MageWorkshop_Core_Helper_Data $mageWorkshopCoreHelper */
        $mageWorkshopCoreHelper = Mage::helper('drcore');

        if (Mage::getStoreConfig('detailedreview/captcha/enabled')) {
            $reCaptchaPingResult = $mageWorkshopCoreHelper->pingMageWorkshopReCaptcha(
                Mage::getStoreConfig('detailedreview/captcha/private_key')
            );

            if (!$reCaptchaPingResult) {
                Mage::getConfig()->saveConfig(
                    'detailedreview/captcha/enabled',
                    '0',
                    $configDataModel->getScope(),
                    $configDataModel->getScopeId()
                );

                Mage::log(
                    'MageWorkshop_DetailedReview ReCaptcha: Google ReCaptcha connection incorrect. '
                    . 'Check "Private Key" configuration by path '
                    . 'System -> Configuration -> MageWorkshop -> Detailed Review -> Captcha Options.',
                    Zend_Log::ERR,
                    'exception.log'
                );

                Mage::getSingleton('core/session')->addError(
                    $mageWorkshopCoreHelper->__(
                        'Captcha could not be enabled. Please check "Private Key" configuration.'
                    )
                );
            }
        }
    }
}
