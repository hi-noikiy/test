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

class Contact extends Connector
{
    const SALESFORCE_CONTACT_ATTRIBUTE = 'salesforce_contact_id';

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Job
     */
    protected $_job;

    protected $_data;

    protected $existedContacts = null;

    protected $createContactIds = null;

    protected $updateContactIds = null;

    protected $dataGetter;

    /**
     * Contact constructor.
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
        $this->_customerFactory = $customerFactory;
        $this->_data = $data;
        $this->_type = 'Contact';
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
     * @throws \Exception
     */
    public function sync($id, $update = false)
    {
        $model = $this->_customerFactory->create()->load($id);
        $email = $model->getEmail();
        $firstname = $model->getFirstname();
        $lastname = $model->getLastname();
        $id = $this->searchRecords($this->_type, 'Email', $email);

        if (!$id || ($update && $id)) {
            $params = $this->_data->getCustomer($model, $this->_type);
            $params += [
                'FirstName' => $firstname,
                'LastName' => $lastname,
                'Email' => $email,
            ];

            if ($update && $id) {
                $this->updateRecords($this->_type, $id, $params, $model->getId());
            } else {
                $id = $this->createRecords($this->_type, $params, $model->getId());
            }
        }

        return $id;
    }

    /**
     * Sync by Email
     *
     * @param  $data
     * @return string
     * @throws \Exception
     */
    public function syncByEmail($data)
    {
        $id = $this->searchRecords($this->_type, 'Email', $data['Email']);
        if (!$id) {
            $params = $data;
            $id = $this->createRecords($this->_type, $params);
        }

        return $id;
    }

    /**
     * sync array of emails data
     *
     * @param $dataEmails
     * @param $operation
     * @return array|mixed|string
     * @throws \Exception
     */
    public function syncByEmails($dataEmails, $operation)
    {
        if (empty($dataEmails))
            return [];
        $ids = [];
        $unsyncedEmails = [];
        $salesforceContacts = $this->getAllSalesforceContact();
        foreach ($dataEmails as $email => $value) {
            if (isset($salesforceContacts[$email])) {
                $ids[$email] = $salesforceContacts[$email]['Id'];
            } else {
                $unsyncedEmails[] = $value;
            }
        }

        if (!empty($unsyncedEmails)) {
            $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($unsyncedEmails));
            $this->saveReports($operation, $this->_type, $response, $unsyncedEmails);
            if (is_array($response)) {
                $total = count($response);
                for ($i = 0; $i < $total; $i++) {
                    if (isset($response[$i]['success']) && $response[$i]['success']) {
                        $ids[$unsyncedEmails[$i]['Email']] = $response[$i]['id'];
                    }
                }
            }
        }
        return $ids;
    }

    /**
     * Delete Record
     *
     * @param string $email
     * @throws \Exception
     */
    public function delete($email)
    {
        $contactId = $this->searchRecords('Contact', 'Email', $email);
        if ($contactId) {
            $this->deleteRecords('Contact', $contactId);
        }
    }

    /**
     * Sync All Customer on Magento to Salesforce
     */
    public function syncAllContact()
    {
        try {
            $customers = $this->getMagentoContacts();
            $lastCustomerId = $customers->getLastItem()->getId();
            $count = 0;
            $response = [];
            foreach ($customers as $customer) {
                $this->addRecord($customer->getId());
                $count++;
                if ($count >= 10000 || $customer->getId() == $lastCustomerId) {
                    $response += $this->syncQueue();
                }
            }
            return $response;
        } catch (\Exception $e) {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = \Magento\Framework\App\ObjectManager::getInstance()->create(\Psr\Log\LoggerInterface::class);
            $logger->critical($e->getMessage());
        }
        return null;
    }

    public function syncQueue()
    {
        $createResponse = $this->createContacts();
        $updateResponse = $this->updateContacts();
        $response = $createResponse + $updateResponse;
        $this->unsetCreateQueue();
        $this->unsetUpdateQueue();
        return $response;
    }

    /**
     * Send request to create contacts
     */
    protected function createContacts()
    {
        $response = [];
        if (!is_null($this->createContactIds)) {
            $response = $this->sendContactsRequest($this->createContactIds, 'insert');
        }
        return $response;
    }

    /**
     * Send request to update contacts
     */
    protected function updateContacts()
    {
        $response = [];
        if (!is_null($this->updateContactIds)) {
            $response = $this->sendContactsRequest($this->updateContactIds, 'update');
        }
        return $response;
    }

    /**
     * @param int $customerId
     */
    public function addRecord($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $id = $this->checkExistedContact($customer);
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
        $this->createContactIds[] = [
            'mid' => $customer->getId(),
            'mObj' => $customer
        ];
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $salesforceId
     */
    protected function addToUpdateQueue($customer, $salesforceId)
    {
        $this->updateContactIds[] = [
            'mid' => $customer->getId(),
            'mObj' => $customer,
            'sid' => $salesforceId
        ];
    }

    protected function unsetCreateQueue()
    {
        $this->createContactIds = null;
    }

    protected function unsetUpdateQueue()
    {
        $this->updateContactIds = null;
    }

    /**
     * @param array $contactIds
     * @param string $operation
     * @return mixed|string
     * @throws \Exception
     */
    public function sendContactsRequest($contactIds, $operation)
    {
        $params = [];
        foreach ($contactIds as $contactId) {
            $customer = $contactId['mObj'];
            $info = $this->_data->getCustomer($customer, $this->_type);
            $info += [
                'FirstName' => $customer->getFirstname(),
                'LastName' => $customer->getLastname(),
                'Email' => $customer->getEmail()
            ];
            if (isset($contactId['sid'])) {
                $info += ['Id' => $contactId['sid']];
            }
            $params[] = $info;
        }
        $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $contactIds);
        return $response;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return array|bool
     */
    protected function checkExistedContact($customer)
    {
        $existedContacts = $this->getAllSalesforceContact();
        if (isset($existedContacts[$customer->getEmail()]) && $customer->getId()) {
            unset($this->existedContacts[$customer->getEmail()]);
            return [
                'mObj' => $customer,
                'sid' => $existedContacts[$customer->getEmail()]['Id']
            ];
        }
        return false;
    }

    /**
     * return an array of contacts on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceContact()
    {
        if (!is_null($this->existedContacts)) {
            return $this->existedContacts;
        }
        $allContacts = [];
        $existedContacts = $this->dataGetter->getAllSalesforceContacts();
        foreach ($existedContacts as $key => $value) {
            $allContacts[$value['Email']] = $value;
        }
        $this->existedContacts = $allContacts;
        return $this->existedContacts;
    }
}
