<?php

namespace Ktpl\General\Model\Order\Email\Sender;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{

	protected function getPaymentHtml(\Magento\Sales\Model\Order $order)
    {
    	$payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodcode =  $method->getCode(); 

        if(in_array($methodcode,['banktransfer','cashondelivery','checkmo'])) 
        {
                $title  = $this->globalConfig->getValue(
                           'payment/'.$methodcode.'/title',
                           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        );
                
                if($methodcode == 'checkmo'){
                    $instructions  = $this->globalConfig->getValue(
                           'payment/'.$methodcode.'/mailing_address',
                           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        );    
                }else{
                    $instructions  = $this->globalConfig->getValue(
                           'payment/'.$methodcode.'/instructions',
                           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        );
                }
                
                return nl2br($title.'<br>'.$instructions);
        }

        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}