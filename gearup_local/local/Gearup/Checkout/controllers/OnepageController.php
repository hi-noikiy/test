<?php
require_once Mage::getModuleDir('controllers', 'Oye_Checkout') . DS . 'OnepageController.php';

class Gearup_Checkout_OnepageController extends Oye_Checkout_OnepageController {

    public function getUpdatedCartSummeryAction() {
        Mage::getSingleton('core/session')->setUpdateAction('shipping_payment');
        $this->_saveShippingMethod();
        $this->_savePayment();
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/ajax'));
    }

    protected function getCartShippingInfo($type) {
        return $this->getLayout()
            ->createBlock('checkout/cart_shipping')
            ->setTemplate('checkout/cart/'.$type.'.phtml')
            ->toHtml();
    }

    protected function getMessages() {
        $messages = Mage::getSingleton('checkout/session')->getMessages(true);
        if (!empty($messages)) {
            return $this->getLayout()->getMessagesBlock()->setMessages($messages)->getGroupedHtml();
        }
    }

    protected function getTotals($quote, $totalsBlock = 'gearup_payfort/checkout_cart_totals') {
        $result = array();

        $result['grandTotal'] = Mage::helper('checkout')->formatPrice($quote->getGrandTotal());
        $result['html'] = $this->getLayout()
            ->createBlock($totalsBlock)
            ->setTemplate('checkout/cart/totals.phtml')
            ->toHtml();

        return $result;
    }

    protected function getCoupon() {
        $coupon = array();
        $couponFormHtml = $this->getLayout()
            ->createBlock('checkout/cart_coupon')
            ->setTemplate('checkout/cart/coupon.phtml')
            ->toHtml();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $style = 'min-height: 300px;';
        } else {
            $style = '';
        }

        $resultHtml = '';
        $quote2 = Mage::getSingleton('checkout/session')->getQuote();
        $discountTotal = 0;
        $discountPercentage = 0;
        foreach ($quote2->getAllItems() as $item) {
            $discountTotal += $item->getDiscountAmount();
            $discountPercentage += $item->getDiscountPercent();
        }

        $coupon['discount'] = $discountTotal;

        if(Mage::getDesign()->getPackageName() == "aw_mobile3") {
            $coupon['popup'] = $this->getLayout()
                ->createBlock('checkout/cart_coupon')
                ->setTemplate('checkout/cart/coupon.phtml')
                ->toHtml();

            if (strlen($quote2->getCouponCode()) > 0):
                $resultHtml .= '<div class="discount-code-is">
                                    <div class="first-info-coupon">
                                        <p class="discount-code-active-ch">
                                            <span>'.$this->__('Discount code').': </span>
                                            <span>'. $quote2->getCouponCode() .'</span>
                                        </p>
                                    </div>
                                    <div class="second-info-coupon">                    
                                        <p>
                                            <span>'.$this->__('Discount').'</span>
                                            <span>-<span class="price">'. Mage::helper('core')->formatPrice($discountTotal, true, false) .'</span></span>
                                        </p>
                                        <a href="#" onclick="discountForm.submit(true)" title="Remove item" class="btn-remove">Remove item</a>
                                    </div>
                                </div><script>var discountForm = new VarienForm("discount-coupon-form");</script>';
            else:
                $resultHtml .= '<div class="discount-code-is"><div class="do-you-have-discount-code">'.$this->__('Do you have a'). '&ensp;<button class="cart__apply-discount  js-open-panel" data-open-panel="discount" id="discount_container_link" type="button">'. $this->__('discount code'). '</button>?</div></div>';
            endif;
        } else {
            if (strlen($quote2->getCouponCode()) > 0):
                $resultHtml .= '<tr class="discount-form-tr discounted" id="discount-form-tr">
                       <td colspan="2" class="oye-left-container discount-form-container">' . $couponFormHtml . '</td>
                       <td class="discount-status a-center"></td>
                       <td></td>';
                if ($discountTotal):
                    $resultHtml .= '<td class="discount-form-last-td subtotal-cart-price a-right">
             <span class="cart-price">
             <span class="price">'
                        . Mage::helper('core')->formatPrice($discountTotal, true, false) . '</span></span></td>';
                else:
                    $resultHtml .= '<td class="discount-form-last-td subtotal-cart-price a-right"></td>';
                endif;
                $resultHtml .= '<td><a href="#" onclick="discountForm.submit(true)" title="' . $this->__('Remove item') . '" class="btn-remove">' . $this->__('Remove item') . '</a></td>
                </tr>
               <tr id="discount-appy-tr">
                    <td colspan="6" class="discount-form-last-td subtotal-cart-price">
                        <div class="totals totals-container">
                        
                        </div>
                    </td>
                </tr>';
            else:
                $resultHtml .= '<tr class="discount-form-tr" id="discount-form-tr">
                    <td colspan="2" class="oye-left-container discount-form-container">' . $couponFormHtml . '</td>
                    <td></td>
                        <td class="discount-status a-center"><span style="display:none;">' . $this->__('no discount') . '</span></td>
                        <td colspan="4" class="discount-form-last-td subtotal-cart-price">
                        <div class="totals totals-container" style="' . $style . '">
                     
                        </div>
                    </td>                   
                </tr>';
            endif;
        }

        $coupon['html'] = $resultHtml;
        return $coupon;

    }

    protected function getUsepoints() {
        return $this->getLayout()
            ->createBlock('rewards/checkout_cart_usepoints', 'usepoints')
            ->setTemplate('mst_rewards/checkout/cart/usepoints.phtml')
            ->toHtml();
    }

    protected function getSidebarDiscount($quote)
    {
        $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        if ($discount && Mage::getDesign()->getPackageName() != "aw_mobile3") {
            $html = '<div class="shipping-to-do" id="discount_discount">
                <label for="country" class="required">Discount</label>
                <span class="price">' .
                Mage::app()->getStore()->formatPrice($discount) .
                '</span>
            </div>';
        } else {
            $html = '';
        }

        return $html;
    }

    protected function getSidebarCod($quote)
    {
        if ($quote->getBaseCodFee()) {
            $html = '<div class="shipping-to-do" id="cash_on_delivery">
                <label for="country" class="required">Cash on Delivery fee</label>
                <span class="price">'
                . $quote->getStore()
                    ->convertPrice($quote->getBaseCodFee() + $quote->getBaseCodTaxAmount(), true) .
                '</span>
            </div>';
        } else {
            $html = '';
        }

        return $html;
    }




    protected function getSaveForLater() {
        return $this->getLayout()
            ->createBlock('saveforlater/items', 'saveforlater.items')
            ->setTemplate('redstage_saveforlater/items.phtml')
            ->toHtml();
    }

    protected function getAvailableShipping() {
        return $this->getLayout()
            ->createBlock('checkout/onepage_shipping_method_available', 'checkout.onepage.shipping_method.available')
            ->setTemplate('checkout/onepage/shipping_method/available.phtml')
            ->toHtml();
    }

    protected function getAvailablePayment() {
        return $this->getLayout()
            ->createBlock('checkout/onepage_payment_methods', 'checkout.payment.methods')
            ->setTemplate('oye/checkout/horizontal/onepage/payment/methods.phtml')
            ->toHtml();
    }

    protected function getMinicart()
    {
        $minicart = array();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $quoteItems = $quote->getAllItems();
        $subtotalInclTax = 0;
        foreach ($quoteItems as $item) {
            if ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $subtotalInclTax += $item->getRowTotalInclTax();
            }
        }
        if ($subtotalInclTax > 0) {
            $minicart['price'] = Mage::helper('checkout')->formatPrice($subtotalInclTax);
        }


        $count = Mage::helper('checkout/cart')->getSummaryCount();
        if($count == null){
            $count = 0;
        }
        $minicart['count'] = $count;

        $minicart['html'] = $this->getLayout()
            ->createBlock('checkout/cart_sidebar')
            ->setTemplate('checkout/cart/sidebar.phtml')
            ->toHtml();

        return $minicart;
    }


//<block type="checkout/cart_shipping" name="shipping_info" as="shipping_info" template="checkout/cart/shipping_info.phtml"/>

    protected function getCartItemHtml($quote, $isSidebar = false)
    {
        $cartItems = array();
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                switch ($item->getProductType()) {
                    case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                        $template = $this->getLayout()
                            ->createBlock('checkout/cart_item_renderer');
                        break;
                    case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                        $template = $this->getLayout()
                            ->createBlock('checkout/cart_item_renderer_grouped');
                        break;
                    case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                        $template = $this->getLayout()
                            ->createBlock('checkout/cart_item_renderer_configurable');
                        break;
                    default:
                        $template = $this->getLayout()
                            ->createBlock('checkout/cart_item_renderer');
                        break;
                }
                if($isSidebar) {
                    $cartItems[] = $template->setTemplate('oye/checkout/horizontal/onepage/progress/cart/sidebar/default.phtml')
                        ->setItem($item)->toHtml();

                } else {
                    $cartItems[] = $template->setTemplate('checkout/cart/item/default.phtml')
                        ->setItem($item)->toHtml();
                }
            }
        }
        if (empty($cartItems)) {
            return $this->getLayout()
                ->createBlock('checkout/cart')
                ->setTemplate('redstage_saveforlater/cart_empty_ajax.phtml')
                ->toHtml();
        } else {
            return $cartItems;
        }
    }


    public function ajaxAction(){

        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->norouteAction();
            return;
        }
        $output = array();

        $quote = Mage::helper('checkout/cart')->getQuote();
        $quote->collectTotals()->save();


        switch(Mage::getSingleton('core/session')->getUpdateAction()) {
            case 'cart_remove':
            case 'update_qty':
                $output['discount'] = self::getSidebarDiscount($quote);
                $output['cod'] = self::getSidebarCod($quote);
                $output['messages'] = self::getMessages($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['totals'] = self::getTotals($quote);
                $output['coupon']  = self::getCoupon();
                $output['usePoints'] = self::getUsepoints();
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['minicart'] = self::getMinicart();
                $output['sidebarItems'] = self::getCartItemHtml($quote, true);
            case 'saveforlater':
                $output['cod'] = self::getSidebarCod($quote);
                $output['discount'] = self::getSidebarDiscount($quote);
                $output['messages'] = self::getMessages($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['totals'] = self::getTotals($quote);
                $output['coupon']  = self::getCoupon();
                $output['usePoints'] = self::getUsepoints();
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['minicart'] = self::getMinicart();
                $output['sidebarItems'] = self::getCartItemHtml($quote, true);
                $output['saveForLater'] = self::getSaveForLater();
                break;
            case 'saveforlater_remove':
            case 'saveforlater_qty_change':
                $output['items'] = self::getCartItemHtml($quote);
                $output['messages'] = self::getMessages($quote);
                $output['saveForLater'] = self::getSaveForLater();
                break;
            case 'shipping_change':
                $output['cod'] = self::getSidebarCod($quote);
                $output['messages'] = self::getMessages($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['sidebarItems'] = self::getCartItemHtml($quote, true);
                $output['minicart'] = self::getMinicart();
                $output['shippingInfo'] = self::getCartShippingInfo('shipping_info');  //2step
                $output['shippingCart'] = self::getCartShippingInfo('shipping');
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['totals'] = self::getTotals($quote);
                $output['coupon']  = self::getCoupon();
                $output['usePoints'] = self::getUsepoints();
                break;
            case 'coupon':
                $output['cod'] = self::getSidebarCod($quote);
                $output['discount'] = self::getSidebarDiscount($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['messages'] = self::getMessages($quote);
                $output['shippingInfo'] = self::getCartShippingInfo('shipping_info');  //2step
                $output['shippingCart'] = self::getCartShippingInfo('shipping');
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['totals'] = self::getTotals($quote);
                $output['coupon']  = self::getCoupon();
                $output['usePoints'] = self::getUsepoints();
                break;
            case 'rewards':
                $output['cod'] = self::getSidebarCod($quote);
                $output['discount'] = self::getSidebarDiscount($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['messages'] = self::getMessages($quote);
                $output['shippingInfo'] = self::getCartShippingInfo('shipping_info');  //2step
                $output['shippingCart'] = self::getCartShippingInfo('shipping');
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['totals'] = self::getTotals($quote);
                $output['coupon']  = self::getCoupon();
                $output['usePoints'] = self::getUsepoints();
                break;
            case 'shipping_payment':
                $output['cod'] = self::getSidebarCod($quote);
                $output['items'] = self::getCartItemHtml($quote);
                $output['messages'] = self::getMessages($quote);
                $output['shippingInfo'] = self::getCartShippingInfo('shipping_info');  //2step
                $output['availableShipping'] = self::getAvailableShipping();
                $output['availablePayment'] = self::getAvailablePayment();
                $output['totals'] = self::getTotals($quote, 'gearup_payfort/checkout_progress_totals');
                break;
            default:  //remove basket



        }
        Mage::getSingleton('core/session')->unsUpdateAction();
        Mage::getSingleton('core/session')->unsCartMessage();
        $output['items'] = Mage::helper('ampreorder/html')->injectCartPreorderNoteAjax($output['items']);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
        return $output;


        /* Load the block belonging to the current step */
        //$messages = self::getMessages($quote);
//        $items = self::getCartItemHtml($quote);//1st
//        $shippingInfo = self::getCartShippingInfo('shipping_info');  //2step
//        $shippingCart = self::getCartShippingInfo('shipping');  //1step
//        $totals = self::getTotals($quote);//1st
//        $coupon = self::getCoupon();//1st
//        $usePoints = self::getUsepoints();//1st
//        $saveForLater = self::getSaveForLater();//1st
//        $availableShipping = self::getAvailableShipping();//2st
//        $availablePayment = self::getAvailablePayment();//2st
//        $minicart = self::getMinicart();//header
//        $sidebarItems = self::getCartItemHtml($quote, true);//2,3 st
    }

    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->_prepareDataJSON($result);
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            $gotoSection = 'review';

            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection($gotoSection);
            }
            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        } elseif($result['error']) {
            $result['redirect'] = '/checkout/onepage/index/goto/review';
        } else {
            $result['redirect'] = '/checkout/onepage/success';
        }
        Mage::getSingleton('checkout/type_onepage')->getCheckout()->setCartCouponCode(null);
        $this->_prepareDataJSON($result);
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $register = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);

            if (strpos($data['firstname'], ' ') !== false) {

                $fullname = explode(' ',$data['firstname']);
                $data['firstname'] = $fullname[0];
                $data['lastname'] = $fullname[1];
            }
            if(!isset($data['differet_billing'])){
                //$data['use_for_shipping'] = 1;
                $_POST['billing'] = $data;
                if (empty($data['customer_password'])) {
                    $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
                } elseif (!empty($register['create_a_registration'])) {
                    $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
                }
                $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
                $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
            }else
                $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            if(isset($data['event_country_change'])){
                if (isset($data['country_id'])) {
                    $address =$this->getOnepage()->getQuote()->getShippingAddress();
                    $this->getOnepage()->getQuote()->getShippingAddress()->setData('country_id', $data['country_id'])->save();
                    $address->setCollectShippingRates(true);
                    $address->save();
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                }
                unset($result['error']);
                $this->_prepareDataJSON($result);
                return;
            }
            if (!isset($result['error'])) {
                //$this->loadLayout('checkout_onepage_review_horizontal');
                //$result['goto_section'] = 'review';
                $result['duplicateShippingInfo'] = true;
                $this->loadLayout('checkout_onepage_review_horizontal');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
                //$result['duplicateBillingInfo'] = 'true';
            }
            $this->_prepareDataJSON($result);
        }
    }
}
