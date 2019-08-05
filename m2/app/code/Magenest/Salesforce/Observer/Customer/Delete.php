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

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\Salesforce\Model\Sync\Lead;
use Magenest\Salesforce\Model\Sync\Contact;
use Magenest\Salesforce\Model\Sync\Account;

/**
 * Class Delete
 */
class Delete extends AbstractCustomer
{
    /**
     * Delete constructor.
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
     * Admin delete a customer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $observer->getEvent()->getCustomer();
            $email = $customer->getEmail();
            if ($this->getEnableConfig('lead')) {
                $this->_lead->delete($email);
            }

            if ($this->getEnableConfig('contact')) {
                $this->_contact->delete($email);
            }
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
    }
}
