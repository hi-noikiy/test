<?php
class HN_Salesforce_Model_Sync_Contact extends HN_Salesforce_Model_Connector{	
	
	public function __construct() {
		parent::__construct();
		$this->_type = 'Contact';
		$this->_table = 'customer';
	}

	/**
	 * Update or create new a record
	 *
	 * @param int $id
	 * @param boolean $update
	 * @param Mage_Customer_Model_Customer $model
	 * @param boolean $check
	 * @return string
	 */
	public function sync($id, $update = false, $model= null, $check = true) {
		if(!$model && $id)
			$model = Mage::getSingleton('customer/customer')->load($id);

		$email = $model->getEmail();
		$firstname = $model->getFirstname();
		$lastname = $model->getLastname();

		if($check)
			$id = $this->searchRecords($this->_type, 'Email', $email);
		else
			$id = false;

		/* 1. Update Record */
		if(!$id || ($update && $id)){

			/*  	Pass data of customer to array		 */
			$params = $this->_data->getCustomer($model, $this->_type);
			$params += [
				'FirstName' => $firstname,
				'LastName' =>$lastname,
				'Email' => $email
			];

			if($update && $id)
				$this->updateRecords($this->_type, $id, $params);
			else
				$id = $this->createRecords($this->_type, $params);
		}

		return $id;
	}

	/**
	 * create new a record by email
	 *
	 * @param int $id
	 * @param boolean $update
	 * @return string
	 */
	public function syncByEmail($data) 
	{		
		$id = $this->searchRecords($this->_type, 'Email', $data['Email']);
		if(!$id){
			$params = $data;
			$id = $this->createRecords($this->_type, $params);
		}

	  	return $id;  
	}

	/**
	 * Delete Record 
	 * @param string email
	 */
	public function delete($email){
		$id = $this->searchRecords($this->_type, 'Email', $email);
		if($id)
			$this->deleteRecords('Contact', $id);
	}
}
