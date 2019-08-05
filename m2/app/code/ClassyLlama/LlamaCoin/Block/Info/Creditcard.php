<?php
namespace ClassyLlama\LlamaCoin\Block\Info;

class Creditcard extends \Magento\Payment\Block\Info\Cc
{
    protected $_template = 'ClassyLlama_LlamaCoin::info/creditcard.phtml';
    protected $_serializer;
    protected $_creditcard;
    protected $_scopeConfig;
    protected $_escaper;
    
    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\Escaper $_escaper,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,    
        \Magento\Framework\Serialize\SerializerInterface $serializer,  
        \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard,    
        array $data = []
    ) {
        parent::__construct($context,$paymentConfig, $data);
        $this->_serializer = $serializer;
        $this->_creditcard = $creditcard;
        $this->_scopeConfig = $scopeConfig;
        $this->_escaper=$_escaper;
        
    }
    
//    protected function _construct()
//    {
//        parent::_construct();
//        $this->setTemplate('ClassyLlama_LlamaCoin::info/creditcard.phtml');
//    }

    /**
     * Get the used card information
     *
     * @return Mage_Payment_Model_Info|void
     */
    public function getCardInfo()
    {
        $info = $this->getInfo();
        $profileId = false;
        if($info->getOrder() && $info->getOrder()->getPayment()) {
            $orderInfo = $info->getOrder()->getPayment()->getAdditionalInformation('order');
            if(isset($orderInfo['optimal_profile_id']) && $orderInfo['optimal_profile_id'] > 0)
            {
                $profile = $this->_creditcard->create()
                    ->load($orderInfo['optimal_profile_id']);
            }
        }

        if(isset($profile)) {

            $info = array(
                'name' => $profile->getCardHolder(),
                'card_type' => $profile->getCardNickname(),
                'card_number' => $profile->getLastFourDigits(),
                'card_exp' => $profile->getCardExpiration(),
            );

            return $info;
        }

        $info = array(
            'name' => $info->getCcOwner(),
            'card_type' => $this->getCcTypeName(),
            'card_number' => $info->getCcLast4(),
            'card_exp' => $info->getCcExpMonth() . '/' . $info->getCcExpYear(),
        );

        return $info;
    }
    
    public function cardData() {
        $skip3d = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allowInterac = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/allow_interac', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $info = $this->getCardInfo();

        // if 3D-Secure check is enabled, only show the Payment Method Title
        if ((!$skip3d || $allowInterac) && empty($info['card_number'])) {
            echo $this->_scopeConfig->getValue('payment/classyllama_llamacoin/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return;
        }
        echo __('Credit Card Type: %1 <br />' , $this->_escaper->escapeHtml(ucwords($info['card_type'])));
        echo __('Credit Card Number: xxxx-%1 <br />', $this->_escaper->escapeHtml($info['card_number']));
        echo __('Expiration Date: %1', $this->_escaper->escapeHtml($info['card_exp']));
        
    }
}