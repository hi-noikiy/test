<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Gearup_Configurator_IndexController extends Mage_Core_Controller_Front_Action {

    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_ENABLED = 'contacts/contacts/enabled';

    public function emailconfigurationAction() {
        $post = $this->getRequest()->getParams();

        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {                
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }              

                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->setReplyTo($post['email'])
                        ->setTemplateSubject($post['pname']. "- configurator product")
                        ->sendTransactional(13, Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER), Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT), null, array('data' => $postObject)
                );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);
                $jsonData['success'] = Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.');
            } catch (Exception $e) {
                $translate->setTranslateInline(true);
                $jsonData['error'] = Mage::helper('contacts')->__('Unable to submit your request. Please, try again later');
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($jsonData));
    }

}
