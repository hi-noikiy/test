<?php

namespace ClassyLlama\LlamaCoin\Block\Form;
class Creditcard extends \Magento\Payment\Block\Form\Cc
{
    //protected $_template = 'ClassyLlama_LlamaCoin::form/creditcard.phtml';
    public $_profiles = null;
    protected $_creditcard;
    protected $_helper;
    protected $_cart;
    protected $_customerSession;
    protected $_scopeConfig;
    
    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard
     * @param \ClassyLlama\LlamaCoin\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard,  
        \ClassyLlama\LlamaCoin\Helper\Data $helper,  
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,    
        \Magento\Checkout\Model\CartFactory $cart,    
        \Magento\Customer\Model\Session $customerSession,    
        array $data = []
    ) {
        parent::__construct($context,$paymentConfig, $data);
        $this->_creditcard = $creditcard;
        $this->_helper = $helper;
        $this->_cart = $cart;
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
    }
    
    

    /**
     * Check for the existing optimal profiles
     *
     * @return bool
     */
    public function hasOptimalProfiles()
    {
        $session = $this->_customerSession;
        $customerId = $session->getId();
        if (isset($customerId))
        {
            $merchCustId = $this->_helper->getMerchantCustomerId($customerId);
            if (!$merchCustId) {
                return false;
            }

            $merchantCustomerId = $merchCustId['merchant_customer_id'];

            $profiles = $this->_creditcard->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('merchant_customer_id', $merchantCustomerId)
                ->addFieldToFilter('is_deleted', false);

            if($profiles->count() >= 1)
            {
                $this->_profiles = $profiles;
                return true;
            }
        }

        $this->_profiles = array();

        return false;
    }

    /**
     * Check if profile saving is enabled
     *
     * @return bool
     */
    public function canSaveProfiles()
    {
        $session = $this->_customerSession;
        $profilesEnabled = $this->_scopeConfig->getValue('payment/classyllama_profiles/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $checkoutMethod = $this->_cart->getQuote()->getCheckoutMethod();
        if (($session->getCustomerId() || $checkoutMethod == 'register') && $profilesEnabled)
        {
            return true;
        }
        return false;
    }

    public function skip3D()
    {
        return $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function allowInterac()
    {
        return $this->_scopeConfig->getValue('payment/classyllama_llamacoin/allow_interac', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
