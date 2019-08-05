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

namespace Magenest\Salesforce\Model\Sync;

use Magenest\Salesforce\Model\Connector;
use Magenest\Salesforce\Model\Data;
use Magenest\Salesforce\Model\QueueFactory;
use Magenest\Salesforce\Model\ReportFactory as ReportFactory;
use Magenest\Salesforce\Model\RequestLogFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class Account
 *
 * @package Magenest\Salesforce\Model\Sync
 */
class Account extends Connector
{
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'salesforce_account_id';

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerFactory;

    /**
     * @var Job
     */
    protected $_job;

    /**
     * @var Data
     */
    protected $_data;

    protected $existedAccounts = null;

    protected $createAccountIds = null;

    protected $updateAccountIds = null;

    protected $dataGetter;

    /**
     * Account constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param CustomerFactory $customerFactory
     * @param Job $job
     * @param DataGetter $dataGetter
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        Data $data,
        CustomerFactory $customerFactory,
        Job $job,
        DataGetter $dataGetter,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory
    )
    {
        parent::__construct($scopeConfig, $resourceConfig, $reportFactory, $queueFactory, $requestLogFactory);
        $this->_data = $data;
        $this->_customerFactory = $customerFactory;
        $this->_type = 'Account';
        $this->_table = 'customer';
        $this->_job = $job;
        $this->dataGetter = $dataGetter;
    }

    /**
     * Update or create new a record
     *
     * @param  int $id
     * @param  boolean $update
     * @return string
     */
    public function sync($id, $update = false)
    {
        $customer = $this->_customerFactory->create()->load($id);
        $email = $customer->getEmail();
        $id = $this->searchRecords($this->_type, 'Name', $email);

        if (!$id || ($update && $id)) {
            // Pass data of customer to array
            $params = $this->_data->getCustomer($customer, $this->_type);
            $params += [
                'Name' => $email,
                'AccountNumber' => $customer->getId(),
            ];
            if ($update && $id) {
                $this->updateRecords($this->_type, $id, $params, $customer->getId());
            } else {
                $id = $this->createRecords($this->_type, $params, $customer->getId());
            }
        }
        $this->saveAttribute($customer, $id);
        return $id;
    }

    /**
     * Create new a record by email
     *
     * @param  string $email
     * @return string
     */
    public function syncByEmail($email)
    {
        $id = $this->searchRecords($this->_type, 'Name', $email);
        if (!$id) {
            $params = ['Name' => $email];
            $id = $this->createRecords($this->_type, $params);
        }

        return $id;
    }


    /**
     * Sync All Customer on Magento to Salesforce
     */
    public function syncAllAccount()
    {
        try {
            $customers = $this->_customerFactory->create()->getCollection();
            $lastCustomerId = $customers->getLastItem()->getId();
            $count = 0;
            $response = [];
            /** @var \Magento\Customer\Model\Customer $customer */
            foreach ($customers as $customer) {
                $this->addRecord($customer->getId());
                $count++;
                if ($count >= 10000 || $customer->getId() == $lastCustomerId) {
                    $response += $this->syncQueue();
                }
            }
            return $response;
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
        return null;
    }

    public function syncQueue()
    {
        $createResponse = $this->createAccounts();
        $this->saveAttributes($this->createAccountIds, $createResponse);
        $updateResponse = $this->updateAccounts();
        $this->saveAttributes($this->updateAccountIds, $updateResponse);
        $response = $createResponse + $updateResponse;
        $this->unsetCreateQueue();
        $this->unsetUpdateQueue();
        return $response;
    }

    /**
     * Send request to create accounts
     */
    protected function createAccounts()
    {
        $response = [];
        if (!is_null($this->createAccountIds)) {
            $response = $this->sendAccountsRequest($this->createAccountIds, 'insert');
        }
        return $response;
    }

    /**
     * Send request to update accounts
     */
    protected function updateAccounts()
    {
        $response = [];
        if (!is_null($this->updateAccountIds)) {
            $response = $this->sendAccountsRequest($this->updateAccountIds, 'update');
        }
        return $response;
    }

    /**
     * @param int $customerId
     */
    public function addRecord($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $id = $this->checkExistedAccount($customer);
        if (!$id) {
            $this->addToCreateQueue($customer);
        } else {
            $this->addToUpdateQueue($id['mObj'], $id['sid']);
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function addToCreateQueue($customer)
    {
        $this->createAccountIds[] = [
            'mObj' => $customer,
            'mid' => $customer->getId()
        ];
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param $salesforceId
     */
    protected function addToUpdateQueue($customer, $salesforceId)
    {
        $this->updateAccountIds[] = [
            'mObj' => $customer,
            'mid' => $customer->getId(),
            'sid' => $salesforceId
        ];
    }

    protected function unsetCreateQueue()
    {
        $this->createAccountIds = null;
    }

    protected function unsetUpdateQueue()
    {
        $this->updateAccountIds = null;
    }

    /**
     * sync array of emails
     *
     * @param array $emails
     * @param string $operation
     * @return array
     * @throws \Exception
     */
    public function syncByEmails($emails, $operation)
    {
        if (empty($emails))
            return [];
        $ids = [];
        $unsyncedEmails = [];
        $salesforceAccounts = $this->getAllSalesforceAccount();
        foreach ($emails as $email) {
            if (isset($salesforceAccounts[$email['email']])) {
                foreach ($email['order_ids'] as $orderId) {
                    $ids[$orderId] = $salesforceAccounts[$email['email']]['Id'];
                }
            } else {
                $unsyncedEmails[] = ['Name' => $email['email']];
            }
        }

        if (!empty($unsyncedEmails)) {
            $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($unsyncedEmails));
            $this->saveReports($operation, $this->_type, $response, $unsyncedEmails);
            if (is_array($response)) {
                $total = count($response);
                for ($i = 0; $i < $total; $i++) {
                    if (isset($response[$i]['success']) && $response[$i]['success']) {
                        foreach ($emails[$unsyncedEmails[$i]['Name']]['order_ids'] as $order_id) {
                            $ids[$order_id] = $response[$i]['id'];
                        }
                    }
                }
            }
        }
        return $ids;
    }

    /**
     * @param array $accountIds
     * @param string $operation
     * @return mixed|string
     * @throws \Exception
     */
    public function sendAccountsRequest($accountIds, $operation)
    {
        $params = [];
        foreach ($accountIds as $accountId) {
            $customer = $accountId['mObj'];
            $info = $this->_data->getCustomer($customer, $this->_type);
            $info += [
                'Name' => $customer->getEmail(),
                'AccountNumber' => $customer->getId(),
            ];
            if (isset($accountId['sid'])) {
                $info += ['Id' => $accountId['sid']];
            }
            $params[] = $info;
        }
        $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $accountIds);
        return $response;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return array|bool
     */
    protected function checkExistedAccount($customer)
    {
        $existedAccounts = $this->getAllSalesforceAccount();
        $customerEmail = $customer->getEmail();
        if (isset($existedAccounts[$customerEmail]) && $customer->getId()) {
            unset($this->existedAccounts[$customerEmail]);
            return [
                'mObj' => $customer,
                'sid' => $existedAccounts[$customerEmail]['Id']
            ];
        }

        return false;
    }

    /**
     * return an array of accounts on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceAccount()
    {
        if (!is_null($this->existedAccounts)) {
            return $this->existedAccounts;
        }
        $allSalesforceAccounts = [];
        $existedAccounts = $this->dataGetter->getAllSalesforceAccounts();
        foreach ($existedAccounts as $key => $value) {
            $allSalesforceAccounts[$value['Name']] = $value;
        }
        $this->existedAccounts = $allSalesforceAccounts;
        return $this->existedAccounts;
    }

    /**
     * @param $customerIds
     * @param $response
     * @throws \Exception
     */
    protected function saveAttributes($customerIds, $response)
    {
        if (empty($customerIds) || is_null($customerIds))
            return;
        if (is_array($response) && is_array($customerIds)) {
            $total = count($response);
            for ($i = 0; $i < $total; $i++) {
                $customer = $customerIds[$i]['mObj'];
                if (isset($response[$i]['id']) && $customer->getId()) {
                    $this->saveAttribute($customer, $response[$i]['id']);
                }
            }
        } else {
            throw new \Exception('Response not an array');
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $salesforceId
     * @throws \Exception
     */
    protected function saveAttribute($customer, $salesforceId)
    {
        $customerData = $customer->getDataModel();
        $customerData->setId($customer->getId());
        $customerData->setCustomAttribute(self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE, $salesforceId);
        $customer->updateData($customerData);
        /** @var \Magento\Customer\Model\ResourceModel\Customer $customerResource */
        $customerResource = $this->_customerFactory->create()->getResource();
        $customerResource->saveAttribute($customer, self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
    }
}
