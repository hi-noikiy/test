<?php
class HN_Salesforce_Model_Sync_Campaign extends HN_Salesforce_Model_Connector{
		
	public function __construct() {
		parent::__construct();
		$this->_type = 'Campaign';
		$this->_table = 'catalogrule';			
	}
	
	public function sync($id) {

		$model = Mage::getModel('catalogrule/rule')->load($id);
		$name = $model->getName();

		$id = $this->searchRecords($this->_type, 'Name', trim($name));
		$params = $this->_data->getCampaign($model, $this->_type);
		$params += ['Name' => $name];

		if(!$id)
			$id = $this->createRecords($this->_type, $params);
		else
			$this->updateRecords($this->_type, $id, $params);

		return $id;
	}
}
 
	
