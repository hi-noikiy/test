<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Helper_Compatibility_Action extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isActionApplied() {
        if (Mage::helper('core')->isModuleEnabled('Mirasvit_Action')) {
            Mage::getSingleton('action/observer')->onControllerActionLayoutRenderBefore(false);
            if (Mage::registry('m__action_current_product_id')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isMagentoBundleEnabled() {
        return Mage::helper('core')->isModuleEnabled('Mage_Bundle');
    }
}
