<?php
class EM_Link_IndexController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        // if( !Mage::getStoreConfigFlag(self::XML_PATH_ENABLED) ) {
            // $this->norouteAction();
        // }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('mobile-app')
            ->setFormAction( Mage::getUrl('*/*/post') );

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
	public function postAction()
    {	
        $storeId = Mage::app()->getStore()->getId();
		$templateId = 'link_email_email_template';//here you can use template id defined in XML or you can use template ID in database (would be 1,2,3,4 .....etc)
		$mailSubject = 'The link of mobile app';
		$collection3 = Mage::getModel('link/link')->getCollection();
		$collection3->addFieldToFilter('link_id', array('eq' => 1));
		foreach($collection3 as $data1)
		{
			$content= $data1->getData('content'); 
		}
		$sender = array('name' => 'support',
		'email' => 'customer-service@priceguru.com');
		
		$email = $this->getRequest()->getPost('email-app');
				if (!Zend_Validate::is($email, 'EmailAddress')) {
						Mage::getSingleton('core/session')->addError(Mage::helper('link')->__('Please enter a valid email address.'));
					}
				else
				{
					$name = 'coder-php';
					$vars['content']=$content;
					$mailTemplate = Mage::getModel('core/email_template');
					$mailTemplate->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('link')->__('Link has been send to your email. Thank you!!!'));
				}
				$this->_redirectReferer();

    }
}