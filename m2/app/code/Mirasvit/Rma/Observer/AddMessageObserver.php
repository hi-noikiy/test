<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Observer;
use Mirasvit\Rma\Model\Config;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;

class AddMessageObserver implements ObserverInterface
{
    public function __construct(
        StatusRepositoryInterface $statusRepository,
        RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Helper\Mail $rmaMail,
        \Mirasvit\Rma\Helper\Ruleevent $rmaRuleEvent
    ) {
        $this->statusRepository     = $statusRepository;
        $this->rmaManagement        = $rmaManagement;
        $this->attachmentManagement = $attachmentManagement;
        $this->rmaMail              = $rmaMail;
        $this->rmaRuleEvent         = $rmaRuleEvent;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Mirasvit\Rma\Model\Rma $rma */
        $rma = $observer->getData('rma');
        /** @var \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer */
        $performer = $observer->getData('performer');
        /** @var \Mirasvit\Rma\Model\Message $message */
        $message = $observer->getData('message');

        $params = $observer->getData('params');

        if (!empty($params['helpdeskEmail'])) {
            $message->setEmailId($params['helpdeskEmail']->getId());
            $params['helpdeskEmail']->setIsProcessed(true)
                ->save();
            $this->attachmentManagement->copyEmailAttachments($params['email'], $message);
        } elseif (empty($params['isHistory'])) {
            $this->attachmentManagement->saveAttachments(
                \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_MESSAGE,
                $message->getId(),
                'attachment'
            );
        }

        if ($performer instanceof \Mirasvit\Rma\Service\Performer\UserStrategy) {
            if ($message->getIsCustomerNotified()) {
                $this->rmaMail->sendNotificationCustomerEmail($rma, $message);
            }
            //send notification about internal message
            if (
                $rma->getUserId() != $performer->getId() && !$message->getIsVisibleInFrontend()
            ) {
                $this->rmaMail->sendNotificationAdminEmail($rma, $message);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_STAFF_REPLY, $rma
            );
        } else {
            if (!empty($params['isNotifyAdmin'])) {
                $this->rmaMail->sendNotificationAdminEmail($rma, $message);
            }
            $status = $this->rmaManagement->getStatus($rma);
            $customerMessage = $this->statusRepository->getCustomerMessageForStore($status, $rma->getStoreId());
            $allowSend = !$customerMessage || $customerMessage && $rma->getStatusId() == $rma->getOrigData('status_id');
            if ($message->getIsCustomerNotified() && $allowSend) {
                $this->rmaMail->sendNotificationCustomerEmail($rma, $message);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_CUSTOMER_REPLY, $rma
            );
        }
    }
}