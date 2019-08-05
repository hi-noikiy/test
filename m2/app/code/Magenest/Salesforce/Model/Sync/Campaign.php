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
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

class Campaign extends Connector
{
    /**
     * Constants
     */
    const XML_PATH_SYNC_CAMPAIGN = 'salesforcecrm/sync/campaign';

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var Job
     */
    protected $_job;

    /**
     * @var Data
     */
    protected $_data;

    protected $existedCampaigns = null;

    protected $createCampaignIds = null;

    protected $updateCampaignIds = null;

    protected $dataGetter;

    /**
     * Campaign constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param RuleFactory $ruleFactory
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
        RuleFactory $ruleFactory,
        Job $job,
        DataGetter $dataGetter,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory
    )
    {
        parent::__construct($scopeConfig, $resourceConfig, $reportFactory, $queueFactory, $requestLogFactory);
        $this->_ruleFactory = $ruleFactory;
        $this->_data = $data;
        $this->_type = 'Campaign';
        $this->_table = 'catalogrule';
        $this->_job = $job;
        $this->dataGetter = $dataGetter;
    }

    /**
     * Create new a record
     *
     * @param  int $id
     * @return string
     */
    public function sync($id)
    {
        $rule = $this->_ruleFactory->create()->load($id);
        $name = $rule->getName();

        $id = $this->searchRecords($this->_type, 'Name', trim($name));
        $params = $this->_data->getCampaign($rule, $this->_type);
        $params += ['Name' => $name];

        if (!$id) {
            $id = $this->createRecords($this->_type, $params, $rule->getId());
        } else {
            $this->updateRecords($this->_type, $id, $params, $rule->getId());
        }

        return $id;
    }

    /**
     * Sync All Campaigns on Magento to Salesforce
     */
    public function syncAllCampaigns()
    {
        try {
            $rules = $this->_ruleFactory->create()->getCollection();
            $lastRuleId = $rules->getLastItem()->getId();
            $count = 0;
            $response = [];
            /** @var \Magento\CatalogRule\Model\Rule $rule */
            foreach ($rules as $rule) {
                $this->addRecord($rule->getId());
                $count++;
                if ($count >= 10000 || $rule->getId() == $lastRuleId) {
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
        $createResponse = $this->createCampaigns();
        $updateResponse = $this->updateCampaigns();
        $response = $createResponse + $updateResponse;
        $this->unsetCreateQueue();
        $this->unsetUpdateQueue();
        return $response;
    }

    /**
     * Send request to create accounts
     */
    protected function createCampaigns()
    {
        $response = [];
        if (!is_null($this->createCampaignIds)) {
            $response = $this->sendCampaignsRequest($this->createCampaignIds, 'insert');
        }
        return $response;
    }

    /**
     * Send request to update accounts
     */
    protected function updateCampaigns()
    {
        $response = [];
        if (!is_null($this->updateCampaignIds)) {
            $response = $this->sendCampaignsRequest($this->updateCampaignIds, 'update');
        }
        return $response;
    }

    /**
     * @param int $ruleId
     */
    public function addRecord($ruleId)
    {
        $rule = $this->_ruleFactory->create()->load($ruleId);
        $id = $this->checkExistedCampaign($rule);
        if (!$id) {
            $this->addToCreateQueue($rule);
        } else {
            $this->addToUpdateQueue($id['mObj'], $id['sid']);
        }
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     */
    protected function addToCreateQueue($rule)
    {
        $this->createCampaignIds[] = [
            'mObj' => $rule,
            'mid' => $rule->getId()
        ];
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param string $salesforceId
     */
    protected function addToUpdateQueue($rule, $salesforceId)
    {
        $this->updateCampaignIds[] = [
            'mObj' => $rule,
            'mid' => $rule->getId(),
            'sid' => $salesforceId
        ];
    }

    protected function unsetCreateQueue()
    {
        $this->createCampaignIds = null;
    }

    protected function unsetUpdateQueue()
    {
        $this->updateCampaignIds = null;
    }

    /**
     * @param array $campaignIds
     * @param string $operation
     * @return mixed|string
     */
    protected function sendCampaignsRequest($campaignIds, $operation)
    {
        $params = [];
        foreach ($campaignIds as $id) {
            $rule=$id['mObj'];
            $info = $this->_data->getCampaign($rule, $this->_type);
            $info += ['Name' => $rule->getName()];
            if (isset($id['sid'])) {
                $info += ['Id' => $id['sid']];
            }
            $params[] = $info;
        }
        $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $campaignIds);
        return $response;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @return array|bool
     */
    protected function checkExistedCampaign($rule)
    {
        $existedCampaigns = $this->getAllSalesforceCampaigns();
        $ruleName = trim($rule->getName());
        if (isset($existedCampaigns[$ruleName])) {
            unset($this->existedCampaigns[$ruleName]);
            return [
                'mObj'=>$rule,
                'sid' => $existedCampaigns[$ruleName]['Id']
            ];
        }
        return false;
    }

    /**
     * return an array of Campaigns on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceCampaigns()
    {
        if (!is_null($this->existedCampaigns)) {
            return $this->existedCampaigns;
        }
        $allRules = [];
        $existedCampaigns = $this->dataGetter->getAllSalesforceCampaigns();
        foreach ($existedCampaigns as $key => $value) {
            $allRules[$value['Name']] = $value;
        }
        $this->existedCampaigns = $allRules;
        return $this->existedCampaigns;
    }
}
