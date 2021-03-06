<?php
/**
 * Copyright © 2015 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Salesforce extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_Salesforce
 * @author   ThaoPV
 */

namespace Magenest\Salesforce\Model;

use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Connector
 *
 * @package Magenest\Salesforce\Model
 */
class Connector
{
    /**
     *#@+
     * Constants
     */
    const XML_PATH_SALESFORCE_IS_CONNECTED = 'salesforcecrm/config/is_connected';
    const XML_PATH_SALESFORCE_EMAIL = 'salesforcecrm/config/email';
    const XML_PATH_SALESFORCE_PASSWD = 'salesforcecrm/config/passwd';
    const XML_PATH_SALESFORCE_CLIENT_ID = 'salesforcecrm/config/client_id';
    const XML_PATH_SALESFORCE_CLIENT_SECRET = 'salesforcecrm/config/client_secret';
    const XML_PATH_SALESFORCE_SECURITY_TOKEN = 'salesforcecrm/config/security_token';
    const XML_PATH_SALESFORCE_ACCESS_TOKEN = 'salesforcecrm/config/access_token';
    const XML_PATH_SALESFORCE_INSTANCE_URL = 'salesforcecrm/config/instance_url';
    const XML_PATH_SALESFORCE_CONTACT_ENABLE = 'salesforcecrm/sync/contact';
    const XML_PATH_SALESFORCE_ACCOUNT_ENABLE = 'salesforcecrm/sync/account';
    const XML_PATH_SALESFORCE_LEAD_ENABLE = 'salesforcecrm/sync/lead';
    const XML_PATH_SALESFORCE_CAMPAIGN_ENABLE = 'salesforcecrm/sync/campaign';
    const XML_PATH_SALESFORCE_ORDER_ENABLE = 'salesforcecrm/sync/order';
    const XML_PATH_SALESFORCE_PRODUCT_ENABLE = 'salesforcecrm/sync/product';


    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     *
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var \Magenest\Salesforce\Model\ReportFactory
     */
    protected $_reportFactory;

    /**
     * @var string
     */
    protected $_type;

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var QueueFactory
     */
    protected $_requestLogFactory;

    protected $credential = null;

    /**
     * Connector constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory
    )
    {

        $this->_scopeConfig = $scopeConfig;
        $this->_resourceConfig = $resourceConfig;
        $this->_reportFactory = $reportFactory;
        $this->_queueFactory = $queueFactory;
        $this->_requestLogFactory = $requestLogFactory;
    }

    public function setCredential($data)
    {
        $this->credential = $data;
    }

    /**
     * Get Access Token & Instance Url
     *
     * @param  array $data
     * @param  bool|false $update
     * @return mixed
     */
    public function getAccessToken($data = [], $update = false)
    {
        try {
            if ((!empty($data) && $update)) {
                $username = $data['username'];
                $password = $data['password'];
                $client_id = $data['client_id'];
                $client_secret = $data['client_secret'];
                $security_token = $data['security_token'];
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_EMAIL, $data['username'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_PASSWD, $data['password'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_CLIENT_ID, $data['client_id'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_CLIENT_SECRET, $data['client_secret'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_SECURITY_TOKEN, $data['security_token'], 'default', 0);
            } elseif (!is_null($this->credential)) {
                $data = $this->credential;
                $username = $data['username'];
                $password = $data['password'];
                $client_id = $data['client_id'];
                $client_secret = $data['client_secret'];
                $security_token = $data['security_token'];
            } else {
                $username = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_EMAIL);
                $password = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_PASSWD);
                $client_id = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_CLIENT_ID);
                $client_secret = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_CLIENT_SECRET);
                $security_token = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_SECURITY_TOKEN);
            }

            if (!$username || !$password || !$client_id || !$client_secret || !$security_token) {
                throw new \InvalidArgumentException('Field not setup !');
            }

            $base_url = 'https://login.salesforce.com/';
            $url = $base_url . 'services/oauth2/token';
            $params = [
                'grant_type' => 'password',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'username' => $username,
                'password' => $password . $security_token
            ];
            $response = $this->makeRequest(\Zend_Http_Client::POST, $url, [], $params);
            $response = json_decode($response, true);
            if (isset($response['access_token']) && isset($response['instance_url'])) {
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_INSTANCE_URL, $response['instance_url'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_ACCESS_TOKEN, $response['access_token'], 'default', 0);
                $this->_resourceConfig->saveConfig(self::XML_PATH_SALESFORCE_IS_CONNECTED, 1, 'default', 0);
                unset($response['id']);
                unset($response['token_type']);
                unset($response['signature']);
                unset($response['issued_at']);

                return $response;
            } else {
                throw new \InvalidArgumentException($response['error_description']);
            }
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * @param $method
     * @param $path
     * @param null $paramter
     * @param bool $useFreshCredential
     * @return mixed|string
     * @throws \Exception
     */
    public function sendRequest($method, $path, $paramter = null, $useFreshCredential = false)
    {
        if ($useFreshCredential) {
            $instance_url = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_INSTANCE_URL, ScopeInterface::SCOPE_STORE);
            $access_token = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);
        }


        try {
            if (!isset($instance_url) || !isset($access_token) || $useFreshCredential) {
                $login = $this->getAccessToken();
                $instance_url = $login['instance_url'];
                $access_token = $login['access_token'];
            }
        } catch (\InvalidArgumentException $exception) {
            throw new \Exception($exception->getMessage());
        }

        $headers = [
            "Authorization" => "Bearer " . $access_token,
            "Content-Type" => "application/json",
        ];
        $url = $instance_url . $path;
        $response = $this->makeRequest($method, $url, $headers, $paramter);
        $response = json_decode($response, true);
        if (isset($response[0]['errorCode']) && $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
            $this->sendRequest($method, $path, $paramter, true);
        }

        return $response;
    }

    /**
     * @param $table
     * @param $parameter
     * @param null $mid
     * @return bool
     * @throws \Exception
     */
    public function createRecords($table, $parameter, $mid = null)
    {
        $path = "/services/data/v34.0/sobjects/" . $table . "/";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        if (isset($response["id"])) {
            $id = $response["id"];
            $this->saveReport($id, 'create', $table, 1, null, $mid);
            return $id;
        } else if (isset($response[0]['errorCode'])) {
            $message = $response[0]['message'];
            $this->saveReport(null, null, $this->_type, 2, $message, $mid);
            throw new \Exception($message);
        }

        return false;
    }

    /**
     * Delete a record in salesforce
     *
     * @param string $table
     * @param string $id
     */
    public function deleteRecords($table, $id, $mid = null)
    {
        $path = "/services/data/v34.0/sobjects/" . $table . "/" . $id;
        $this->sendRequest(\Zend_Http_Client::DELETE, $path);
        $this->saveReport($id, 'delete', $table, 1, null, $mid);
    }

    /**
     * @param $table
     * @param $id
     * @param $paramter
     * @param null $mid
     * @throws \Exception
     */
    public function updateRecords($table, $id, $paramter, $mid = null)
    {
        $path = "/services/data/v34.0/sobjects/" . $table . "/" . $id;
        $this->sendRequest(\Zend_Http_Client::PATCH, $path, $paramter);
        $this->saveReport($id, 'update', $table, 1, null, $mid);
    }

    /**
     * @param $table
     * @param $field
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function searchRecords($table, $field, $value)
    {

        $query = "SELECT Id FROM $table WHERE $field = '$value' ";
        if ($table == 'PricebookEntry') {
            $query .= ' ORDER BY Id ';
        }

        $query .= 'LIMIT 1';
        $path = '/services/data/v34.0/query?q=' . urlencode($query);

        $response = $this->sendRequest(\Zend_Http_Client::GET, $path);
        if (isset($response['totalSize']) && $response['totalSize'] == 1) {
            $id = $response['records']['0']['Id'];
            return $id;
        }

        return false;
    }

    /**
     * Get All Field of a table in Salesforce
     *
     * @param  string $table
     * @return string
     */
    public function getFields($table)
    {

        $path = '/services/data/v34.0/sobjects/' . $table . '/describe/';
        $response = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $data = [];
        $_type = [
            'picklist',
            'date',
            'datetime',
            'reference',
        ];
        if (isset($response['fields'])) {
            foreach ($response['fields'] as $item => $value) {
                $type = $value['type'];
                if ($value['permissionable'] == 1 && !in_array($type, $_type)) {
                    $label = $value['label'];
                    $name = $value['name'];
                    $data[$name] = $label;
                }
            }
        }

        $fields = serialize($data);

        return $fields;
    }

    /**
     * @param $id
     * @param $action
     * @param $table
     * @param int $status
     * @param null $message
     * @param null $mid
     */
    public function saveReport($id, $action, $table, $status = 1, $message = null, $mid = null)
    {
        $model = $this->_reportFactory->create();
        $model->saveReport($id, $action, $table, $status, $message, $mid);

        return;
    }

    public function saveReports($action, $table, $response, $magentoIds)
    {
        if (is_array($response)) {
            $total = count($response);
            $reports = [];
            for ($i = 0; $i < $total; $i++) {
                $recordId = null;
                $status = 2;
                $message = null;
                $magentoId = null;
                if (isset($response[$i]['success']) && $response[$i]['success']) {
                    $recordId = $response[$i]['id'];
                    $status = 1;
                    $magentoId = isset($magentoIds[$i]['mid']) ? $magentoIds[$i]['mid'] : null;
                } elseif (isset($response[$i]['errors'][0])) {
                    $message = 'ERROR ';
                    foreach ($response[$i]['errors'] as $error) {
                        $message .= $error['message'] . ';';
                    }
                } else {
                    $message = serialize($response);
                }
                $params = [
                    'record_id' => $recordId,
                    'action' => $action,
                    'salesforce_table' => $table,
                    'status' => $status,
                    'msg' => $message,
                    'magento_id' => $magentoId
                ];
                $params += $this->_reportFactory->create()->getInfoReport();
                $reports[] = $params;
            }
            $this->_reportFactory->create()->saveReports($reports);
        }
    }

    /**
     * @param $method
     * @param $url
     * @param array $headers
     * @param array $params
     * @return string
     * @throws \Zend_Http_Client_Exception
     */
    public function makeRequest($method, $url, $headers = [], $params = [])
    {
        $client = new \Zend_Http_Client($url);
        $client->setHeaders($headers);
        if ($method != \Zend_Http_Client::GET) {
            $client->setParameterPost($params);
            if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') {
                $client->setEncType('application/json');
                $params = json_encode($params);
                $client->setRawData($params);
            }
        }
        $response = $client->request($method)->getBody();
        $this->_requestLogFactory->create()->addRequest(RequestLog::REST_REQUEST_TYPE);
        return $response;
    }

    public function syncAllQueue()
    {
        $type = $this->_type;
        if ($type == 'Product2') {
            $type = 'Products';
        }
        $queueModel = $this->_queueFactory->create();
        $queueCollection = $queueModel->getCollection()
            ->addFieldToFilter('type', rtrim($type, 's'));

        $lastId = (int)$queueCollection->getLastItem()->getId();
        $count = 0;
        $response = [];
        /** @var \Magenest\Salesforce\Model\Queue $queue */
        foreach ($queueCollection as $queue) {
            $entityId = $queue->getEntityId();
            $this->addRecord($entityId);
            $count++;
            if ($count >= 5000 || $queue->getId() == $lastId) {
                $response += $this->syncQueue();
                $count = 0;
            }
        }
        $maxRecord = 5000;
        $count = 0;
        $queueCollectionArr = [];
        foreach ($queueCollection as $queue) {
            $queueCollectionArr[] = $queue->getId();
            $count++;
            if ($count >= $maxRecord || $queue->getId() == $lastId) {
                $queueModel->deleteMultiQueues($queueCollectionArr);
                $count = 0;
                $queueCollectionArr = [];
            }
        }
        return $response;
    }

    public function addRecord($entityId)
    {
    }

    public function syncQueue()
    {
        return null;
    }
}
