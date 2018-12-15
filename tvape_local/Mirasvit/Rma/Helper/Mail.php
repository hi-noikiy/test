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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Mail
{
    /**
     * @var array $emails
     */
    public $emails = array();

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return string
     */
    protected function getSender()
    {
        return $this->getConfig()->getNotificationSenderEmail();
    }

    /**
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $variables
     * @param int $storeId
     * @param string $code
     * @param array $attachments
     * @return bool
     */
    protected function send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables,
        $storeId, $code, $attachments
    ) {
        if (!$senderEmail || !$recipientEmail || $templateName == 'none') {
            return false;
        }

        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();

        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $this->_applyDesignConfig();

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $isActiveHelpdesk = $this->getConfig()->isActiveHelpdesk();
        $variables = array_merge($variables, array(
            'logo_url' => $this->_getLogoUrl($storeId),
            'logo_alt' => $this->_getLogoAlt($storeId),
            'hidden_separator' => $isActiveHelpdesk ? Mage::helper('helpdesk/email')->getHiddenSeparator() : '',
            'hidden_code' => $isActiveHelpdesk ? Mage::helper('helpdesk/email')->getHiddenCode($code) : '',
        ));

        $template = Mage::getModel('core/email_template');
        foreach ($attachments as $attachment) {
            $template->getMail()->createAttachment($attachment->getBody(),
                $attachment->getType())->filename = $attachment->getName();
        }

        // All notificators are auto-submitted and not eligible to fetch by Help Desk, if integration enabled
        $template->getMail()->addHeader('Auto-Submitted', true);

        if (strpos($recipientEmail, ',')) {
            $recipientEmail = explode(', ', $recipientEmail);
        }

        // Add blind carbon copy of all emails if such exists
        $bcc = $this->getConfig()->getNotificationSendEmailBcc();
        if ($bcc) {
            if (strpos($bcc, ',')) {
                $bcc = explode(', ', $bcc);
            }
            // Compatibility fix for old Mandrill API
            if (method_exists($template->getMail(), 'addBcc')) {
                if (count($bcc)) {
                    $template->getMail()->addBcc($bcc);
                }
            } else {
                $recipientEmail = array_unique(array_merge((array) $recipientEmail, $bcc));
            }
        }

        $template->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                 ->sendTransactional($templateName,
                 array(
                     'name' => $senderName,
                     'email' => $senderEmail,
                 ),
                 $recipientEmail, $recipientName, $variables);

                 $text = $template->getProcessedTemplate($variables, true);

                 $this->emails[] = array('text' => $text, 'recipient_email' => $recipientEmail,
                     'recipient_name' => $recipientName);
                 $translate->setTranslateInline(true);
                 // restore previous design settings
                 $this->_setDesignConfig($currentDesignConfig->getData());
                 $this->_applyDesignConfig();

                 return true;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param string $comment
     * @return void
     */
    public function sendNotificationCustomerEmail($rma, $comment)
    {
        $attachments = array();
        if (is_object($comment)) {
            $attachments = $comment->getAttachments();
            $comment = $comment->getTextHtml();
        }
        $storeId = $rma->getOrder()->getId() ? Mage::helper('rma')->getStoreByOrder($rma->getOrder())->getId()
            : $rma->getStore()->getId();
        Mage::app()->setCurrentStore($storeId);
        $templateName = $this->getConfig()->getNotificationCustomerEmailTemplate($storeId);

        $recipientEmail = $rma->getEmail();
        $recipientName = $rma->getName();
        $variables = array(
            'customer' => $rma->getCustomer(),
            'rma' => $rma,
            'store' => $rma->getStore(),
        );
        $comment = $this->processVariable($comment, $variables, $storeId);
        $variables['comment'] = $comment;

        $senderName = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/email");

        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,
            $rma->getCode(), $attachments);
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param string $comment
     * @return void
     */
    public function sendNotificationAdminEmail($rma, $comment)
    {
        $attachments = array();
        if (is_object($comment)) {
            $attachments = $comment->getAttachments();
            $comment = $comment->getTextHtml();
        }

        $storeId = $rma->getOrder()->getId() ? Mage::helper('rma')->getStoreByOrder($rma->getOrder())->getId()
            : $rma->getStore()->getId();
        Mage::app()->setCurrentStore($storeId);

        $templateName = $this->getConfig()->getNotificationAdminEmailTemplate($storeId);
        if ($user = $rma->getUser()) {
            $recipientEmail = $user->getEmail();
        } else {
            return;
        }

        $recipientName = '';

        $variables = array(
            'customer' => $rma->getCustomer(),
            'rma' => $rma,
            'store' => $rma->getStore(),
        );
        $comment = $this->processVariable($comment, $variables, $storeId);
        $variables['comment'] = $comment;

        $senderName = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/email");
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,
            $rma->getCode(), $attachments);
    }

    /**
     * @param string                  $recipientEmail
     * @param string                  $recipientName
     * @param Mirasvit_Rma_Model_Rule $rule
     * @param Mirasvit_Rma_Model_Rma  $rma
     * @return void
     */
    public function sendNotificationRule($recipientEmail, $recipientName, $rule, $rma)
    {
        $attachments = array();
        $text = '';
        if ($comment = $rma->getLastComment()) {
            if ($rule->getIsSendAttachment()) {
                $attachments = $comment->getAttachments();
            }

            $text = $comment->getTextHtml();
        }

        $storeId = $rma->getOrder()->getId() ? Mage::helper('rma')->getStoreByOrder($rma->getOrder())->getId()
            : $rma->getStore()->getId();
        Mage::app()->setCurrentStore($storeId);
        $templateName = $this->getConfig()->getNotificationRuleTemplate($storeId);

        $variables = array(
            'customer' => $rma->getCustomer(),
            'rma' => $rma,
            'store' => $rma->getStore(),
        );
        $text = $this->processVariable($text, $variables, $storeId);
        $variables['email_subject'] = $this->processVariable($rule->getEmailSubject(), $variables, $storeId);
        $variables['email_body'] = $this->processVariable($rule->getEmailBody(), $variables, $storeId);
        $variables['comment'] = $text;
        $variables['email_subject'] = $this->processVariable($variables['email_subject'], $variables, $storeId);
        $variables['email_body'] = $this->processVariable($variables['email_body'], $variables, $storeId);

        // Add this message to the history
        $rma->addComment($variables['email_body'], true, null, null, false, true, false);

        $senderName = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/email");
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,
            $rma->getCode(), $attachments);
    }

    /**
     * Can parse template and return ready text.
     *
     * @param string $variable  - text with variables like {{var customer.name}}
     * @param array  $variables - array of variables
     * @param int    $storeId
     *
     * @return string - ready text
     */
    public function processVariable($variable, $variables, $storeId)
    {
        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();

        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $this->_applyDesignConfig();
        $template = Mage::getModel('core/email_template');
        $template->setTemplateText($variable);
        $html = $template->getProcessedTemplate($variables);
        // restore previous design settings
        $this->_setDesignConfig($currentDesignConfig->getData());
        $this->_applyDesignConfig();

        return $html;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);
        $fileName = $store->getConfig('design/email/logo');
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
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        $alt = $store->getConfig('design/email/logo_alt');
        if ($alt) {
            return $alt;
        }

        return $store->getFrontendName();
    }

    /**
     * @var Varien_Object
     */
    protected $_designConfig;

    /**
     * @param array $config
     * @return $this
     */
    protected function _setDesignConfig(array $config)
    {
        $this->_getDesignConfig()->setData($config);

        return $this;
    }

    /**
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
                'theme' => Mage::getDesign()->getTheme('template'),
                'package_name' => Mage::getDesign()->getPackageName(),
            ));
        }

        return $this->_designConfig;
    }

    /**
     * @return $this
     * @throws Mage_Core_Exception
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
        if ($designConfig->hasData('theme')) {
            Mage::getDesign()->setTheme($designConfig->getTheme());
        }
        if ($designConfig->hasData('package_name')) {
            Mage::getDesign()->setPackageName($designConfig->getPackageName());
        }

        return $this;
    }

    /************************/

    /**
     * @param string                 $text
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    public function parseVariables($text, $rma)
    {
        $variables = array(
            'rma' => $rma,
        //            'order' => $rma->getOrder(),
            'status' => $rma->getStatus(),
            'customer' => $rma->getCustomer(),
            'store' => $rma->getStore(),
        );
        $text = $this->processVariable($text, $variables, $rma->getStore()->getId());

        return $text;
    }
}
