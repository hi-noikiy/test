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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Helper_Notification extends Mage_Core_Helper_Abstract
{
    //http://www.iana.org/assignments/auto-submitted-keywords/auto-submitted-keywords.xhtml
    //https://tools.ietf.org/html/rfc3834

    /**
    The auto-replied keyword:
    -  SHOULD be used on messages sent in direct response to another message by an automatic process,
    -  MUST NOT be used on manually-generated messages,
    -  MAY be used on Delivery Status Notifications (DSNs) and Message Disposition Notifications (MDNs),
    -  MUST NOT be used on messages generated by automatic or periodic processes, except for messages which are
    automatic responses to other messages.
     */
    const FLAG_AUTO_REPLIED = 'auto-replied';
    /**
    The auto-generated keyword:
    -  SHOULD be used on messages generated by automatic (often periodic)
    processes (such as UNIX "cron jobs") which are not direct
    responses to other messages,
    -  MUST NOT be used on manually generated messages,
    -  MUST NOT be used on a message issued in direct response to another
    message,
    -  MUST NOT be used to label Delivery Status Notifications (DSNs)
    [I2.RFC3464], or Message Disposition Notifications (MDNs)
    [I3.RFC3798], or other reports of message (non)receipt or
    (non)delivery.  Note: Some widely-deployed SMTP implementations
    currently use "auto-generated" to label non-delivery reports.
    These should be changed to use "auto-replied" instead.
     */
    const FLAG_AUTO_GENERATED = 'auto-generated';
    const FLAG_NO = false;

    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';

    public $emails = array();

    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     * @param int                            $triggeredBy
     *
     * @return void
     */
    protected function notifyUser($ticket, $customer, $user, $triggeredBy)
    {
        $storeId = $ticket->getStoreId();
        if ($ticket->getUserId()) {
            $user = Mage::getModel('admin/user');
            $user->load($ticket->getUserId());
            $this->mail(
                $ticket,
                $customer,
                $user,
                $user->getEmail(),
                $user->getName(),
                $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId),
                $ticket->getLastMessage()->getAttachments(),
                array(),
                self::FLAG_NO //message was originated by customer
            );
        } elseif ($department = $ticket->getDepartment()) {
            if ($department->getNotificationEmail()) {
                $this->mail(
                    $ticket,
                    $customer,
                    $user,
                    $department->getNotificationEmail(),
                    $department->getName(),
                    $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId),
                    $ticket->getLastMessage()->getAttachments(),
                    array(),
                    self::FLAG_NO //message was originated by customer
                );
            }
            if ($department->getIsMembersNotificationEnabled()) {
                foreach ($department->getUsers() as $member) {
                    $this->mail(
                        $ticket,
                        $customer,
                        $user,
                        $member->getEmail(),
                        $department->getName(),
                        $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId),
                        $ticket->getLastMessage()->getAttachments(),
                        array(),
                        self::FLAG_NO //message was originated by customer
                    );
                }
            }
        } else {
            $this->newMessage($ticket, $customer, $user, $triggeredBy, Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC);
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     *
     * @return void
     */
    protected function notifyCustomer($ticket, $customer, $user)
    {
        $storeId = $ticket->getStoreId();
        $this->mail(
            $ticket,
            $customer,
            $user,
            $ticket->getCustomerEmail(),
            $ticket->getCustomerName(),
            $this->getConfig()->getNotificationNewMessageTemplate($storeId),
            $ticket->getLastMessage()->getAttachments()
        );
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     *
     * @return void
     */
    protected function notifyThird($ticket, $customer, $user)
    {
        $storeId = $ticket->getStoreId();
        $this->mail($ticket, $customer, $user, $ticket->getThirdPartyEmail(), '',
            $this->getConfig()->getNotificationThirdNewMessageTemplate($storeId),
            $ticket->getLastMessage()->getAttachments());
    }

    /**
     * Send email notification about creation of new ticket.
     *
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     * @param string                         $triggeredBy
     * @param string                         $messageType
     *
     * @return void
     */
    public function newTicket($ticket, $customer, $user, $triggeredBy, $messageType)
    {
        $storeId = $ticket->getStoreId();

        if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
            //ticket created by customer. we send confirmation to customer.
            $this->mail(
                $ticket,
                $customer,
                $user,
                $ticket->getCustomerEmail(),
                $ticket->getCustomerName(),
                $this->getConfig()->getNotificationNewTicketTemplate($storeId),
                array(),
                array(),
                self::FLAG_AUTO_REPLIED //direct response to customer email with new ticket
            );

            if ($department = $ticket->getDepartment()) {
                if ($department->getNotificationEmail()) {
                    //ticket created by customer. we notify department.
                    $this->mail(
                        $ticket,
                        $customer,
                        $user,
                        $department->getNotificationEmail(),
                        $department->getName(),
                        $this->getConfig()->getNotificationStaffNewTicketTemplate($storeId),
                        $ticket->getLastMessage()->getAttachments(),
                        array(),
                        self::FLAG_NO
                    );
                }
                if ($department->getIsMembersNotificationEnabled()) {
                    foreach ($department->getUsers() as $member) {
                        //ticket created by customer. we notify member of department.
                        $this->mail(
                            $ticket,
                            $customer,
                            $member,
                            $member->getEmail(),
                            $department->getName(),
                            $this->getConfig()->getNotificationStaffNewTicketTemplate($storeId),
                            $ticket->getLastMessage()->getAttachments(),
                            array(),
                            self::FLAG_NO
                        );
                    }
                }
            }
        } else {
            $this->newMessage($ticket, $customer, $user, $triggeredBy, $messageType);
        }

        Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_TICKET, $ticket);
    }

    /**
     * Removes emails of gateways from the list
     *
     * @param array $emails
     *
     * @return array
     */
    private function stripGatewayEmails($emails)
    {
        $collection = Mage::getModel('helpdesk/gateway')->getCollection()->addFieldToFilter('is_active', 1);

        if ($collection->count() && $emails) {
            foreach ($collection as $gateway) {
                foreach ($emails as $k => $email) {
                    if ($gateway->getEmail() == $email) {
                        unset($emails[$k]);
                    }
                }
            }
        }

        return $emails;
    }

    /**
     * Send email notification about new message in the ticket.
     *
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     * @param string                         $triggeredBy
     * @param string                         $messageType
     *
     * @return void
     */
    public function newMessage($ticket, $customer, $user, $triggeredBy, $messageType)
    {
        if ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC) {
            if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(
                    Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_CUSTOMER_REPLY, $ticket);
            } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::USER) {
                $this->notifyCustomer($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(
                    Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_STAFF_REPLY, $ticket);
            }
        } elseif ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD ||
            $messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD) {
            if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::THIRD) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(
                    Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_THIRD_REPLY, $ticket);
            } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::USER) {
                $this->notifyThird($ticket, $customer, $user, $triggeredBy);
            }
        } elseif ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL) {
            /** @var Mage_Admin_Model_User $currentUser */
            $currentUser = Mage::getSingleton('admin/session')->getUser();
            Mage::helper('helpdesk/ruleevent')->newEvent(
                Mirasvit_Helpdesk_Model_Config::RULE_EVENT_TICKET_UPDATED, $ticket);
            if ($ticket->getUserId() == 0 || $ticket->getUserId() !== $currentUser->getId()) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
            }
        }
    }

    /**
     * Sends notification email.
     *
     * @param Mirasvit_Helpdesk_Model_Ticket       $ticket
     * @param Mage_Customer_Model_Customer|false   $customer
     * @param Mage_Admin_Model_User|false          $user
     * @param string                               $recipientEmail
     * @param string                               $recipientName
     * @param string                               $templateName
     * @param Mirasvit_Helpdesk_Model_Attachment[] $attachments
     * @param array                                $variables
     * @param bool                                 $emailFlag
     *
     * @return bool
     *
     * @throws Exception
     * @throws Zend_Mail_Exception
     */
    public function mail($ticket, $customer, $user, $recipientEmail, $recipientName, $templateName,
        $attachments = array(), $variables = array(), $emailFlag = false
    ) {
        if ($templateName == 'none') {
            return false;
        }

        $storeId = $ticket->getStoreId();
        $config = Mage::getSingleton('helpdesk/config');
        if ($config->getDeveloperIsActive($storeId)) {
            if ($sandboxEmail = $config->getDeveloperSandboxEmail($storeId)) {
                $recipientEmail = $sandboxEmail;
            }
        }
        $department = $ticket->getDepartment();
        $store = $ticket->getStore();

        if (!$customer) {
            $customer = $ticket->getCustomer();
        }
        if (!$user) {
            $user = $ticket->getUser();
        }

        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();
        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
        $this->_applyDesignConfig();

        $variables = array_merge($variables,
            array(
                'ticket'           => $ticket,
                'customer'         => $customer,
                'user'             => $user,
                'signature'        => $user ? $user->getSignature() : '',
                'department'       => $department,
                'store'            => $store,
                'preheader_text'   => Mage::helper('helpdesk/email')->getPreheaderText($ticket->getLastMessagePlainText()),
                'hidden_separator' => Mage::helper('helpdesk/email')->getHiddenSeparator(),
                'logo_url'         => $this->_getLogoUrl($store),
                'logo_alt'         => $this->_getLogoAlt($store),
            )
        );

        if (isset($variables['email_subject'])) {
            $variables['email_subject'] = $this->processVariable($variables['email_subject'], $variables);
        }
        if (isset($variables['email_body'])) {
            $variables['email_body'] = $this->processVariable($variables['email_body'], $variables);
        }
        if (isset($variables['signature'])) {
            $variables['signature'] = $this->processVariable($variables['signature'], $variables);
        }

        // Proper sender email names and addresses for department notification
        $senderName = $store->getFrontendName().' - '.$department->getName();
        $senderEmail = $department->getSenderEmail();

        if (!$senderEmail) {
            return;
        }
        if (!$recipientEmail) {
            return;
        }
        $template = Mage::getModel('core/email_template');
        foreach ($attachments as $attachment) {
            $template->getMail()->createAttachment($attachment->getBody(), $attachment->getType(),
                Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $attachment->getName());
        }

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        if ($emailFlag) {
            $template->getMail()->addHeader('Auto-Submitted', $emailFlag);
        }

        // We use CC and BCC only when email is sent to the customer
        if ($recipientEmail == $ticket->getCustomerEmail()) {
            if ($ccEmails = $this->stripGatewayEmails($ticket->getCc())) {
                // Mandrill NOT ABLE to send CC - so we need an overwork
                if (method_exists($template->getMail(), 'addCc')) {
                    if (count($ccEmails)) {
                        $template->getMail()->addCc($ccEmails);
                    }
                } else {
                    $recipientEmail = array_unique(array_merge((array)$recipientEmail, $ccEmails));
                }
            }

            if ($bccEmails = $this->stripGatewayEmails($ticket->getBcc())) {
                if (count($bccEmails)) {
                    $template->getMail()->addBcc($bccEmails);
                }
            }
        }

        if($generalBcc = $this->stripGatewayEmails($this->getConfig()->getGeneralBccEmail($storeId))) {
            $template->getMail()->addBcc($generalBcc);
        }

        $template->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $template
            ->sendTransactional($templateName,
                array(
                    'name' => $senderName,
                    'email' => $senderEmail,
                ),
                $recipientEmail, $recipientName, $variables, $storeId);
                $text = $template->getProcessedTemplate($variables, true);
                $this->emails[] = $text;

                $translate->setTranslateInline(true);

                // restore previous design settings
                $this->_setDesignConfig($currentDesignConfig->getData());
                $this->_applyDesignConfig();
    }

    /**
     * Processes variables in template text.
     *
     * @param string $variable
     * @param array  $variables
     *
     * @return string
     */
    public function processVariable($variable, $variables)
    {
        $template = Mage::getModel('core/email_template');
        $template->setTemplateText($variable);

        return $template->getProcessedTemplate($variables);
    }

    /**
     * Returns URL of current store logo.
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);
        $fileName = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO);
        if ($fileName) {
            $uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
            $fullFileName = Mage::getBaseDir('media').DS.$uploadDir.DS.$fileName;
            if (file_exists($fullFileName)) {
                return Mage::getBaseUrl('media').$uploadDir.'/'.$fileName;
            }
        }

        return Mage::getDesign()->getSkinUrl('images/logo_email.gif');
    }

    /**
     * Returns Alt Text of current store logo.
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        $alt = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO_ALT);
        if ($alt) {
            return $alt;
        }

        return $store->getFrontendName();
    }

    /**
     * Current design config.
     */
    protected $_designConfig;

    /**
     * Sets current email layout configuration.
     *
     * @param array $config
     *
     * @return Mirasvit_Helpdesk_Helper_Notification
     */
    protected function _setDesignConfig(array $config)
    {
        $this->_getDesignConfig()->setData($config);

        return $this;
    }

    /**
     * Returns current email layout configuration.
     *
     * @return Varien_Object
     */
    protected function _getDesignConfig()
    {
        if ($this->_designConfig === null) {
            $store = is_object(Mage::getDesign()->getStore())
                ? Mage::getDesign()->getStore()->getId()
                : Mage::getDesign()->getStore();

            $this->_designConfig = new Varien_Object(array(
                'area' => Mage::getDesign()->getArea(),
                'store' => $store,
            ));
        }

        return $this->_designConfig;
    }

    /**
     * Applies email layout configuration.
     *
     * @return Mirasvit_Helpdesk_Helper_Notification
     */
    protected function _applyDesignConfig()
    {
        $designConfig = $this->_getDesignConfig();
        $design = Mage::getDesign();
        $designConfig->setOldArea($design->getArea())
            ->setOldStore($design->getStore());

        if ($designConfig->hasData('area')) {
            Mage::getDesign()->setArea($designConfig->getArea());
        }
        if ($designConfig->hasData('store')) {
            $store = $designConfig->getStore();
            Mage::app()->setCurrentStore($store);

            $locale = new Zend_Locale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $store));
            Mage::app()->getLocale()->setLocale($locale);
            Mage::app()->getLocale()->setLocaleCode($locale->toString());
            if ($designConfig->hasData('area')) {
                Mage::getSingleton('core/translate')->setLocale($locale)
                    ->init($designConfig->getArea(), true);
            }
            $design->setStore($store);
            $design->setTheme('');
            $design->setPackageName('');
        }

        return $this;
    }
}