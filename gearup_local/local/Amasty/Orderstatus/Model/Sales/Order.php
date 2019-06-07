<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderstatus
 */
class Amasty_Orderstatus_Model_Sales_Order extends Amasty_Orderstatus_Model_Sales_Order_Pure
{
    public function addStatusHistoryComment($comment, $status = false)
    {
        $history = parent::addStatusHistoryComment($comment, $status);
        
        // checking is the new status is one of ours
        $statusCollection = Mage::getResourceModel('amorderstatus/status_collection');
        $statusCollection->addFieldToFilter('is_system', array('eq' => 0));
        foreach ($statusCollection as $statusModel) {
            $underscorePos = strpos($status, '_') + 1;
            if ($statusModel->getAlias() == substr($status, $underscorePos)) {
                // this is it!
                Mage::register('amorderstatus_history_status', $statusModel, true);
            }
        }
        
        return $history;
    }
    
    public function sendOrderUpdateEmail($notifyCustomer = true, $comment='')
    {
        if (!Mage::helper('sales')->canSendOrderCommentEmail($this->getStore()->getId())) {
            return $this;
        }

        $statusModel = Mage::registry('amorderstatus_history_status');
        if ($statusModel && $statusModel->getNotifyByEmail()) {
            $notifyCustomer = true;
        }
        $template = '';
        if ($statusModel) {
            $template = Mage::getModel('amorderstatus/status_template')->loadTemplateId($statusModel->getId(), $this->getStoreId());
            if (0 != strlen($template) && 0 == $template) {
                $template = 'amorderstatus_status_change';
            }
        }

        if (version_compare(Mage::getVersion(), '1.9.1.0', '>=')) {
            Mage::register('amasty_orderstatus_template', $template, true);
            $this->queueOrderUpdateEmail($notifyCustomer, $comment, true);
            return $this;
        }

        $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $this->getStoreId());
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // set design parameters, required for email (remember current)
        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'store'   => $this->getStoreId(),
            'area'    => 'frontend',
            'package' => Mage::getStoreConfig('design/package/name', $this->getStoreId()),
        ));

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $sendTo = array();

        $mailTemplate = Mage::getModel('core/email_template');

        if ($this->getCustomerIsGuest()) {
            if (!$template) { // template was not set via extension
                $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $this->getStoreId());
            }
            $customerName = $this->getBillingAddress()->getName();
        } else {
            if (!$template) { // template was not set via extension
                $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $this->getStoreId());
            }
            $customerName = $this->getCustomerName();
        }
        
        if ($notifyCustomer) {
            $sendTo[] = array(
                'name'  => $customerName,
                'email' => $this->getCustomerEmail()
            );
            if ($copyTo && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $mailTemplate->addBcc($email);
                }
            }

        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'     => $this,
                        'billing'   => $this->getBillingAddress(),
                        'comment'   => $comment
                    )
                );
        }

        $translate->setTranslateInline(true);

        // revert current design
        Mage::getDesign()->setAllGetOld($currentDesign);

        return $this;
    }

    public function queueOrderUpdateEmail($notifyCustomer = true, $comment = '', $forceMode = false)
    {
        $storeId = $this->getStore()->getId();

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recipient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // Retrieve corresponding email template id and customer name
        $templateId = Mage::registry('amasty_orderstatus_template');
        if ($this->getCustomerIsGuest()) {
            if (!$templateId) { // template was not set via extension
                $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
            }
            $customerName = $this->getBillingAddress()->getName();
        } else {
            if (!$templateId) { // template was not set via extension
                $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
            }
            $customerName = $this->getCustomerName();
        }

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            /** @var $emailInfo Mage_Core_Model_Email_Info */
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($this->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is
        // 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'   => $this,
                'comment' => $comment,
                'billing' => $this->getBillingAddress()
            )
        );

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_UPDATE_ORDER)
            ->setIsForceCheck(!$forceMode);
        $mailer->setQueue($emailQueue)->send();

        return $this;
    }
}