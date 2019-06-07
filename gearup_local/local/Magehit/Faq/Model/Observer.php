<?php
class Magehit_Faq_Model_Observer
{
	/**
     * Send notification for admin about new FAQ added
     *
     * @param Varien_Event_Observer $observer
     * @return Magehit_Faq_Model_Observer
     */
    public function sendNotificationToAdmin(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('faq')->isAdminNotificationEnabled()) {
            return $this;
        }
		//Mage::log('FAQ 4');
        $adminEmail = Mage::helper('faq')->getAdminEmail();
        $subject = Mage::helper('faq')->getAdminEmailSubject();
        $templateId = 'faq_email_admin_email_template';//Mage::helper('faq')->getAdminEmailTemplate();
        $senderId = Mage::helper('faq')->getAdminNotificationSendFrom();

        $storeId = Mage::app()->getStore()->getId();
        
        $vars = array(
            'admin_subject' => $subject,
            'store_view' => Mage::app()->getStore()->getFrontendName()
        );

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject)
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $templateId,
                $senderId,
                $adminEmail,
                Mage::helper('faq')->__('Store Administrator'),
                $vars
        );

        $translate->setTranslateInline(true);

        return $this;
    }
}
