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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Helper_Compatibility_Icart
{
    /**
     * @return bool
     */
    public function isEnable()
    {
        return Mage::helper('core')->isModuleEnabled('MageWorx_InstantCart');
    }

    /**
     * @return bool
     */
    public function isRequested()
    {
        return $this->isEnable() &&
            Mage::app()->getFrontController()->getRequest()->getModuleName() == 'icart' &&
            Mage::app()->getFrontController()->getRequest()->getControllerName() == 'index'
        ;
    }
}