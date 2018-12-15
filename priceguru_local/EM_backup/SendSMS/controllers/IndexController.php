<?php
 require "Services/Twilio.php";
class EM_SendSMS_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/sendsms?id=15 
    	 *  or
    	 * http://site.com/sendsms/id/15 	
    	 */
    	/* 
		$sendsms_id = $this->getRequest()->getParam('id');

  		if($sendsms_id != null && $sendsms_id != '')	{
			$sendsms = Mage::getModel('sendsms/sendsms')->load($sendsms_id)->getData();
		} else {
			$sendsms = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($sendsms == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$sendsmsTable = $resource->getTableName('sendsms');
			
			$select = $read->select()
			   ->from($sendsmsTable,array('sendsms_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$sendsms = $read->fetchRow($select);
		}
		Mage::register('sendsms', $sendsms);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
	public function postAction()
	{
		$post = $this->getRequest()->getPost();
		if ( $post ) {
			$AccountSid = "ACb9ee028b9d88c68ab3b15a41684e334b";
			$AuthToken = "8e7c43171c282c7286635a9432b95cdf";
			$client = new Services_Twilio($AccountSid, $AuthToken);
			
			try {
				$postObject = new Varien_Object();
				$postObject->setData($post);
				$product=Mage::getModel('catalog/product')->load($postObject['phone-id']);
				$datamessage=$product->getData('em_sendsms');
				$dataphonenumber=$postObject['phone-number'];
				$people = array($dataphonenumber=>"customer");
				foreach ($people as $number => $name) {
 
					$sms = $client->account->messages->sendMessage(
					 "+1 267-396-1630", 
 
					// the number we are sending to - Any phone number
					$number,
 
					// the sms body
					$datamessage
					);
				};
				print("chay");
				die;
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('sendsms')->__('Your quote request was submitted. Thank you. An agent will contact you within one hour.'));
               $this->_redirectReferer();
                return;
			}
			catch (Exception $e) {
				print($e);
				die;
				Mage::getSingleton('customer/session')->addError(Mage::helper('sendsms')->__('Unable to submit quote request. Please, try again later'));
				 $this->_redirectReferer();
				return;
			}
		}
		else
		{
			Mage::getSingleton('customer/session')->addError(Mage::helper('sendsms')->__('notrun'));
			$this->_redirectReferer();
		}
	}
}