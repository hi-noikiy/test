<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Payment extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
{
    /**
     * Sales Qoute Billing Address instance
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;

    /**
     * Customer Taxvat Widget block
     *
     * @var Mage_Customer_Block_Widget_Taxvat
     */
    protected $_taxvat;

    protected $_methodCode = null;

    public function  __construct() {
        $this->getCheckout()->setStepData('payment', array(
            'label'     => Mage::helper('checkout')->__('Payment Information'),
            'is_show'   => $this->isShow()
        ));
		parent::__construct();
    }
	
	public function getMethodCodeAllow(){
		if(is_null($this->_methodCode)){
			$this->_methodCode = array(
            'cc'=>'apiios/api2_checkout_onepage_output_payment_form_cc',
            'onepay'=>'apiios/api2_checkout_onepage_output_payment_form_onepay',
            'onepayquocte'=>'apiios/api2_checkout_onepage_output_payment_form_onepay',
            'cashondelivery' => ''
        );
		}
		return $this->_methodCode;
	}

    /**
     * Check and prepare payment method model
     *
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        if (!$method || !$method->canUseCheckout()) {
            return false;
        }
        return parent::_canUseMethod($method);
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (float)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Retrieve availale payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if (is_null($methods)) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = Mage::helper('apiios/payment')->getStoreMethods($store, $quote);

            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();

            foreach ($methods as $key => $method) {
                if ($this->_canUseMethod($method)
                    && ($total != 0
                        || $method->getCode() == 'free'
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))
                    /*&& in_array($method->getCode(),array('paypal_express','cc'))*/) {
                    //$this->_assignMethod($method);
                } else {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    public function toArrayFields() {
        $methods = $this->getMethods();
		$codesAllow = $this->getMethodCodeAllow();
        $helper = Mage::helper('payment');
        $result = array();
		foreach($methods as $m){
            if(in_array($m->getCode(),array_keys($codesAllow))){
				$data = array(
                    'code'  =>  $m->getCode(),
                    'title' =>  $m->getTitle()
                );
                $output = $this->getPaymentOutput($m->getCode());
				if($output != false)
                    $data['fields'] = $output->toArrayFields();
				$result[] = $data;	
            }
        }

        $final = array(
            'title' =>  $helper->__('Payment Information'),
            'prefix'=>  'payment',
            'fields'=>  array(
                array(
                    'name'      =>  'payment_method',
                    'type'      =>  'radio_group',
                    'methods'   =>  $result
                )
            )
        );
        return $final;
    }

    public function getPaymentOutput($code){
		$methods = $this->getMethodCodeAllow();
        if(isset($methods[$code]) && !empty($methods[$code]))
            return Mage::getSingleton($methods[$code])->setData('method',Mage::helper('payment')->getMethodInstance($code));
        return false;
    }
}
?>