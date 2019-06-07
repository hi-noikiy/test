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



class Mirasvit_Rewards_Model_Observer_Output
{

    /**
     * Function, that forcibly refreshes points on cart or checkout
     * @return void
     */
    protected function forcedRefreshPoints()
    {
        $force = false;
        /** @var Mirasvit_Rewards_Helper_Compatibility_Icart $icartHelper */
        $icartHelper = Mage::helper('rewards/compatibility_icart');

        if (!Mage::getSingleton('rewards/config')->getAdvancedObserverRefreshPoints()) {
            return;
        }

        if (Mage::app()->getRequest()->getControllerName() != 'cart' &&
            Mage::app()->getRequest()->getControllerName() != 'onepage' &&
            Mage::app()->getRequest()->getControllerName() != 'checkout_cart' &&
            !$icartHelper->isRequested()
        ) {
            return;
        }

        if (!(Mage::getModel('customer/session')->isLoggedIn() && Mage::getModel('customer/session')->getId())) {
            return;
        }

        if (!$quote = Mage::getSingleton('checkout/session')->getQuote()) {
            return;
        }

        if (!$purchase = Mage::helper('rewards/purchase')->getByQuote($quote)) {
            return;
        }

        if ($icartHelper->isRequested()) {
            $force = true;
        }

        $purchase->refreshPointsNumber($force);
        $purchase->save();
    }

    /**
     * @param Varien_Event_Observer $obj
     *
     * @return $this
     */
    public function afterOutput($obj)
    {
        $block = $obj->getEvent()->getBlock();
        /** @var Varien_Object $transport */
        /* @noinspection PhpUndefinedMethodInspection */
        $transport = $obj->getEvent()->getTransport();
        if (empty($transport)) { //it does not work for magento 1.4 and older
            return $this;
        }
        /** @var Mirasvit_Rewards_Model_Config $config */
        $config = Mage::getModel('rewards/config');
        if ($config->getDisplayCart()) {
            $this->appendCartPointsBlock($block, $transport);
        }
        if (!$config->getDisplayCheckout() || $this->isBlockInserted) {
            return $this;
        }
        //$this->appendToCatalogListing($block, $transport); //this can produce high server load
        $this->appendAccountPointsSummary($block, $transport);
        $this->appendToRWDOnestepcheckout($block, $transport);
        $this->appendToIdevOnestepcheckout($block, $transport);
        $this->appendToMageStoreOnestepcheckout($block, $transport);
        $this->appendToAppthaOnestepcheckout($block, $transport);
        $this->appendToIWDOnestepcheckout($block, $transport);
        $this->appendToFirecheckout($block, $transport);
        $this->appendToMagegiantCheckout($block, $transport);
        $this->appendToLotusbreathCheckout($block, $transport);
        $this->appendToGoMageLightCheckout($block, $transport);
        $this->appendToAheadOneStepCheckout($block, $transport);
        $this->appendToAitocCheckout($block, $transport);
        $this->appendToAmastyOneStepCheckout($block, $transport);

        return $this;
    }

    /**
     * Internal variable to ensure, that block is displayed once
     */
    protected $isBlockInserted = false;

    /**
     * Appends spending block to the cart
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendCartPointsBlock($block, $transport)
    {
        if (Mage::app()->getRequest()->getControllerName() != 'cart' &&
            Mage::app()->getRequest()->getControllerName() != 'checkout_cart' &&
            Mage::app()->getRequest()->getControllerName() != "onepage" &&
            !Mage::helper('rewards/compatibility_icart')->isRequested()
        ) {
            return $this;
        }
        if ($block->getBlockAlias() == 'coupon'
            // || $block->getBlockAlias() == 'shipping' //if we add to shipping block, we have bad CSS styles
            || $block->getBlockAlias() == 'crosssell') {
            $b = $block->getLayout()
                ->createBlock('rewards/checkout_cart_usepoints', 'usepoints')
                ->setTemplate('mst_rewards/checkout/cart/usepoints.phtml');

            $this->forcedRefreshPoints();
            $html = $transport->getHtml();
            $ourhtml = $b->toHtml();
            $config = Mage::getSingleton('rewards/config');
            $items = array_keys(Mage::getSingleton('checkout/session')->getQuote()->getTotals());
            if (!$this->isBlockInserted) {
                if (($config->getGeneralIsAllowRewardsAndCoupons() ==
                        Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_HIDDEN)
                    && in_array('rewards_spend', $items)) {
                    $html = $ourhtml;
                } else {
                    if ((($config->getGeneralIsAllowRewardsAndCoupons() ==
                            Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_WARNED)
                        && in_array('rewards_spend', $items))) {
                        $html = str_replace('<div class="discount-form">',
                            '<div class="discount-form"><font color="red">'.
                            Mage::helper('rewards')->__('You can not use coupon with points spent.').'</font><br>',
                            $html);
                    }
                    $html = $ourhtml.$html;
                }

                $this->isBlockInserted = true;
            }
            $transport->setHtml($html);
        }

        return $this;
    }

    /**
     * Appends account points summary
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendAccountPointsSummary($block, $transport)
    {
        if ($block->getBlockAlias() == 'top' && $block->getChild('rewards_customer_account_dashboard_top')) {
            $html = $transport->getHtml();
            $ourhtml = $block->getChildHtml('rewards_customer_account_dashboard_top');

            if ($ourhtml && strpos($html, $ourhtml) === false) {
                $html = $ourhtml.$html;
            }
            $transport->setHtml($html);
        }

        return $this;
    }

    /**
     * Appends block to catalog listing
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToCatalogListing($block, $transport)
    {
        if (!($block instanceof Mage_Catalog_Block_Product_List)) {
            return $this;
        }
        $productCollection = $block->getLoadedProductCollection();
        $html = $transport->getHtml();
        $html = $this->_addToProductListHtml($productCollection, $block, $html);
        $transport->setHtml($html);

        return $this;
    }

    /**
     * @param array $productCollection
     * @param Mage_Core_Block_Template $block
     * @param string $html
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    protected function _addToProductListHtml($productCollection, $block, $html)
    {
        $isListMode = strpos($html, 'class="products-list" id="products-list">') !== false;

        foreach ($productCollection as $product) {
            $b = $block->getLayout()
                ->createBlock('rewards/product_list_points', 'rewards_product_list_points')
                ->setTemplate('mst_rewards/product/list/points.phtml');
            $block->insert($b);
            $b->setProduct($product);
            $ourhtml = $b->toHtml();

            if (!$ourhtml) {
                continue;
            }
        }

        return $html;
    }

    /**
     * Appends spending block to IDev Checkout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToRWDOnestepcheckout($block, $transport)
    {
        if (Mage::getSingleton('core/design_package')->getPackageName() != 'rwd' ||
            Mage::getStoreConfig('firecheckout/general/enabled')) {
            return $this;
        }

        if ($block instanceof Mage_Checkout_Block_Onepage) {
            $this->forcedRefreshPoints();
            $html = $transport->getHtml();
            $block = Mage::app()->getLayout()
                ->createBlock('rewards/checkout_cart_usepoints', 'usepoints')
                ->setTemplate('mst_rewards/checkout/cart/rwd-ajax.phtml');
            $transport->setHtml($block->toHtml() . $html);
            return $this;
        }

        if (!$block instanceof Mage_Checkout_Block_Onepage_Payment_Methods) {
            return $this;
        }

        $html = $transport->getHtml();
        if (!strpos($html, 'checkout-payment-method-load')) {
            $this->forcedRefreshPoints();
            $block = Mage::app()->getLayout()
                ->createBlock('rewards/checkout_cart_usepoints', 'usepoints')
                ->setTemplate('mst_rewards/checkout/cart/usepoints_rwdcheckout.phtml');
            $transport->setHtml($block->toHtml() . $html);
        }

        return $this;
    }

    /**
     * Appends spending block to IDev Checkout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToIdevOnestepcheckout($block, $transport)
    {
        if (!$block instanceof Idev_OneStepCheckout_Block_Checkout) {
            return $this;
        }
        $this->forcedRefreshPoints();
        $html = $transport->getHtml();

        $anchor = '<div class="payment-methods">';
        if (strpos($html, $anchor)) {
            $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
                ->setTemplate('mst_rewards/checkout/cart/usepoints_idev_onestepcheckout.phtml');
            $xpos = strpos($html, '</dl>', strpos($html, $anchor) + strlen($anchor));
            $pos = strpos($html, '</div>', $xpos) + 6;
            $html = substr($html, 0, $pos) . $block->toHtml() . substr($html, $pos);
        }

        $transport->setHtml($html);

        return $this;
    }

    /**
     * Appends spending block to MageStore OnestepCheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToMageStoreOnestepcheckout($block, $transport)
    {
        if (!$block instanceof Magestore_Onestepcheckout_Block_Onestepcheckout) {
            return $this;
        }
        $this->forcedRefreshPoints();
        $html = $transport->getHtml();

        if (strpos($html, '<li class="payment-method">') !== false) {
            $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
                ->setTemplate('mst_rewards/checkout/cart/usepoints_magestore_onestepcheckout.phtml');
            $html = preg_replace(
                '/<li class="payment-method">/', $block->toHtml().'<li class="payment-method">', $html, 1
            );
        }

        $transport->setHtml($html);

        return $this;
    }

    /**
     * Appends spending block to Apptha OnestepCheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToAppthaOnestepcheckout($block, $transport)
    {
        if (!$block instanceof Apptha_Onestepcheckout_Block_Onestepcheckout) {
            return $this;
        }
        $this->forcedRefreshPoints();
        $html = $transport->getHtml();

        $ourBlock = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_apptha_onestepcheckout.phtml');

        $anchor = '<li id="column-3" class="firecheckout-section">';
        $pos = strpos($html, $anchor);
        if ($pos !== false) {
            $xpos = $pos - 15;
            $xanchor = '</ul>';
            $xpos = strpos($html, $xanchor, $xpos);
            if ($xpos !== false) {
                $hanchor = substr($html, $xpos, $pos - $xpos + strlen($anchor));
                $html = str_replace($hanchor, $ourBlock->toHtml().$hanchor, $html);
            }
        } else {
            $anchor = '<li id="column-2" class="firecheckout-section apptha_checkout_column-2">';
            if (strpos($html, $anchor) !== false) {
                $html = str_replace($anchor, $ourBlock->toHtml().$anchor, $html);
            }
        }

        $transport->setHtml($html);

        return $this;
    }

    /**
     * Appends spending block to IWD OnestepCheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToIWDOnestepcheckout($block, $transport)
    {
        if (!$block instanceof IWD_Opc_Block_Wrapper) {
            return $this;
        }
        $this->forcedRefreshPoints();
        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_iwd_onestepcheckout.phtml');
        $html = $transport->getHtml();
        $anchor = '<div class="shipping-block">';
        $html = str_replace($anchor, $block->toHtml().$anchor, $html);
        $transport->setHtml($html);

        return $this;
    }

    /**
     * Appends spending block to Firecheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object $transport
     * @return Mirasvit_Rewards_Model_Observer_Output
     */
    public function appendToFirecheckout($block, $transport)
    {
        if (!$block instanceof TM_FireCheckout_Block_Checkout) {
            return $this;
        }

        $html = $transport->getHtml();
        $expr = '(<div\s+id=\"payment-method\" class=\"firecheckout-section\">)';
        if (!preg_match($expr, $html)) {
            return $this;
        }
        $this->forcedRefreshPoints();

        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_firecheckout.phtml');
        $html = preg_replace($expr, $block->toHtml() . '$0', $html);

        $transport->setHtml($html);
        $this->isBlockInserted = true;

        return $this;
    }

    /**
     * Appends spending block to Firecheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     * @return $this
     */
    private function appendToMagegiantCheckout($block, $transport)
    {
        if (!$block instanceof Magegiant_Onestepcheckout_Block_Onestep_Form_Review_Comments) {
            return $this;
        }
        $this->forcedRefreshPoints();

        $html = $transport->getHtml();

        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_magegiant.phtml');
        $anchor = '<div id="giant-onestepcheckout-order-review-comments-wrapper">';
        $html = str_replace($anchor, $block->toHtml().$anchor, $html);
        $transport->setHtml($html);

        return $this;
    }

    /**
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     *
     * @return $this
     */
    private function appendToLotusbreathCheckout($block, $transport)
    {
        if ((
                (Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowshowcoupon') && !$block instanceof Mage_Checkout_Block_Cart_Coupon) || (
                    !Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowshowcoupon') &&
                    !$block instanceof Lotusbreath_OneStepCheckout_Block_Onepage_Payment
                )
            ) ||
            !Mage::helper('mstcore')->isModuleInstalled('Lotusbreath_OneStepCheckout') ||
            (Mage::app()->getRequest()->isAjax() && !($block instanceof Lotusbreath_OneStepCheckout_Block_Onepage_Review))
        ) {
            return $this;
        }
        $this->forcedRefreshPoints();
        $html = $transport->getHtml();
        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_lotusbreath.phtml');
        $html = $html.$block->toHtml();
        $transport->setHtml($html);
        $this->isBlockInserted = true;

        return $this;
    }

    /**
     * Appends spending block to MageLight Checkout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     * @return $this
     */
    private function appendToGoMageLightCheckout($block, $transport)
    {
        if (!$block instanceof GoMage_Checkout_Block_Onepage) {
            return $this;
        }

        if (!$this->isBlockInserted) {
            $this->forcedRefreshPoints();
            $html = $transport->getHtml();
            $anchor = '<div class="glc-step methods" id="gcheckout-onepage-methods">';
            $ourBlock = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
                ->setTemplate('mst_rewards/checkout/cart/usepoints_gomagecheckout.phtml');

            $html = str_replace($anchor, $ourBlock->toHtml().$anchor, $html);
            $transport->setHtml($html);
            $this->isBlockInserted = true;
        }

        return $this;
    }

    /**
     * Appends spending block to Ahead Onestepcheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     * @return $this
     */
    private function appendToAheadOneStepCheckout($block, $transport)
    {
        if (!$block instanceof AW_Onestepcheckout_Block_Onestep) {
            return $this;
        }

        $this->forcedRefreshPoints();
        $html = $transport->getHtml();
        $anchor = '<div id="aw-onestepcheckout-shipping-method-wrapper"';
        $ourBlock = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_aheadcheckout.phtml');

        $html = str_replace($anchor, $ourBlock->toHtml().$anchor, $html);

        $transport->setHtml($html);

        return $this;
    }

    /**
     * Appends spending block to Aitoc Onestepcheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     * @return $this
     */
    private function appendToAitocCheckout($block, $transport)
    {
        if (!$block instanceof Aitoc_Aitcheckout_Block_Checkout_Billing) {
            return $this;
        }

        $this->forcedRefreshPoints();
        $html = $transport->getHtml();
        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints_aitoc_onestepcheckout.phtml');
        $transport->setHtml($html . $block->toHtml());

        return $this;
    }

    /**
     * Appends spending block to Amasty Onestepcheckout
     *
     * @param Mage_Core_Block_Template $block
     * @param Varien_Object            $transport
     * @return $this
     */
    private function appendToAmastyOneStepCheckout($block, $transport)
    {
        if (!$action = Mage::app()->getFrontController()->getAction()) {
            return $this;
        }

        if (!$block instanceof Mage_Checkout_Block_Onepage_Payment ||
            $action->getFullActionName() == 'checkout_cart_index' ||
            !Mage::helper('mstcore')->isModuleInstalled('Amasty_Scheckout')
        ) {
            return $this;
        }

        $version = Mage::getConfig()->getNode()->modules->Amasty_Scheckout->version->asArray();
        $template = 'mst_rewards/checkout/cart/usepoints_amasty_onestepcheckout.phtml';
        if (version_compare($version, '3.1.3') === 1) {
            $template = 'mst_rewards/checkout/cart/usepoints_amasty_onestepcheckout_313.phtml';
        }
        $this->forcedRefreshPoints();
        $html = $transport->getHtml();
        $block = Mage::app()->getLayout()->createBlock('rewards/checkout_cart_usepoints')
            //->setTemplate($template);
            ->setTemplate('mst_rewards/checkout/cart/usepoints_amasty_onestepcheckout.phtml');
        $transport->setHtml($html . $block->toHtml());

        return $this;
    }
}
