<?php
class HN_Salesforce_Model_Sync_CustomCustomer extends HN_Salesforce_Model_Connector{
	
	const XML_PATH_SALESFORCE_CUSTOM_CUSTOMER = 'salesforce/custom/customer';
	const XML_PATH_SALESFORCE_CUSTOM_CUSTOMER_UNIQUE = 'salesforce/custom/unique_customer';

	public function __construct() {
		parent::__construct();
		$this->_type = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_CUSTOMER);
		$this->_key = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_CUSTOMER_UNIQUE);
		if(!$this->_type || !$this->_key){
			return;
		}	
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
	public function sync($id, $update = false, $model = null, $check = true) {

		if(!$model && $id)
			$model = Mage::getSingleton('customer/customer')->load($id);

		$email = $model->getEmail();
        if($check)
            $id = $this->searchRecords($this->_type, $this->_key, $email);
        else
            $id = false;

        if(!$id || ($update && $id))
        {
			/*  	Pass data of customer to array		 */
			$params = $this->_data->getCustomer($model, $this->_type);
			$params['Name'] = $model->getName();

            if($update && $id)
            	$this->updateRecords($this->_type, $id, $params);
            else
                $id = $this->createRecords($this->_type, $params);
        }

		return $id;
	}

	/**
	 * Delete Record 
	 * @param string email
	 */
	public function delete($email){
		$id = $this->searchRecords($this->_type, $this->_key, $email);
		if($id)
			$this->deleteRecords($this->_type, $id);
	}

}
