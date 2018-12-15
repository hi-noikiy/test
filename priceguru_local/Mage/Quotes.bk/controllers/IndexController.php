<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Questions
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Questions index controller
 *
 * @category   Mage
 * @package    Mage_Questions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Quotes_IndexController extends Mage_Core_Controller_Front_Action
{

    const XML_PATH_EMAIL_RECIPIENT  = 'quotes/email/recipient_email';
    const XML_PATH_EMAIL_SENDER     = 'quotes/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'quotes/email/email_template';
	const XML_PATH_EMAIL_TEMPLATE2   = 'quotes/email/email2_template';
    const XML_PATH_ENABLED          = 'quotes/quotes/enabled';

    public function preDispatch()
    {
        parent::preDispatch();

        if( !Mage::getStoreConfigFlag(self::XML_PATH_ENABLED) ) {
            $this->norouteAction();
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('quoteForm')
            ->setFormAction( Mage::getUrl('*/*/post') );

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);


                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->addBcc(array('Solutions' => 'anhdung18031991@yahoo.com>'))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE2),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

				if($postObject['merchant_email'])
				{
					$mailTemplate1 = Mage::getModel('core/email_template');
					$mailTemplate1->setDesignConfig(array('area' => 'frontend'))
					->addBcc(array('Solutions' => 'anhdung18031991@yahoo.com>'))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE2),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                       $postObject['merchant_email'],
                        null,
                        array('data' => $postObject)
                    );		
				}
				if($postObject['email-new'])
				{
					$mailTemplate2 = Mage::getModel('core/email_template');
					$mailTemplate2->setDesignConfig(array('area' => 'frontend'))
					->addBcc(array('Solutions' => 'anhdung18031991@yahoo.com>'))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                       $postObject['email-new'],
                        null,
                        array('data' => $postObject)
                    );
				}
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('quotes')->__('Your quote request was submitted. Thank you. An agent will contact you within one hour.'));
               $this->_redirectReferer();
                return;
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError(Mage::helper('quotes')->__('Unable to submit quote request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

}
