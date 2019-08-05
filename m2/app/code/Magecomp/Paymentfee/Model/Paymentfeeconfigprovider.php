<?php
namespace Magecomp\Paymentfee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Paymentfeeconfigprovider implements ConfigProviderInterface
{
    
    protected $storeManager;
    protected $scopeConfig;
    
    public function __construct(
       StoreManagerInterface $storeManager,
       ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $store = $this->getStoreId();
		
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$checkoutsession = $om->get('Magento\Checkout\Model\Session');
		$quotedata = $checkoutsession->getQuote();
		$paymentfeelabel = $quotedata->getMcPaymentfeeDescription(); 
        $config = [
            'paymentfee' => [
                'getinfo' => [
					'paymentfeelabel' => $paymentfeelabel
                ]
            ]
        ];

        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }
}