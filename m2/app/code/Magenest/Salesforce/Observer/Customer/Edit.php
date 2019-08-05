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
namespace Magenest\Salesforce\Observer\Customer;

use Magenest\Salesforce\Model\QueueFactory;
use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\Sync\Account;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\Salesforce\Model\Sync\Lead;
use Magenest\Salesforce\Model\Sync\Contact;

/**
 * Class Update
 */
class Edit extends AbstractCustomer
{
    /**
     * Edit constructor.
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $config
     * @param Lead $lead
     * @param Contact $contact
     * @param Account $account
     */
    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config,
        Lead $lead,
        Contact $contact,
        Account $account
    ) {
        parent::__construct($queueFactory, $config, $lead, $contact, $account);
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            $customer = $event->getCustomer();
            $this->syncContact($customer);
            $this->syncLead($customer);
            $this->syncAccount($customer);
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
    }
}
