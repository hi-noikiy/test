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



class Mirasvit_RewardsSocial_Model_Observer_Output
{

    /**
     * @param Varien_Event_Observer $obj
     *
     * @return $this
     */
    public function afterOutput($obj)
    {
        $block = $obj->getEvent()->getBlock();
        $transport = $obj->getEvent()->getTransport();

        $html = $transport->getHtml();

        $blockHtml = '';
        if ($this->isAllowed($block)) {
            $blockHtml = $block->getLayout()
                ->createBlock('rewardssocial/buttons', 'buttons')
                ->setTemplate('mst_rewardssocial/buttons.phtml')
                ->toHtml();
        }

        $transport->setHtml($blockHtml . $html);

        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return bool
     */
    protected function isAllowed($block)
    {
        if (Mage::getModel('rewardssocial/config')->getButtonsBlock()) {
            if (
                $block->getTemplateFile() == Mage::getModel('rewardssocial/config')->getButtonsBlock() ||
                $block->getTemplate() == Mage::getModel('rewardssocial/config')->getButtonsBlock()
            ) {
                return true;
            }
        } elseif ($block->getBlockAlias() == 'product.info') {
            return true;
        }

        return false;
    }
}
