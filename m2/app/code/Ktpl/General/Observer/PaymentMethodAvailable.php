<?php

namespace Ktpl\General\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    private $app_state;
    protected $_scopeConfig;
    protected $_storeManager;
    public function __construct(
    	 \Magento\Framework\App\State $app_state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
    	 $this->app_state = $app_state;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote  = $observer->getEvent()->getQuote();
        $area_code  = $this->app_state->getAreaCode();
        if($area_code != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        {
        
        	if (!$quote)return;

            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $checkResult = $observer->getEvent()->getResult();

            $configPath = 'payment/'.$paymentMethod.'/frontenable';
            $storeId = $this->getStoreId();
            $configValue = $this->getConfigValue($configPath,$storeId);
            $allpaymentMethod=array('banktransfer','free','checkmo');
                   
           if(in_array($paymentMethod, $allpaymentMethod) && $configValue ==0) {
                             
                $checkResult->setData('is_available', false);
               
            }
        }    

    }

  
    public function getConfigValue($config_path,$id)
    {
        return $this->_scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $id);        
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}