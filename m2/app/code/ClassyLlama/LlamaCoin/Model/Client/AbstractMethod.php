<?php

namespace ClassyLlama\LlamaCoin\Model\Client;

class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod {

    protected $_apiUrl = null;
    protected $_encryptor;
    protected $_scopeConfig;
    
    /**
     * 
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []    
    
    ){
        $this->_encryptor = $encryptor;
        $this->_scopeConfig = $scopeConfig;
        $this->_apiUrl = $this->_getApiUrl();
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,    
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct() {
        
    }

    /**
     * Get the API url based on the configuration
     *
     * @return string
     */
    protected function _getApiUrl() {
        if ($this->_scopeConfig->getValue('payment/classyllama_llamacoin/mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 'development') {
            $url = 'https://api.test.netbanx.com';
        } else {
            $url = 'https://api.netbanx.com';
        }
        return $url;
    }

    /**
     * @return bool|string
     *
     */
    protected function _getUserPwd() {
        try {
            if(isset($_POST['order']['currency']) && $_POST['order']['currency'] =='USD') {
                $user = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/classyllama_llamacoin/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,3));
                $pwd = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/classyllama_llamacoin/trans_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,3));
            } else {
                $user = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/classyllama_llamacoin/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $pwd = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/classyllama_llamacoin/trans_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            }

            if ($user != '' && $pwd != '') {
                return $user . ':' . $pwd;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException\Exception("Something went wrong with your api credentials");
            }
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/optipayment.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e->__toString());
            return false;
        }
    }

    /**
     * 
     * @param type $curl
     */
    protected function _checkCurlVerifyPeer($curl) {
        if ($this->_scopeConfig->getValue('payment/classyllama_llamacoin/mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 'development') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        }
    }

}
