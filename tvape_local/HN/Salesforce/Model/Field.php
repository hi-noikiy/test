<?php

use HN_Salesforce_Model_Sync_CustomCustomer as CustomCustomer;
use HN_Salesforce_Model_Sync_CustomProduct as CustomProduct;
use HN_Salesforce_Model_Sync_CustomInvoice as CustomInvoice;
use HN_Salesforce_Model_Observer as Observer;

class HN_Salesforce_Model_Field extends Mage_Core_Model_Abstract {

	public function _construct() {
		parent::_construct ();
		$this->_init ( 'salesforce/field' );
	}

	public function setSalesforceFields($s_table)
	{
		$model = Mage::getModel('salesforce/connector');
		$s_fields = $model->getFields($s_table);

		return $s_fields;
	}

	public function saveFields($s_table, $m_table, $update = false)
	{
		$salesforce = $this->setSalesforceFields($s_table);
		$data = [
			'type' => $s_table,
			'salesforce' => $salesforce,
			'magento' =>  $m_table,
			'status' => 1
		];
		$id = $this->loadByTable($s_table)->getId();
		if($id && $update)
			$this->addData($data);
		else
			$this->setData($data);

		$this->save();

		return $this;
	}

	public function getAllTable()
	{
		$table = [
			'Account' => 'customer',
			'Contact' => 'customer',
			'Campaign' => 'catalogrule',
			'Lead' => 'customer',
			'Product2'=> 'product',
			'Order' => 'order'
		];
		$customCustomer = Mage::getStoreConfig(CustomCustomer::XML_PATH_SALESFORCE_CUSTOM_CUSTOMER);
		$customProduct = Mage::getStoreConfig(CustomProduct::XML_PATH_SALESFORCE_CUSTOM_PRODUCT);
		$customInvoice = Mage::getStoreConfig(CustomInvoice::XML_PATH_SALESFORCE_CUSTOM_INVOICE);

		if($customCustomer && Mage::getStoreConfigFlag(Observer::XML_PATH_SYNC_CUSTOM_CUSTOMER))
			$table[$customCustomer] = 'customer';
		if($customProduct && Mage::getStoreConfigFlag(Observer::XML_PATH_SYNC_CUSTOM_PRODUCT))
			$table[$customProduct] = 'product';
		if($customInvoice && Mage::getStoreConfigFlag(Observer::XML_PATH_SYNC_CUSTOM_INVOICE))
			$table[$customInvoice] = 'invoice';
		return $table;
	}
	public function changeFields(){
		$table = $this->getAllTable();
		$data = ['' => '--- Select Option ---'];
		foreach ($table as $key => $value) {
			$length = strlen($key);
			$subkey = substr($key, $length - 3 , $length);
			if($subkey == '__c')
				$data[$key] = substr($key, 0, $length - 3);
			elseif($key == 'Product2')
				$data[$key] = 'Product';
			else
				$data[$key] = $key;
		}
		return $data;
	}
	public function getMagentoFields($m_table)
	{
		return $this->setMagentoFields($m_table);
	}

	public function getSalesforceFields($s_table)
	{
		if(!$this->loadByTable($s_table)->getId()){
			$alltable = $this->getAllTable();
			$m_table = $alltable[$s_table];
			$this->saveFields($s_table, $m_table);
		}
			
		$salesFields = $this->getSalesforce();
		return unserialize($salesFields);
	}


	public function loadByTable($s_table)
	{
		return $this->load($s_table, 'type');
	}
	
	public function setMagentoFields($table)
	{
		$m_fields = [];
		
		switch ($table) {
			case 'customer':
				$m_fields =[
					'entity_id' => 'ID',
					'email' => 'Email',
					'created_at' => 'Created At',
					'update_at' => 'Updated At',
					'is_active' => 'is Active',
					'created_in' => 'Created in',
					'prefix' => 'Prefix',
					'firstname' => 'First name',
					'middlename' => 'Middle Name/Initial',
					'lastname' => 'Last name',
					'taxvat' => 'Tax/VAT Number',
					'gender' => 'Gender',
					'dob' => 'Date of Birth',
					'bill_firstname' => 'Billing First Name',
					'bill_middlename' => 'Billing Middle Name',
					'bill_lastname' => 'Billing Last Name',
					'bill_company' => 'Billing Company',
					'bill_street' => 'Billing Street',
					'bill_city' => 'Billing City',
					'bill_region' => 'Billing State/Province',
					'bill_country_id' => 'Billing Country',
					'bill_postcode' => 'Billing Zip/Postal Code',
					'bill_telephone' => 'Billing Telephone',
					'bill_fax' => 'Billing Fax',
					'ship_firstname' => 'Shipping First Name',
					'ship_middlename' => 'Shipping Middle Name',
					'ship_lastname' => 'Shipping Last Name',					
					'ship_company' => 'Shipping Company',
					'ship_street' => 'Shipping Street',
					'ship_city' => 'Shipping City',
					'ship_region' => 'Shipping State/Province',
					'ship_country_id' => 'Shipping Country',
					'ship_postcode' => 'Shipping Zip/Postal Code',
					'ship_telephone' => 'Shipping Telephone',
					'ship_fax' => 'Shipping Fax',
					'vat_id' => 'VAT number',
				];
				break;

			case 'catalogrule':
				$m_fields = [
					'rule_id' => 'Rule Id',
					'description' => 'Description',
					'from_date' => 'From Date',
					'to_date' => 'To Date',
					'is_active' => 'Active',
					'simple_action' => 'Simple Action(Apply)',
					'discount_amount' => 'Discount Amount',
					'sub_is_enable' => 'Enable Discount to Subproducts',
					'sub_simple_action' => 'Subproducts Simple Action(Apply)',
					'sub_discount_amount' => 'Subproducts Discount Amount'
				];
				break;

			case 'product': 
				$m_fields = [
					'name' => 'Name',
					'description' => 'Description',
					'short_description' => 'Short Description',
					'sku' => 'SKU',
					'weight' => 'Weight',
					'news_from_date' => 'Set Product as New from Date',
					'news_to_date' => 'Set Product as New to Date',
					'status' => 'Status',
					'country_of_manufacture' => 'Country of Manufacture',
					'url_key' => 'URL Key',
					'price' => 'Price',
					'special_price' => 'Special Price',
					'special_from_date' => 'Special From Date',
					'special_to_date' => 'Special To Date',
					'stock_stock_id' => 'Stock Id',
					'stock_qty' => 'Qty',
					'meta_title' => 'Meta Title',
					'meta_keyword' => 'Meta Keywords',
					'meta_description' => 'Meta Description',
					'tax_class_id' => 'Tax Class',
					'image' => 'Base Image',
					'small_image' => 'Small Image',
					'thumbnail' => 'Thumbnail',
				];
				break;

			case 'order':
				$m_fields = [
					'entity_id' => 'ID',
					'state' => 'State',
					'status' => 'Status',
					'coupon_code' => 'Coupon Code',
					'coupon_rule_name' => 'Coupon Rule Name',
					'increment_id' => 'Increment ID',
					'created_at' => 'Created At',
					'company' => 'Company',	
					'customer_firstname' => 'Customer First Name',	
					'customer_middlename' => 'Customer Middle Name',
					'customer_lastname'	=> 'Customer Last Name',
					'bill_firstname' => 'Billing First Name',
					'bill_middlename' => 'Billing Middle Name',
					'bill_lastname' => 'Billing Last Name',
					'bill_company' => 'Billing Company',				
					'bill_street' => 'Billing Street',
					'bill_city' => 'Billing City',
					'bill_region' => 'Billing State/Province',
					'bill_postalcode' => 'Billing Zip/Postal Code',
					'bill_telephone' => 'Billing Telephone',
					'bill_country_id' => 'Billing Country',
					'ship_firstname' => 'Shipping First Name',
					'ship_middlename' => 'Shipping Middle Name',
					'ship_lastname' => 'Shipping Last Name',					
					'ship_company' => 'Shipping Company',
					'ship_street' => 'Shipping Street',
					'ship_city' => 'Shipping City',
					'ship_region' => 'Shipping State/Province',
					'ship_postalcode' => 'Shipping Zip/Postal Code',
					'ship_country_id' => 'Shipping Country',
					'shipping_amount' => 'Shipping Amount',
					'shipping_description' => 'Shipping Description',
					'order_currency_code' => 'Currency Code',
					'total_item_count' => 'Total Item Count',
					'store_currency_code' => 'Store Currency Code',
					'shipping_discount_amount' => 'Shipping Discount Amount',
					'discount_description' => 'Discount Description',
					'shipping_method' => 'Shipping Method',
					'store_name' => 'Store Name',
					'discount_amount' => 'Discount Amount',
					'tax_amount' => 'Tax Amount',								
					'subtotal' => 'Sub Total',
					'grand_total' => 'Grand Total',
					'remote_ip' => 'Remote IP',
				];
				break;

			case 'invoice':
				$m_fields = [
					'entity_id' => 'ID',
					'state' => 'State',
					'increment_id' => 'Increment ID',
					'order_id' => 'Order ID',
					'created_at' => 'Created At',
					'updated_at' => 'Updated At',
					'company' => 'Company',	
					'customer_firstname' => 'Customer First Name',	
					'customer_middlename' => 'Customer Middle Name',
					'customer_lastname'	=> 'Customer Last Name',
					'bill_firstname' => 'Billing First Name',
					'bill_middlename' => 'Billing Middle Name',
					'bill_lastname' => 'Billing Last Name',
					'bill_company' => 'Billing Company',				
					'bill_street' => 'Billing Street',
					'bill_city' => 'Billing City',
					'bill_region' => 'Billing State/Province',
					'bill_postalcode' => 'Billing Zip/Postal Code',
					'bill_telephone' => 'Billing Telephone',
					'bill_country_id' => 'Billing Country',
					'ship_firstname' => 'Shipping First Name',
					'ship_middlename' => 'Shipping Middle Name',
					'ship_lastname' => 'Shipping Last Name',					
					'ship_company' => 'Shipping Company',
					'ship_street' => 'Shipping Street',
					'ship_city' => 'Shipping City',
					'ship_region' => 'Shipping State/Province',
					'ship_postalcode' => 'Shipping Zip/Postal Code',
					'ship_country_id' => 'Shipping Country',
					'shipping_amount' => 'Shipping Amount',
					'order_currency_code' => 'Currency Code',
					'total_qty' => 'Total Qty',
					'store_currency_code' => 'Store Currency Code',
					'discount_description' => 'Discount Description',
					'shipping_method' => 'Shipping Method',
					'shipping_incl_tax' => 'Shipping Tax',
					'discount_amount' => 'Discount Amount',
					'tax_amount' => 'Tax Amount',								
					'subtotal' => 'Sub Total',
					'grand_total' => 'Grand Total',
					'remote_ip' => 'Remote IP',
				];

			default:
				break;
		}

		return $m_fields;
	}
}