<?php
class CheckoutApi_ChargePayment_Model_Method_Creditcard extends CheckoutApi_ChargePayment_Model_Method_Abstract
{
    /**
    * Is this payment method a gateway (online auth/charge) ?
    */
    protected $_isGateway = true;
    protected $_canUseInternal = true;
    protected $_code = 'creditcard';

    protected $_formBlockType = 'checkoutapi_chargePayment/form_creditcard';
   // protected $_infoBlockType = 'checkoutapi_chargePayment/info_creditcard';

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param array $extraConfig
     * @return mixed
     */


    protected function _createCharge(Varien_Object $payment,$amount,$extraConfig = array())
    {
        /** @var CheckoutApi_Client_ClientGW3  $Api */
        
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $order  = $payment->getOrder();
        $amount = $order->getGrandTotal();
        $amount = $Api->valueToDecimal($amount, $order->getOrderCurrencyCode());
        $config = parent::_createCharge($payment,$amount,$extraConfig);  
        $cardToken = $payment->getAdditionalInformation('cko_card_token');
        if (!empty($cardToken)){
          $config['postedParam'] = array_merge ( array('cardToken' => $cardToken) , $config['postedParam'] );
          $chargeResponse = $Api->createCharge($config);
        } else {
          $config['authorization'] = $this->getConfigData('privatekey');
          $config['paymentToken'] = $payment->getAdditionalInformation('cko_cc_paymenToken');
          $chargeResponse = $Api->verifyChargePaymentToken($config); 
        }
        
        if ($Api->getExceptionState()->hasError()){
          Mage::log($Api->getExceptionState()->getErrorMessage(), null, 'ckoerror.log');
          Mage::log( $config,Zend_Log::DEBUG,'ckotoken.log');
        }
        
        return $chargeResponse;
    }
    protected function _getCcCodeType($paymentMethod)
    {
        $type = 'OT';
        foreach (Mage::getSingleton('checkoutapi_chargePayment/config')->getCcTypes() as $code => $name) {
            if( strtolower($paymentMethod) == strtolower($name)){
                $type = $code;
            }
        }

        return $type;
    }

    protected  function _isRedirect($payment)
    {
        //$redirect3dUrl = Mage::getSingleton('customer/session')->getRedirectUrl();
        $cko_redirectUrl = $payment->getAdditionalInformation('cko_redirectUrl');
        if($cko_redirectUrl ) {
            return true;
        }
        return false;
    }

    protected function _capture ( Varien_Object $payment , $amount )
    {
        $postedVal = Mage::app()->getRequest()->getParams();
        $extraConfig = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE ,
            'autoCapTime' => $this->getConfigData ( 'auto_capture_time' )
        );
        $this->setPendingState($payment);
        if(!$this->_isRedirect($payment) && !isset($postedVal['cko-payment-token'])) {
            $this->_placeOrder($payment, $amount, "Payment has been successfully captured for Transaction ", $extraConfig);
        }
    }

    /**
     * Authorize payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return CheckoutApi_ChargePayment_Model_Method_Creditcard
     */

    public function authorize ( Varien_Object $payment , $amount )
    {
        $postedVal = Mage::app()->getRequest()->getParams();
        if ( !$this->canAuthorize () ) {
            Mage::throwException ( Mage::helper ( 'payment' )->__ ( 'Authorize action is not available.' ) );
        } else {
            $extraConfig = array (
                'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH ,
                'autoCapTime' => 0
            );
            
            $this->setPendingState($payment);
            if(!$this->_isRedirect($payment) && !isset($postedVal['cko-payment-token'])) {
                 $this->_placeOrder ( $payment , $amount , "Payment has been successfully authorize for Transaction " , $extraConfig );
            }
        }

        return $this;
    }
    public function validate()
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            Mage::throwException(Mage::helper('payment')->__('Selected payment type is not allowed for billing country.'));
        }
        return $this;
    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $details['cko_cc_paymenToken'] = $data->getData('cko_cc_paymenToken');
        $details['cko_card_token'] = $data->getData('cko_card_token');
        $details['cko_redirectUrl'] = $data->getData('cko_redirectUrl');
        $info->setAdditionalInformation('cko_cc_paymenToken',$details['cko_cc_paymenToken']);
        $info->setAdditionalInformation('cko_card_token',$details['cko_card_token']);
        $info->setAdditionalInformation('cko_redirectUrl',$details['cko_redirectUrl']);
        $info->setAdditionalData(serialize($details));
        $info->setPaymentToken( $details['cko_cc_paymenToken']);
        return $this;
    }
    
    public function getOrderPlaceRedirectUrl()
    {
        $info = $this->getInfoInstance();
        $chargeMode = Mage::getSingleton('core/session')->getRespondChargeMode(); 
        $toReturn = null;
        if($chargeMode == 2){
          $toReturn = Mage::getSingleton('customer/session')->getRedirectUrl();
        }

        $cko_redirectUrl = $info->getAdditionalInformation('cko_redirectUrl');

        if($cko_redirectUrl){
            $dataOrder= $this->getCentinelValidationData();
            
            $block = Mage::getBlockSingleton('checkoutapi_chargePayment/form_creditcard')->getPaymentTokenResult($dataOrder->getOrderNumber());
            $paymentToken = $block['token'];
            
            $cko_redirectUrl = Mage::helper('checkoutapi_chargePayment')->replace_between($cko_redirectUrl, 'paymentToken=', '&', $paymentToken);
            $toReturn = $cko_redirectUrl.'&trackId='.$dataOrder->getOrderNumber();
        }

        return $toReturn;
    }


}