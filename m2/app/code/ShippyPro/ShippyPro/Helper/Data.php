<?php

namespace ShippyPro\ShippyPro\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_apiUrl = 'https://www.shippypro.com/api';

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    
    public function getStoreId()
    {
        return 0;
        
        /*if (empty($this->storeId)) {
            $this->storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->storeId;*/
    }

    public function apiRequest($params)
    {
        $apiKey = $this->getScopeConfig()->getValue('carriers/shippypro/apikey',\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->getStoreId());

        $data = json_encode($params);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );                
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);    
        curl_setopt($curl, CURLOPT_USERPWD, $apiKey);
        curl_setopt($curl, CURLOPT_URL, $this->_apiUrl);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($curl); 
        curl_close($curl);

        return json_decode($json);
    }
}