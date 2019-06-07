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



require_once Mage::getBaseDir('code').'/local/Lotusbreath/OneStepCheckout/controllers/IndexController.php';

class Mirasvit_Rewards_Checkout_LotusbreathController extends Lotusbreath_OneStepCheckout_IndexController
{
    public function applyPointsAction()
    {
        $response = Mage::helper('rewards/checkout')->processRequest();

        $return = array(
            'results' => $response,
        );
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));
    }

    public function getRewardsAction()
    {
        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setIsAjax(true)
            ->setTemplate('mst_rewards/checkout/cart/usepoints_lotusbreath.phtml');

        $return = array(
            'html' => $block->toHtml(),
        );

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));
    }
}
