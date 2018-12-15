<?php
class HN_Salesforce_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
    	$this->loadLayout();
    	$this->getLayout()->createBlock('sync_old_data');
		$this->renderLayout ();			
	}

	public function syncAction() {

		$post = $this->getRequest()->getParams();
		if($post && !empty($post['type'])){
			$table = Mage::getSingleton('salesforce/field')->getAllTable();
			$type = $post['type'];
			$from_id = (int) $post['from_id'];
			$to_id = (int) $post['to_id'];
			if($to_id < $from_id){
				Mage::getSingleton('adminhtml/session')->addError(
					Mage::helper('salesforce')->__('Value of "To Id" great than "From Id" !')
				);
				$this->_redirect('*/*/');
				return;
			}
				
			$check = $post['check']==1 ? true : false;
			$value = $table[$type];

			switch($value){
				case 'customer':
					$collection = Mage::getResourceModel('customer/customer_collection')
													->addAttributeToSelect('*')
													->addAttributeToFilter('entity_id', ['from' => $from_id, 'to' => $to_id]);
					break;
				case 'product':
					$collection = Mage::getResourceModel('catalog/product_collection')
													->addAttributeToSelect('*')
													->addAttributeToFilter('entity_id', ['from' => $from_id, 'to' => $to_id]);
					break;
				case 'order':
					$collection = Mage::getResourceModel('sales/order_collection')
													->addAttributeToSelect('*')
													->addAttributeToFilter('increment_id', ['from' => $from_id, 'to' => $to_id]);
					break;
				case 'invoice':
					$collection = Mage::getResourceModel('sales/order_invoice_collection')
													->addAttributeToSelect('*')
													->addAttributeToFilter('increment_id', ['from' => $from_id, 'to' => $to_id]);
					break;
				
				default:
					break;
			}

			$custom_product = Mage::getStoreConfig(HN_Salesforce_Model_Sync_CustomProduct::XML_PATH_SALESFORCE_CUSTOM_PRODUCT);
			$custom_invoice = Mage::getStoreConfig(HN_Salesforce_Model_Sync_CustomInvoice::XML_PATH_SALESFORCE_CUSTOM_INVOICE);
			$custom_customer = Mage::getStoreConfig(HN_Salesforce_Model_Sync_CustomCustomer::XML_PATH_SALESFORCE_CUSTOM_CUSTOMER);

			switch($type){
				case $custom_product:
					$type = 'customProduct';
					break;
				case $custom_invoice:
					$type = 'customInvoice';
					break;
				case $custom_customer:
					$type = 'customCustomer';
					break;
				case 'Product2':
					$type = 'product';
					break;
				default:
					$type = strtolower($type);
					break;
			}
			$load_model = 'salesforce/sync_'.$type;
            $model = Mage::getModel($load_model);
			$count = count($collection);
			echo "Need sync ".$count." records.</br>";
			$i = 1;
			foreach($collection as $_collect)
            {
				$id_salesforce = $model->sync(false, false, $_collect, $check);
				echo $i.'/'.$count.'</br>';
                echo "Sync from ID: ".$_collect->getId().' to Id(Salesforce): '.$id_salesforce."</br></br>	";
				$i++;
				flush();
				ob_flush();
			}

			exit();
		}
	
		$this->_redirect('*/*/');
	}
}
