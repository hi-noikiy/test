<?php
class HN_Salesforce_Model_Sync_Account extends HN_Salesforce_Model_Connector{
	
	public function __construct() {
		parent::__construct();
		$this->_type = 'Account';	
		$this->_table = 'customer';			
	}	
		
    /**
     * Update or create new a record
     *
     * @param int $id
     * @param boolean $update
	 * @param Mage_Customer_Model_Customer $model
	 * @param boolen $check
     * @return string
     */ 
	public function sync($id, $update = false, $model= null, $check = true) {

		if(!$model && $id)
			$model = Mage::getSingleton('customer/customer')->load($id);

		$email = $model->getEmail();
		if($check){
			$id = $this->searchRecords($this->_type, 'Name', $email);
			if($update && !$id)
				return;
		}
		else
			$id = false;
		if(!$id || ($update && $id)){

			/*  	Pass data of customer to array		 */
			$params = $this->_data->getCustomer($model, $this->_type);
            $params += [
                    'Name' => $email,
					'AccountNumber' => $model->getId(),
            ];
			if($update && $id)
				$this->updateRecords($this->_type, $id, $params);
			else
				$id = $this->createRecords($this->_type, $params);
		}

		return $id;
	}

    /**
     * Create new a record by email
     *
     * @param int $id
     * @return string
     */ 
	public function syncByEmail($email) 
	{		
		$id = $this->searchRecords($this->_type, 'Name', $email);
		if(!$id){
			$params = ['Name' => $email];
			$id = $this->createRecords($this->_type, $params);
		}

	  	return $id;  
	}

	/**
	 * Delete Record
	 * @param string email
	 */
	public function delete($email){
		$id = $this->searchRecords($this->_type, 'Name', $email);
		if($id)
			$this->deleteRecords($this->_type, $id);

		return;
	}
}
