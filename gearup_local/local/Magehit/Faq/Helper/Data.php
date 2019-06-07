<?php

class Magehit_Faq_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
     * Path to store config admin email notification enable
     *
     * @var string
     */
    const XML_ADMIN_EMAIL_ENABLED                = 'magehit_faq/email_admin/send_enable';
    /**
     * Path to store config send email for admin from
     *
     * @var string
     */
    const XML_ADMIN_EMAIL_SEND_FROM              = 'magehit_faq/email_admin/send_from';
    /**
     * Path to store config admin email
     *
     * @var string
     */
    const XML_ADMIN_EMAIL              = 'magehit_faq/email_admin/admin_email';
    /**
     * Path to store config admin email subject
     *
     * @var string
     */
    const XML_ADMIN_EMAIL_SUBJECT              = 'magehit_faq/email_admin/email_subject';
    /**
     * Path to store config admin email template
     *
     * @var string
     */
    const XML_ADMIN_EMAIL_TEMPLATE              = 'magehit_faq/email_admin/email_template';
	
	
	public function getPageTitle(){
    	return Mage::getStoreConfig('magehit_faq/general/page_title') ? Mage::getStoreConfig('magehit_faq/general/page_title'):"";
	}

	public function getKeyword(){
    	return Mage::getStoreConfig('magehit_faq/general/faq_keyword') ? Mage::getStoreConfig('magehit_faq/general/faq_keyword'):"";
	}
	
	public function getDescription(){
		return Mage::getStoreConfig('magehit_faq/general/faq_description') ? Mage::getStoreConfig('magehit_faq/general/faq_description'):"";
	}
	
	public function getRobot(){
		return Mage::getStoreConfig('magehit_faq/general/faq_robots') ? Mage::getStoreConfig('magehit_faq/general/faq_robots'):"INDEX,FOLLOW";
	}

	/**
     * Checks if send email for admin enabled
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return boolean
     */
    public function isAdminNotificationEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_ADMIN_EMAIL_ENABLED, $store);
    }
    /**
     * Return admin email send from contact
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return String
     */
    public function getAdminNotificationSendFrom($store = null)
    {
        return Mage::getStoreConfig(self::XML_ADMIN_EMAIL_SEND_FROM, $store);
    }
    /**
     * Return admin email
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return String
     */
    public function getAdminEmail($store = null)
    {
        return Mage::getStoreConfig(self::XML_ADMIN_EMAIL, $store);
    }
    /**
     * Return admin email subject
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return String
     */
    public function getAdminEmailSubject($store = null)
    {
        return Mage::getStoreConfig(self::XML_ADMIN_EMAIL_SUBJECT, $store);
    }
    /**
     * Return admin email template
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return String
     */
    public function getAdminEmailTemplate($store = null)
    {
        return Mage::getStoreConfig(self::XML_ADMIN_EMAIL_TEMPLATE, $store);
    }
	
}