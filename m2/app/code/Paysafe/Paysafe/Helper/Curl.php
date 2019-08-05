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

class Curl extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $http;
    protected $_logger;

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Paysafe\Paysafe\Logger\logger                $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Paysafe\Paysafe\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->http = $curlFactory->create();
        $this->_logger = $logger;
    }

    /**
     * get a response from the gateway
     * @param  boolean $isJsonDecoded
     * @return string | boolean
     */
    protected function getResponse($isJsonDecoded = false)
    {
        $response = $this->http->read();
        $responseCode = \Zend_Http_Response::extractCode($response);
        $responseBody = \Zend_Http_Response::extractBody($response);
        $this->http->close();
        $this->_logger->info(
                'response from gateway : '.
                json_encode($responseCode)
            );
        if ($responseCode != 500 || $responseCode != 502 || $responseCode != 404) {
            $this->_logger->info(
                'response from gateway : '.
                json_encode($responseBody)
            );
            if ($isJsonDecoded) {
                return json_decode($responseBody, true);
            }
            return $responseBody;
        }
        return false;
    }

    /**
     * send request to the gateway
     *
     * @param string $url
     * @param string $request
     * @param boolean $credentials
     * @param boolean $isJsonDecoded
     * @return string | boolean
     */
    protected function sendRequest($url, $request, $credentials = false, $isJsonDecoded = true)
    {
        $this->http->setConfig(['verifypeer' => false]);
        if ($credentials) {
            $this->http->addOption(CURLOPT_USERPWD, $credentials['api_user'].':'.$credentials['api_password']);
        }
        $headers = ['Content-type: application/json'];
        $this->http->write(\Zend_Http_Client::POST, $url, $http_ver = '1.1', $headers, $request);
        return $this->getResponse($isJsonDecoded);
    }

    /**
     * put request to the gateway
     *
     * @param string $url
     * @param string $request
     * @param boolean $credentials
     * @param boolean $isJsonDecoded
     * @return string | boolean
     */
    protected function putRequest($url, $request, $credentials = false, $isJsonDecoded = true)
    {
        $this->http->setConfig(['verifypeer' => false]);
        if ($credentials) {
            $this->http->addOption(CURLOPT_USERPWD, $credentials['api_user'].':'.$credentials['api_password']);
        }
        $headers = ['Content-type: application/json'];
        $this->http->write(\Zend_Http_Client::PUT, $url, $http_ver = '1.1', $headers, $request);
        return $this->getResponse($isJsonDecoded);
    }

    /**
     * Send the deregistration payment account to the gateway
     * @param  string $url
     * @param  array $proxyParameters [<description>]
     * @return array|boolean
     */
    protected function sendDeRegistration($url, $credentials = false, $isJsonDecoded = true)
    {
        $this->http->setConfig(['verifypeer' => false]);
        if ($credentials) {
            $this->http->addOption(CURLOPT_USERPWD, $credentials['api_user'].':'.$credentials['api_password']);
        }
        $headers = ['Content-type: application/json'];
        $this->http->addOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->http->write(\Zend_Http_Client::GET, $url);

        return $this->getResponse($isJsonDecoded);
    }

    /**
     * get payment status from the gateway
     *
     * @param string $url
     * @param boolean $credentials
     * @param boolean $isJsonDecoded
     * @return string | boolean
     */
    protected function getPaymentStatus($url, $credentials = false, $isJsonDecoded = true)
    {
        $this->http->setConfig(['verifypeer' => false]);
        if ($credentials) {
            $this->http->addOption(CURLOPT_USERPWD, $credentials['api_user'].':'.$credentials['api_password']);
        }
        $headers = ['Content-type: application/json'];
        $this->http->write(\Zend_Http_Client::GET, $url, $http_ver = '1.1', $headers);
        return $this->getResponse($isJsonDecoded);
    }

}
