<?php

namespace Ktpl\Paymentcharge\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    
    protected $scopeConfig;

    /**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) 
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * 
     * @param type $storeid
     * @return type
     */
    public function Enablepaymentcharge($storeid = null){
        $configValue = $this->scopeConfig->getValue('ktpl_wholesaler_section/payment/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $configValue ? $configValue : false;
    }
    /**
     * 
     * @param type $storeid
     * @return type
     */
    public function enablecustomergroup($storeid = null){
        $cg = $this->scopeConfig->getValue('ktpl_wholesaler_section/payment/customergroup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customergroup = explode(',', $cg);
        return $customergroup;
    }
    
    /**
     * 
     * @param type $baseGrandTotal
     * @param type $customergroup
     * @param type $storeid
     * @return type
     */
    public function calculatePaymentcharge($baseGrandTotal,$customergroup=null,$storeid=null) {
        $per = 0;
        if($this->Enablepaymentcharge($storeid)){
            if(in_array($customergroup, $this->enablecustomergroup($storeid))){
                $type = $this->scopeConfig->getValue('ktpl_wholesaler_section/payment/payment_charge_type',\Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
                $discounts = $this->scopeConfig->getValue('ktpl_wholesaler_section/payment/payment_charge',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($type==1){
                    $per =  $baseGrandTotal * $discounts / 100;
                } else if ($type ==2){
                    $per = $discounts;
                }
            }
        }
        return $per;
    }
    
    public function getchargeLabel()
    {
        return __('Payment Charge');
    }
    
}
