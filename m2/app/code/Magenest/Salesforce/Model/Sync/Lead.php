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
 * @author   ThaoPV-<thaopw@gmail.com>
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

class Lead extends Connector
{
    /**
     * @var /Magento/Customer/Model/CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Job
     */
    protected $_job;

    protected $_data;

    protected $existedLeads = null;

    protected $createLeadIds = null;

    protected $updateLeadIds = null;

    protected $dataGetter;

    /**
     * Lead constructor.
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
        $this->_type = 'Lead';
        $this->_table = 'customer';
        $this->_job = $job;
        $this->dataGetter = $dataGetter;
    }

    /**
     * Update or create new a record
     *
     * @param int $id
     * @param bool $update
     * @return bool
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
            if (empty($params['Company'])) {
                $params['Company'] = 'N/A';
            }

            if ($update && $id) {
                $this->updateRecords($this->_type, $id, $params, $model->getId());
            } else {
                $id = $this->createRecords($this->_type, $params, $model->getId());
            }
        }

        return $id;
    }

    /**
     * Delete Record
     *
     * @param string $email
     * @throws \Exception
     */
    public function delete($email)
    {
        $leadId = $this->searchRecords('Lead', 'Email', $email);
        if ($leadId) {
            $this->deleteRecords('Lead', $leadId);
        }
    }

    /**
     * Sync by email
     *
     * @param string $email
     * @throws \Exception
     */
    public function syncByEmail($email)
    {
        $leadId = $this->searchRecords('Lead', 'Email', $email);
        if (!$leadId) {
            $params = [
                'Email' => $email,
                'LastName' => 'Guest',
                'Company' => 'N/A',
            ];
            $this->createRecords($this->_type, $params);
        }
    }

    /**
     * Sync All Customer on Magento to Salesforce
     */
    public function syncAllLead()
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
            $logger = \Magento\Framework\App\ObjectManager::getInstance()->create(\Psr\Log\LoggerInterface::class);
            $logger->critical($e->getMessage());
        }
        return null;
    }

    public function syncQueue()
    {
        $createResponse = $this->createLeads();
        $updateResponse = $this->updateLeads();
        $response = $createResponse + $updateResponse;
        $this->unsetCreateQueue();
        $this->unsetUpdateQueue();
        return $response;
    }

    /**
     * Send request to create leads
     */
    protected function createLeads()
    {
        $response = [];
        if (!is_null($this->createLeadIds)) {
            $response = $this->sendLeadsRequest($this->createLeadIds, 'insert');
        }
        return $response;
    }

    /**
     * Send request to update leads
     */
    protected function updateLeads()
    {
        $response = [];
        if (!is_null($this->updateLeadIds)) {
            $response = $this->sendLeadsRequest($this->updateLeadIds, 'update');
        }
        return $response;
    }

    /**
     * @param int $customerId
     */
    public function addRecord($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $id = $this->checkExistedLead($customer);
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
        $this->createLeadIds[] = [
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
        $this->updateLeadIds[] = [
            'mObj' => $customer,
            'mid' => $customer->getId(),
            'sid' => $salesforceId
        ];
    }

    protected function unsetCreateQueue()
    {
        $this->createLeadIds = null;
    }

    protected function unsetUpdateQueue()
    {
        $this->updateLeadIds = null;
    }

    /**
     * @param array $leadIds
     * @param string $operation
     * @return mixed|string
     * @throws \Exception
     */
    protected function sendLeadsRequest($leadIds, $operation)
    {
        $params = [];
        foreach ($leadIds as $leadId) {
            $customer = $leadId['mObj'];
            $info = $this->_data->getCustomer($customer, $this->_type);
            $info += [
                'FirstName' => $customer->getFirstname(),
                'LastName' => $customer->getLastname(),
                'Email' => $customer->getEmail(),
                'Company' => 'N/A',
            ];
            if (isset($leadId['sid'])) {
                $info += ['Id' => $leadId['sid']];
            }
            $params[] = $info;
        }
        $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $leadIds);
        return $response;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return array|bool
     */
    protected function checkExistedLead($customer)
    {
        $existedLeads = $this->getAllSalesforceLead();
        if (isset($existedLeads[$customer->getEmail()]) && $customer->getId()) {
            unset($this->existedLeads[$customer->getEmail()]);
            return [
                'mObj' => $customer,
                'sid' => $existedLeads[$customer->getEmail()]['Id']
            ];
        }
        return false;
    }

    /**
     * return an array of leads on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceLead()
    {
        if (!is_null($this->existedLeads)) {
            return $this->existedLeads;
        }
        $allLeads = [];
        $existedLeads = $this->dataGetter->getAllSalesforceLeads();
        foreach ($existedLeads as $key => $value) {
            $allLeads[$value['Email']] = $value;
        }
        $this->existedLeads = $allLeads;
        return $this->existedLeads;
    }
}
