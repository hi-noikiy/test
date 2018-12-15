<?php
/**
 * 
 */
class HN_Salesforce_Model_Data 
{
	protected $_field;

	public function __construct(){
		$this->_field = Mage::getSingleton('salesforce/field');
	}
	/**
	 * Select mapping
	 *
	 * @param string $magento_field
	 * @param string $_type
	 * @return array $result
	 */
	public function getMapping($data, $_type) {

		$model = Mage::getModel('salesforce/map');
		$collection = $model->getResourceCollection()
							->addFieldToFilter('type', $_type)
							->addFieldToFilter('status', 1);
		$map = [];
		$result = [];

		foreach ($collection as $key => $value) {
			$salesforce = $value->getSalesforce();
			$magento = $value->getMagento();
			$map[$salesforce] = $magento;
		}

		foreach ( $map as $key => $value ) {	
			if($data[$value])
					$result[$key] = $data[$value];
		}

		return $result;	
	}

	/** 
	 * Get Country Name
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return string
	 */
	public function getCountryName($id)
	{
        $model = Mage::getSingleton('directory/country')->loadByCode($id);

        return $model->getName();
	}	

	/** 
	 * Get all data of Customer
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param string $_table
	 * @return array
	 */
    public function getCustomer($model, $_type){

		$magento_fields = $this->_field->getMagentoFields('customer');
        $data = [];
        foreach ($magento_fields as $key => $item) {
        	$sub = substr($key, 0, 5);
        	if($sub == 'bill_' && $model->getDefaultBillingAddress())
        	{            		
        		$value = substr($key, 5);
        		$billing = $model->getDefaultBillingAddress();
        		$data[$key] = $billing->getData($value);
        	}
        	elseif($sub =='ship_' && $model->getDefaultShippingAddress())
        	{
        		$value = substr($key, 5);
        		$shipping = $model->getDefaultShippingAddress();
        		$data[$key] = $shipping->getData($value);
        	}
        	else
        		$data[$key] = $model->getData($key);
        }

        if(!empty($data['bill_country_id'])){
        	$country_id = $data['bill_country_id'];
        	$data['bill_country_id'] = $this->getCountryName($country_id);
        }
        if(!empty($data['ship_country_id'])){
        	$country_id = $data['ship_country_id'];
        	$data['ship_country_id'] = $this->getCountryName($country_id);
        }

        /* Mapping data*/
        $params = $this->getMapping($data, $_type);

        return $params; 
    }

	/** 
	 * Pass data of CatalogRule to array and return after mapping
	 *
	 * @param Mage_CatalogRule_Model_Rule $model
	 * @param string $_table
	 * @return array
	 */
	public function getCampaign($model, $_type){
		
		$magento_fields = $this->_field->getMagentoFields('catalogrule');
		$data = [];

		/* Pass data of catalog rule price to array */
		foreach ($magento_fields as $key => $item) {
			$data[$key] = $model->getData($key);
		}
		$action = [
			'by_percent' => 'By Percentage of the Original Price',
			'by_fixed' => 'By Fixed Amount',
			'to_percent' => 'To Percentage of the Original Price',
			'to_fixed' => 'To Fixed Amount',
		];
		if(!empty($data['simple_action'])){
			foreach($action as $key => $value){
				if($data['simple_action'] == $key)
					$data['simple_action'] = $value;
			}
		}
		if ($data['sub_is_enable'] == 1){
			$data['sub_is_enable'] = 'Yes';
			foreach($action as $key => $value){
				if($data['simple_action'] == $key)
					$data['simple_action'] = $value;
			}
		}
		else{
			$data['sub_is_enable'] = 'No';
		}
		
		/* Mapping data*/
		$params = $this->getMapping($data, $_type);

		return $params;
	}

    /** 
	 * Pass data of Order to array and return mapping
	 *
	 * @param Mage_Sales_Model_Order $model
	 * @param string $_table
	 * @return array
	 */
    public function getOrder($model, $_type){

		$magento_fields = $this->_field->getMagentoFields('order');
		$data = [];

		foreach ($magento_fields as $key => $item) {
        	$sub = substr($key, 0, 5);
        	if($sub == 'bill_')
        	{            		
        		$billing = $model->getBillingAddress();
        		$data[$key] = $billing->getData(substr($key, 5));
        	}
        	elseif($sub == 'ship_')
        	{
        		$shipping = $model->getShippingAddress();
        		$data[$key] = $shipping->getData(substr($key, 5));
        	}
        	else
        		$data[$key] = $model->getData($key);
    	}

        if(!empty($data['bill_country_id'])){
        	$country_id = $data['bill_country_id'];
        	$data['bill_country_id'] = $this->getCountryName($country_id);;
        }
        if(!empty($data['ship_country_id'])){
        	$country_id = $data['ship_country_id'];
        	$data['ship_country_id'] = $this->getCountryName($country_id);;
        }

        /* Mapping data*/
        $params = $this->getMapping($data, $_type);

        return $params; 
    }

    /** 
	 * Pass data of Product to array and return after mapping
	 *
	 * @param Mage_Sales_Model_Order $model
	 * @param string $_table
	 * @return array
	 */
	public function getProduct($model, $_type){
		
		$magento_fields = $this->_field->getMagentoFields('product');
		$data = [];

		/*..........Pass data of Product to array..........*/
		foreach ($magento_fields as $key => $item) {
        	$sub = substr($key, 0, 5);
        	if($sub == 'stock')
        	{
        		$stockItem =$model->getStockItem();
        		$data[$key] = $stockItem->getData(substr($key, 6));
			}
        	else
        		$data[$key] = $model->getData($key);
    	}
    	if(!empty($data['country_of_manufacture'])){
    		$country = Mage::getSingleton('directory/country');
        	$country_id = $data['country_of_manufacture'];
        	$data['country_of_manufacture'] = $this->getCountryName($country_id);
        }
        if(!empty($data['tax_class_id'])){
        	$tax_class = Mage::getSingleton('tax/class');
        	$tax_id = $data['tax_class_id'];
        	if($tax_id == 0)
        		$data['tax_class_id'] = "None";
        	else
        		$data['tax_class_id'] = $tax_class->load($tax_id)->getClassName();
        }
        /*.............End pass data...............*/
        
		// 4. Mapping data			
		$params = $this->getMapping($data, $_type);

		return $params;
	}

	/** 
	 * Pass data of Invoice to array and return after mapping
	 *
	 * @param Mage_Sales_Model_Order_Invoice $model
	 * @param string $_table
	 * @return array
	 */
	public function getInvoice($model, $_type){
		
		$magento_fields = $this->_field->getMagentoFields('invoice');
		$data = [];

		foreach ($magento_fields as $key => $item) {
        	$sub = substr($key, 0, 5);
        	if($sub == 'bill_')
        	{            		
        		$billing = $model->getBillingAddress();
        		$data[$key] = $billing->getData(substr($key, 5));
        	}
        	elseif($sub =='ship_')
        	{
        		$shipping = $model->getShippingAddress();
        		$data[$key] = $shipping->getData(substr($key, 5));
        	}
        	else{
        		$data[$key] = $model->getData($key);
        	}
    	}
    	$data['order_increment_id'] = $model->getOrderIncrementId();
        if(!empty($data['bill_country_id'])){
        	$country_id = $data['bill_country_id'];
        	$data['bill_country_id'] = $this->getCountryName($country_id);;
        }
        if(!empty($data['ship_country_id'])){
        	$country_id = $data['ship_country_id'];
        	$data['ship_country_id'] = $this->getCountryName($country_id);
        }

        /* Mapping data*/
        $params = $this->getMapping($data, $_type);

        return $params; 
	}
}