<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Gearup_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {

        Mage::getSingleton('core/session')->setUpdateAction('shipping_change');

        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->collectTotals()->save();
        $this->_getSession()->setEstimatedShippingAddressData(array(
            'country_id' => $country,
            'postcode'   => $postcode,
            'city'       => $city,
            'region_id'  => $regionId,
            'region'     => $region
        ));
        $this->_redirect('checkout/onepage/ajax');

    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_redirect('checkout/onepage/ajax');
            return;
        }

        Mage::getSingleton('core/session')->setUpdateAction('coupon');
        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_redirect('checkout/onepage/ajax');
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                    $this->_getSession()->setCartCouponCode($couponCode);
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                Mage::getSingleton('checkout/type_onepage')->getCheckout()->setCartCouponCode(null);
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/onepage/ajax');
    }

    public function deleteAction()
    {
        Mage::getSingleton('core/session')->setUpdateAction('cart_remove');

        if ($this->_validateFormKey()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                try {
                    $this->_getCart()->removeItem($id)
                        ->save();
                } catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Cannot remove the item.'));
                    Mage::logException($e);
                }
            }
        } else {
            $this->_getSession()->addError($this->__('Cannot remove the item.'));
        }

        $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/ajax'));
    }

    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        Mage::getSingleton('core/session')->setUpdateAction('update_qty');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_redirect('checkout/onepage/ajax');
    }
    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {

            $cart = $this->_getCart();
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $cartMessage = array();
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $stockQty = $cart->getItems()->getItems()[$index]->getProduct()->getStockItem()->getQty();

                        if ($data['qty'] > $stockQty) {
                            $cartData[$index]['qty'] = $filter->filter($stockQty);//trim($data['qty']));
                            $message = Mage::helper('cataloginventory')->__('Available qty to purchase is %s pcs',
                                1 * $stockQty);
                            $cartMessage[$index] = array(
                                "text" => $message,
                                "type" => "error"
                            );

                        } else
                            $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                        $cart->getQuote()->setCustomerId(null);
                    }
                    $cartData = $cart->suggestItemsQty($cartData);
                    $cart->updateItems($cartData)
                        ->save();
                }

                Mage::getSingleton('core/session')->setCartMessage($cartMessage);
                $this->_getSession()->setCartWasUpdated(true);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

}