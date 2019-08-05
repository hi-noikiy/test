<?php
namespace Ktpl\AddonPopup\Helper;

use Magento\Store\Model\Group;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;

    protected $scopeConfig;

    protected $customerSession;


    public function __construct(
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession

    )
	{
    	$this->_storeManager = $storeManager;
    	$this->scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
	}

	

    /**
     * Check if product is already in cart
     *
     * @param int $productId
     * @return bool
     */
    public function isProductAlreadyInCart($productId)
    {
        
        $quote = $this->_customerSession->getCheckoutSession()->getQuote();
        $items = $quote->getAllItems();

        $isInCart = false;
        foreach($items as $item) {
            if ($item->getProductId() == $productId) {
                $isInCart = true;
                break;
            }
        }

        return $isInCart;
    }
   

    public function getCurrentStoreId() 
    {
        return $this->_storeManager->getStore()->getStoreId(); 
    }

      public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    public function getStoreUrl($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseUrl();
    }

}
?>