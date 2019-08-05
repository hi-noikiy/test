<?php

namespace Ktpl\Wholesaler\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    
    protected $scopeConfig;
    protected $_categoryRepository;
    protected $categoryHelper;
    public $_wholesalerlabel = '';
    protected $jsonHelper;

    /**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Catalog\Helper\Category $categoryHelper, 
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Catalog\Model\CategoryRepository $categoryRepository) 
    {
        $this->scopeConfig = $scopeConfig;
        $this->categoryHelper = $categoryHelper;
        $this->_categoryRepository = $categoryRepository;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * 
     * @return type
     */
    public function Wholesalercategoryurl() {
        $categoryId = $this->scopeConfig->getValue('ktpl_wholesaler_section/wholesale/categoryid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($categoryId == '') {
            $categoryId = 29;
        }
        $categoryObj = $this->_categoryRepository->get($categoryId);
        return $this->categoryHelper->getCategoryUrl($categoryObj);
    }
    /**
     * 
     * @param type $storeid
     * @return type
     */
    public function Enabletierdiscount($storeid = null){
        $configValue = $this->scopeConfig->getValue('ktpl_wholesaler_section/wholesale/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $configValue ? $configValue : false;
    }
    /**
     * 
     * @param type $storeid
     * @return type
     */
    public function enablecustomergroup($storeid = null){
        $cg = $this->scopeConfig->getValue('ktpl_wholesaler_section/wholesale/customergroup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
    public function calculateCustomDiscount($baseGrandTotal,$customergroup=null,$storeid=null) {
        $per = 0;
        if($this->Enabletierdiscount($storeid)){
            if(in_array($customergroup, $this->enablecustomergroup($storeid))){
                $discounts = ($this->jsonHelper->jsonDecode($this->scopeConfig->getValue('ktpl_wholesaler_section/wholesale/wholesaler_discount',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
                foreach ($discounts as $discount) {
                    $temp[$discount['total']] = array($discount['discount'],$discount['name']);
                }
                krsort($temp);
                foreach ($temp as $key => $t) {
                    if ($key <= $baseGrandTotal) {
                        $per = $t[0];
                        $this->_wholesalerlabel = $t[1]; 
                        break;
                    }
                }
            }
        }
        return $per;
    }
    
    public function getTierLabel()
    {
        return __('Tier Discount');
    }
}
