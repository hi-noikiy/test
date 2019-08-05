<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysafe\Paysafe\Helper;

class Core extends \Paysafe\Paysafe\Helper\Curl
{
    protected $cardPaymentsUrlTest = 'https://api.test.paysafe.com/cardpayments/v1/accounts/';
    protected $cardPaymentsUrlLive = 'https://api.paysafe.com/cardpayments/v1/accounts/';
    protected $threedsecureUrlTest = 'https://api.test.paysafe.com/threedsecure/v1/accounts/';
    protected $threedsecureUrlLive = 'https://api.paysafe.com/threedsecure/v1/accounts/';
    protected $customerVaultUrlTest = 'https://api.test.paysafe.com/customervault/v1/profiles';
    protected $customerVaultUrlLive = 'https://api.paysafe.com/customervault/v1/profiles';

    /**
     * Get payment URL
     * @param  string $environment
     * @param  string $accountId
     * @return string
     */
    public function getCardPaymentsUrl($environment, $accountId)
    {
    	if ($environment == 'LIVE') {
    		$cardPaymentsUrl = $this->cardPaymentsUrlLive;
		} else {
    		$cardPaymentsUrl = $this->cardPaymentsUrlTest;
		}

        return $cardPaymentsUrl.$accountId.'/auths';
    }

    /**
     * Get customer vault URL
     * @param  string $environment
     * @param  string $accountId
     * @return string
     */
    public function getCustomerVaultUrl($environment)
    {
        if ($environment == 'LIVE') {
            return $this->customerVaultUrlLive;
        } else {
            return $this->customerVaultUrlTest;
        }
    }

    /**
     * Get 3D secure URL
     * @param  string $environment
     * @param  string $accountId
     * @return string
     */
    public function get3dsecureUrl($environment, $accountId)
    {
        if ($environment == 'LIVE') {
            $threedsecureUrl = $this->threedsecureUrlLive;
        } else {
            $threedsecureUrl = $this->threedsecureUrlTest;
        }

        return $threedsecureUrl.$accountId.'/enrollmentchecks';
    }

    /**
     * do payment process
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function doPayment($credentials, $parameters)
    {
        $url = $this->getCardPaymentsUrl($credentials['environment'], $credentials['account_id']);

        $this->_logger->info('do payment URL : '.$url);
        $this->_logger->info(
            'do payment parameters : '.
            json_encode($parameters)
        );

        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }

    /**
     * register payment account
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function getRegisterPayment($credentials, $parameters)
    {
        $url = $this->getCustomerVaultUrl($credentials['environment']);

        $this->_logger->info('do register payment account URL : '.$url);
        $this->_logger->info(
            'do register payment account parameters : '.
            json_encode($parameters)
        );

        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }


    /**
     * change payment account
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function getChangePayment($credentials, $parameters)
    {
        $url = $this->getCustomerVaultUrl($credentials['environment']) . '/' . $credentials['profileId'] . '/cards/' . $credentials['cardId'];

        $this->_logger->info('do change payment account URL : '.$url);
        $this->_logger->info(
            'do change payment account parameters : '.
            json_encode($parameters)
        );

        $request = json_encode($parameters);
        return $this->putRequest($url, $request, $credentials);
    }

    /**
     * delete payment account
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function deleteRegistration($profileId, $cardId, $credentials)
    {
        $url = $this->getCustomerVaultUrl($credentials['environment']) . '/' . $profileId . '/cards/' . $cardId;

        $this->_logger->info('do delete payment account URL : '.$url);

        return $this->sendDeRegistration($url, $credentials);
    }

    /**
     * 3DS enrollment lookup
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function enrollmentLookup($credentials, $parameters)
    {
        $url = $this->get3dsecureUrl($credentials['environment'], $credentials['account_id']);

        $this->_logger->info('3DS enrollment lookup : '.$url);

        $this->_logger->info(
            'do 3DS enrollment lookup : '.
            json_encode($parameters)
        );
        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }

    /**
     * validate 3DS authentication
     * @param  array $credentials
     * @param  array $parameters
     * @return string
     */
    public function validate3dsAuthentication($credentials, $parameters)
    {
        $url = $this->get3dsecureUrl($credentials['environment'], $credentials['account_id']).'/'.$credentials['enrollmentId'].'/authentications';

        $this->_logger->info('3validate 3DS authentication : '.$url);

        $this->_logger->info(
            'do validate 3DS authentication : '.
            json_encode($parameters)
        );
        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }
    /**
     * capture process
     * @param  array $transactionId
     * @param  array $parameters
     * @return string
     */
    public function captureProcess($transactionId, $credentials, $parameters)
    {
        $url = $this->getCardPaymentsUrl($credentials['environment'], $credentials['account_id']) .
            '/'. $transactionId .'/settlements';

        $this->_logger->info('do capture/settle URL : '.$url);
        $this->_logger->info(
            'do capture/settle URL parameters : '.
            json_encode($parameters)
        );
        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }

    /**
     * update payment status
     * @param  string $transactionId
     * @param  string $settlementId
     * @param  array $updateStatusCredentials
     * @return array
     */
    public function updateStatus($transactionId, $settlementId, $refundId, $updateStatusCredentials)
    {
        $this->_logger->info('start update payment status');

        if ($settlementId != '' && $refundId == '') {
            $this->_logger->info('get settlementId : '. $settlementId);
            $url = str_replace('/auths', '', $this->getCardPaymentsUrl($updateStatusCredentials['environment'], $updateStatusCredentials['account_id']));
            $url .='/settlements/'. $settlementId; 
        } elseif ($refundId != '') {
            $this->_logger->info('get refundId : '. $refundId);
            $url = str_replace('/auths', '', $this->getCardPaymentsUrl($updateStatusCredentials['environment'], $updateStatusCredentials['account_id']));
            $url .='/refunds/'. $refundId; 
        } else {
            $this->_logger->info('get transactionId : '. $transactionId);
            $url = $this->getCardPaymentsUrl($updateStatusCredentials['environment'], $updateStatusCredentials['account_id']) . '/'. $transactionId;
        }
        
        $this->_logger->info('get API url : '. $url);
    
        return $this->getPaymentStatus($url, $updateStatusCredentials);
    }

    /**
     * refund process
     * @param  array $settlementId
     * @param  array $parameters
     * @return string
     */
    public function refundProcess($settlementId, $credentials, $parameters)
    {
        $url = str_replace('/auths', '', $this->getCardPaymentsUrl($credentials['environment'], $credentials['account_id']));
        $url .='/settlements/'. $settlementId .'/refunds';

        $this->_logger->info('do refund URL : '.$url);
        $this->_logger->info(
            'do refund URL parameters : '.
            json_encode($parameters)
        );
        $request = json_encode($parameters);
        return $this->sendRequest($url, $request, $credentials);
    }

    /**
     * get the general configurations
     * @param  string $field
     * @param  string $storeId
     * @return string
     */
    public function getGeneralConfig($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $path = 'payment/paysafe_general/' . $field;
        return $this->getScopeConfig()->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * get store id
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
    }

    /**
     * get encryptor
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    protected function getEncryptor()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');
    }

    /**
     * get admin configuration
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected function getScopeConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
    }

    /**
     * get general credentials
     * @return array
     */
    public function getGeneralCredentials()
    {
        $credentials = array();
        $credentials['account_id'] = $this->getGeneralConfig('account_id');
        $credentials['merchant_url'] = $this->getGeneralConfig('shop_url');
        $credentials['merchant_name'] = $this->getGeneralConfig('merchant_name');
        $credentials['api_user'] = $this->getEncryptor()->decrypt($this->getGeneralConfig('api_user'));
        $credentials['api_password'] = $this->getEncryptor()->decrypt($this->getGeneralConfig('api_password'));
        $credentials['recurring'] = $this->getGeneralConfig('recurring');
        $credentials['capture_method'] = $this->getGeneralConfig('capture_method');
        
        return $credentials;
    }

    /**
     * get API key
     * @return string
     */
    public function getApiKey()
    {
        $singleUseUser = $this->getGeneralConfig('singleuse_user');
        $singleUsePassword = $this->getGeneralConfig('singleuse_password');
        $apiKey = base64_encode($singleUseUser.':'.$singleUsePassword);
        
        return $apiKey;
    }
}
