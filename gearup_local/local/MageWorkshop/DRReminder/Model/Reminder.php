<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_DRReminder_Model_Reminder

 * @method int getCustomerId()
 * @method MageWorkshop_DRReminder_Model_Reminder setCustomerId(string $customerId)
 * @method string getCustomerName()
 * @method MageWorkshop_DRReminder_Model_Reminder setCustomerName(string $customerName)
 * @method string getEmail()
 * @method MageWorkshop_DRReminder_Model_Reminder setEmail(string $customerName)
 * @method int getOrderId()
 * @method MageWorkshop_DRReminder_Model_Reminder setOrderId(string $orderId)
 * @method int getIncrementId()
 * @method MageWorkshop_DRReminder_Model_Reminder setIncrementId(string $incrementId)
 * @method getCreatingDate()
 * @method MageWorkshop_DRReminder_Model_Reminder setCreatingDate($creatingDate)
 * @method getExpirationDate()
 * @method MageWorkshop_DRReminder_Model_Reminder setExpirationDate($expirationDate)
 * @method getSentAt()
 * @method MageWorkshop_DRReminder_Model_Reminder setSentAt($sentAt)
 * @method string getStatus()
 * @method MageWorkshop_DRReminder_Model_Reminder setStatus(string $status)
 *
 */
class MageWorkshop_DRReminder_Model_Reminder extends Mage_Core_Model_Abstract
{
    protected $_reminder = null;

    public  function _construct()
    {
        $this->_init('drreminder/reminder');
    }

    public function initRemindersSending()
    {
        $allowSend = Mage::helper('drreminder')->isSendingAllowedNow();
        if (
            Mage::getStoreConfig('drreminder/settings/remind_enable')
            && Mage::getStoreConfig('drreminder/settings/remind_send_email')
            && $allowSend
        ) {
            $reminderCollection = Mage::getModel('drreminder/reminder')->getCollection()
                ->addFieldToFilter('status', array('eq' => MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_PENDING));

            foreach ($reminderCollection as $reminder) {
                $beforetime = strtotime($reminder->getExpirationDate()) - Mage::getModel('core/date')->timestamp(time());

                // @TODO check how this works with reminders that were not sent in the last 24 hours (for example, cron failed)
                if ($beforetime < 0 ) {
                    $reminder->sendReminderEmail();
                }
            }
        }
    }

    public function sendReminderEmail()
    {
        $mailer = Mage::getModel('core/email_template_mailer');
        $storeId = $this->getStoreId();
        $senderKey = Mage::getStoreConfig('drreminder/settings/remind_email_sender', $storeId);
        $templateId = Mage::getStoreConfig('drreminder/settings/remind_email_template', $storeId);
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getEmail(), $this->getCustomerName());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($senderKey);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'customer_name'      => $this->getCustomerName(),
                'reminder'     => $this,
                'store_name' => Mage::app()->getStore($storeId)->getFrontendName()
            )
        );
        Mage::dispatchEvent('drreminder_review_reminder', array(
            'mailer' => $mailer
        ));
        $mailer->send();
        $this->markReminderAsSent();
    }

    public function markReminderAsSent()
    {
        $this
            ->setSentAt(Mage::getModel('core/date')->gmtDate())
            ->setStatus(MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_SENT)
            ->save();
        $items = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $this->getOrderId()));
        foreach ($items as $item) {
            $item->setReminder(1);
            $item->save();
        }
    }


}
