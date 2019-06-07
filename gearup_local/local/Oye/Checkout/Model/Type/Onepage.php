<?php

class Oye_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{

    protected function _isOneStepLayout()
    {
        return Mage::helper('oyecheckout')->isOneStepLayout();
    }

    public function saveBilling($data, $customerAddressId)
    {
        $result = parent::saveBilling($data, $customerAddressId);
        $this->getCheckout()
            ->setStepData('billing_shipping', 'complete', true);
        if(!$this->_isOneStepLayout()){
            return $result;
        }
        if(isset($result['error']) && Mage::app()->getRequest()->isAjax()){
            if($data = Mage::app()->getRequest()->getParam('billing', array())){
                $address = $this->getQuote()->getBillingAddress();
                foreach($data as $attribute => $value){
                    if($value){
                        $address->setData($attribute, $value);
                    }
                }
                $address->save();
                if (!$this->getQuote()->isVirtual()) {
                    $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
                    switch ($usingCase) {
                        case 0:
                            $shipping = $this->getQuote()->getShippingAddress();
                            $shipping->setSameAsBilling(0);
                            break;
                        case 1:
                            $billing = clone $address;
                            $billing->unsAddressId()->unsAddressType();
                            $shipping = $this->getQuote()->getShippingAddress();
                            $shippingMethod = $shipping->getShippingMethod();

                            // Billing address properties that must be always copied to shipping address
                            $requiredBillingAttributes = array('customer_address_id');

                            // don't reset original shipping data, if it was not changed by customer
                            foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                                if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                                    && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                                ) {
                                    $billing->unsetData($shippingKey);
                                }
                            }
                            $shipping->addData($billing->getData())
                                ->setSameAsBilling(1)
                                ->setSaveInAddressBook(0)
                                ->setShippingMethod($shippingMethod)
                                ->setCollectShippingRates(true);
                            $shipping->save();
                            break;
                    }
                }

                $this->getQuote()->collectTotals();
                $this->getQuote()->save();

            }
        }
        return array();
    }

    public function saveShipping($data, $customerAddressId)
    {
        $result = parent::saveShipping($data, $customerAddressId);
        $this->getCheckout()
            ->setStepData('billing_shipping', 'complete', true);
        if(!$this->_isOneStepLayout()){
            return $result;
        }
        if(isset($result['error']) && Mage::app()->getRequest()->isAjax()){
            if($data = Mage::app()->getRequest()->getParam('shipping', array())){
                $address = $this->getQuote()->getShippingAddress();
                foreach($data as $attribute => $value){
                    if($value){
                        $address->setData($attribute, $value);
                    }
                }
                $address->setCollectShippingRates(true);
                $address->save();
                $this->getQuote()->collectTotals()->save();
            }
        }
        return array();
    }


    public function createGiftMessage($giftMessages)
    {
        $quote = $this->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if(is_array($giftMessages)) {
            foreach ($giftMessages as $entityId=>$message) {

                $giftMessage = Mage::getModel('giftmessage/message');

                switch ($message['type']) {
                    case 'quote':
                        $entity = $quote;
                        break;
                    case 'quote_item':
                        $entity = $quote->getItemById($entityId);
                        break;
                    case 'quote_address':
                        $entity = $quote->getAddressById($entityId);
                        break;
                    case 'quote_address_item':
                        $entity = $quote->getAddressById($message['address'])->getItemById($entityId);
                        break;
                    default:
                        $entity = $quote;
                        break;
                }

                if($entity->getGiftMessageId()) {
                    $giftMessage->load($entity->getGiftMessageId());
                }

                if(trim($message['message'])=='') {
                    if($giftMessage->getId()) {
                        try{
                            $giftMessage->delete();
                            $entity->setGiftMessageId(0)
                                ->save();
                        }
                        catch (Exception $e) { }
                    }
                    continue;
                }

                try {
                    $giftMessage->setSender($message['from'])
                        ->setRecipient($message['to'])
                        ->setMessage($message['message'])
                        ->save();

                    $entity->setGiftMessageId($giftMessage->getId())
                        ->save();

                }
                catch (Exception $e) { }
            }
        }
        return $this;
    }

    public function savePayment($data)
    {
        if(!empty($data)) {
            $payment = $this->getQuote()->getPayment();
            $payment->setMethodInstance(null);
            $result = parent::savePayment($data);

            $this->getCheckout()
                ->setStepData('payment_shipping', 'complete', true);
            return $result;
        }
        return false;
    }

    public function saveShippingMethod($shippingMethod)
    {
        if(!empty($shippingMethod)) {
            $result = parent::saveShippingMethod($shippingMethod);
            $this->getCheckout()
                ->setStepData('payment_shipping', 'complete', true);
            $this->getQuote()->setTotalsCollectedFlag(false);
            $this->getQuote()->collectTotals()->save();

            return $result;
        } elseif (empty($this->getQuote()->getShippingAddress()->getShippingMethod())) {
            $shippingMethod = reset(Mage::getModel('checkout/cart_shipping_api')
                ->getShippingMethodsList($this->getQuote()->getId()))['code'];//get first available shipping method
            $result = parent::saveShippingMethod($shippingMethod);
            $this->getCheckout()
                ->setStepData('payment_shipping', 'complete', true);
            $this->getQuote()->setTotalsCollectedFlag(false);
            $this->getQuote()->collectTotals()->save();

            return $result;
        }
        return false;
    }
}