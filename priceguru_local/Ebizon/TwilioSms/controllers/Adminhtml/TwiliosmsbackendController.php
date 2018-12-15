<?php
class Ebizon_TwilioSms_Adminhtml_TwiliosmsbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
		$read_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$select = $read_connection->select()
		->from('twilio_sms', array('*')) // select * from tablename or use array('id','title') selected values
		->where('id=?',1);               // where id =1
		$rowArray =$read_connection->fetchRow($select);   //return row
		
		if(!empty($_POST['form_submit']))
		{
			if(empty($rowArray)){
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
				$connection->beginTransaction();
				$__fields = array();
				$__fields['accounts_id'] = $_POST['accounts_id'];
				$__fields['auth_token'] = $_POST['auth_token'];
				$__fields['from_number'] = $_POST['from_number'];
				$__fields['status'] = $_POST['is_active'];
				$__fields['order_sms'] = $_POST['order_sms'];
				$__fields['order_complete_sms'] = $_POST['order_complete_sms'];
				$__fields['cim_credit_sms'] = $_POST['cim_credit_sms'];
				$__fields['cim_process_sms'] = $_POST['cim_process_sms'];
				$__fields['cim_complete_sms'] = $_POST['cim_complete_sms'];
				$connection->insert('twilio_sms', $__fields);
				$connection->commit();
				Mage::getSingleton('core/session')->addSuccess('Data has been updated.');
			}
			elseif(!empty($rowArray)){
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
				$connection->beginTransaction();
				$__fields = array();
				$__fields['accounts_id'] = $_POST['accounts_id'];
				$__fields['auth_token'] = $_POST['auth_token'];
				$__fields['from_number'] = $_POST['from_number'];
				$__fields['status'] = $_POST['is_active'];
				$__fields['order_sms'] = $_POST['order_sms'];
				$__fields['order_complete_sms'] = $_POST['order_complete_sms'];
				$__fields['cim_credit_sms'] = $_POST['cim_credit_sms'];
				$__fields['cim_process_sms'] = $_POST['cim_process_sms'];
				$__fields['cim_complete_sms'] = $_POST['cim_complete_sms'];
				$__where = $connection->quoteInto('id =?', '1');
				$connection->update('twilio_sms', $__fields, $__where);
				$connection->commit();
				Mage::getSingleton('core/session')->addSuccess('Data has been updated.'); 
			}
		}		
		
		$rowArray =$read_connection->fetchRow($select);   //return row
		Mage::register('rowArray', $rowArray);
		
       /*$this->loadLayout();
	   $this->_title($this->__("Twilio Sms"));
	   $this->renderLayout();*/
		$this->loadLayout();
		$this->_title($this->__("Twilio SMS"));
		$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','twiliosmsbackend', array('template' => 'twiliosms/twiliosmsbackend.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
    }
}
