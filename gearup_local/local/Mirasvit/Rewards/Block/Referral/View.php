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



class Mirasvit_Rewards_Block_Referral_View extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $referral = $this->getReferral();
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            /* @noinspection PhpUndefinedMethodInspection */
            $headBlock->setTitle(Mage::helper('rewards')->__('Referral %s', $referral->getName()));
        }
    }

    public function getReferral()
    {
        return Mage::registry('current_referral');
    }

    /************************/
}
