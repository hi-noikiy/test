<?php
class EM_Apiios_Model_Api2_Checkout_Coupon_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
	const API_ADD_COUPON = 'API add coupon to cart';

	protected function _getCart()
    {
        return Mage::getSingleton('apiios/api2_cart')->setStore($this->_getStore());
    }

	protected function _getSession()
    {
        return Mage::getSingleton('apiios/api2_checkout_session')->setStore($this->_getStore());
    }

	protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

	protected function _saveSoupon(array $data){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
		/**
         * No reason continue with empty shopping cart
         */
		$check	=	'error';
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $check	=	'error';
        }

        $couponCode = (string) $data['coupon_code'];
        if ($data['remove'] == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $check	=	'error';
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
					$check	=	'success';
                    $message	=	Mage::Helper('apiios')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                }
                else {
                    $message	=	Mage::Helper('apiios')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                }
            } else {
				$check	=	'success';
                $message	=	Mage::Helper('apiios')->__('Coupon code was canceled.');
            }

        } catch (Mage_Core_Exception $e) {
			$check	=	'error';
			$message	=	$e->getMessage();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::Helper('apiios')->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $result['result']['check']	=	$check;
        //$result['result']['message']	=	$message;
        $result['result']['coupon_code']	=	$couponCode;
        $result['result']['totals']	=	$this->_getCart()->totals();

		$this->_successMessage(
			$message,
			Mage_Api2_Model_Server::HTTP_OK,
			$result
		);
	}

    /**
     * Save coupon (for ios)
     *
     * @param array $data
     * @return string|void
     */
    protected function _create($data){
        $this->_saveSoupon($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Save coupon (for android)
     *
     * @param array $data
     */
    protected function _multiCreate($data){
        $this->_saveSoupon($data[0]);
    }
}
?>