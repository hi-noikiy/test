<?php
/*
  IndexController.php

  OneSaas Connect API 1.0.6.10 for Magento 1.5.1.0
  http://www.onesaas.com

  Copyright (c) 2012 oneSaas

  Version change:
  1.0.6.5 	- Fixed xml encoding for product sku
  			- Added xml formatting if DOM is installed

  1.0.6.6 	- Added htmlspecialchars for all fields left
  			- Removed xml formatting with DOM as it returns an empty doc if the xml is broken (impossible to debug)

  1.0.6.7	- Updated support for pushing Shipping Tracking and Product Stock (also in batch mode)
  			- Added Tax on Shipping Amount for order requests
			- Added parsing LastUpdatedTime as UCT
			- Changed Discount Item to return discount per item instead of line

  1.0.6.8	- Updated support for Product Stock to handle both new batch mode and previous single mode

  1.0.6.9	- Updated Contact Information in OrderResponse.xml to use the first not empty name (first, last) for customer name out of :order->getFirstName, order->getBillingAddress, order->getShippingAddress
  				Necessary as some Magento extensions (M2Pro EBay) do not add name (first, last) in the order table but only in the order addresses

  1.0.6.10	- Updated Order Information in OrderResponse.xml.  Added <Order><Address> for shipping and billing addresses.  If these are not present, OneSaas will use the default adresses for the contact
  
  1.0.6.11	- Changed all currency amounts to be reported in the store base currency (fields in db with prefix "base_" instead of customer selected currency amounts.
  
  1.0.6.12	- Changed order addresses to prevent "Call to a member function on a non-object".
  
  1.0.6.13	- Changed order address street to prevent Notice: Undefined offset in some new php installations
  
  1.0.6.14	- Added Store Credit Discount to Order Discount
  
  1.0.6.15	- Added support for CostPrice (Buying Price)
			- Fixed error in using LastUpdatedTime & OrderCreatedTime
  
  1.0.6.16	- Changed Shipping method
  
  1.0.6.17 - Changed Tax Information reported by Orders and Settings Actions to be compatible with "Tax code mapping upgrade for shopping carts" (https://trello.com/c/w4KjuzAY/1480-tax-code-mapping-upgrade-for-shopping-carts-as-per-description-and-photo-of-whiteboard-session)
  
  1.0.6.18 - Added support for pushing products
		   - Update for Jira SB-18 - Returning new order-update as update of original.
  
  2.0.6.21 - Fixed issues with dynamically generated SKU for bundle products in Orders
*/

class Onesaas_Connect_IndexController extends Mage_Core_Controller_Front_Action{

	private $pageNo;
	private $pageSize;
	private $prodId;
	private $lstUpdTm;
	private $ordCreatedTm;
	private $xml = '';

	public function indexAction(){
		
		// Initialize XML Response
		header("Content-type:application/xml");
		$this->xml = '<?xml version="1.0" encoding="utf-8"?>';
		$this->xml .= '<OneSaas version="' . $this->getOneSaasConnectVersion() . '">';

		// Verify AccessKey
		if($this->verifyAccessKey($_GET['AccessKey'])) {

			// Parse parameters and initiliase variables
			$OrderCreatedTime = (isset($_GET['OrderCreatedTime']) && (strtotime($_GET['OrderCreatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['OrderCreatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LastUpdatedTime = (isset($_GET['LastUpdatedTime']) && (strtotime($_GET['LastUpdatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['LastUpdatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LUT = explode('T',max(trim($LastUpdatedTime), trim($OrderCreatedTime)));
			$OCT = explode('T',trim($OrderCreatedTime));
			$this->pageSize = (isset($_GET['PageSize']) && (is_numeric($_GET['PageSize']))) ? (int) $_GET['PageSize'] : 100;  // Allow override of PageSize from URL
			$this->prodId = (isset($_GET['Id']) && (is_numeric($_GET['Id']))) ? (int) $_GET['Id'] : NULL;
			$this->lstUpdTm = implode(" ",$LUT);
			$this->ordCreatedTm = implode(" ",$OCT);
			$this->pageNo = (((isset($_GET['Page']) && (is_numeric($_GET['Page']))) ? (int) $_GET['Page'] : 0)) + 1;	// OS uses 0 for first page
			/*
			$this->xml .= '<LastUpdatedTimeP>' . $_GET['LastUpdatedTime'] . '</LastUpdatedTimeP>';
			$this->xml .= '<OrderCreatedTimeP>' . $_GET['OrderCreatedTime'] . '</OrderCreatedTimeP>';
			$this->xml .= '<lstUpdTime>' . strtotime($_GET['LastUpdatedTime']) . '</lstUpdTime>';
			$this->xml .= '<ordCreatedTime>' . strtotime($_GET['OrderCreatedTime']) . '</ordCreatedTime>';
			$this->xml .= '<LastUpdatedTime>' . $LastUpdatedTime . '</LastUpdatedTime>';
			$this->xml .= '<OrderCreatedTime>' . $OrderCreatedTime . '</OrderCreatedTime>';
			$this->xml .= '<lstUpdTm>' . $this->lstUpdTm . '</lstUpdTm>';
			$this->xml .= '<ordCreatedTm>' . $this->ordCreatedTm . '</ordCreatedTm>';
			$this->xml .= '<toBeUsed>' . max($LastUpdatedTime, $OrderCreatedTime) . '</toBeUsed>';
			*/
			//Check action
			$action = (isset($_GET['Action']) ? $_GET['Action'] : '');
			$requestType = $_SERVER['REQUEST_METHOD'];
			if ($requestType == "POST") {
				switch ($action) {
					case "Products":
						// Rename $action to UpdateProducts
						$action = "UpdateProducts";
						break;
					default:
						// We are not supporting push contacts and orders
						break;
				}
			}
			switch ($action) {
				case 'Contacts':
					$this->contactsAction();
					break;
				case 'Products':
					$this->productsAction();
					break;
				case 'ProductById':
					$this->productByIdAction();
					break;					
				case 'Orders':
					$this->ordersAction();
					break;
				case 'Invoices':
					$this->invoicesAction();
					break;						
				case 'Settings':
					$this->configurationsAction();
					break;
				case 'UpdateStock':
					$this->updateStockAction();
					break;
				case 'ShippingTracking':
					$this->shippingtrackingAction();
					break;
				case 'UpdateProducts':
					$this->updateProductsAction();
					break;
				default:
					$this->xml .= "<Error>Invalid Action</Error>";
			}
		} else {
			$this->xml .= "<Error>Invalid API Key</Error>";
		}

		$this->xml .= '</OneSaas>';

		/* To be uncommented in stable release
		// If DOM is installed, display the result as formatted XML
		if (class_exists('DOMDocument')) {
    		$dom = new DOMDocument();
			$dom->loadXML($this->xml);
			$dom->formatOutput = true;
			$this->xml = $dom->saveXML();
		}
		*/
		echo $this->xml;
	}

	/*** ProductById API Functions Start ***/
	public function productByIdAction(){
		$this->xml .= $this->getProductById($this->prodId);
	}
	
	private function getProductById($id) {
		$prod = $this->getProductModel()->load($id);
		if($prod['entity_id'] != NULL)
		{
			return $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
		}
		else
		{
			return '<Error>Wrong Parameter, Product with Id:'.$id.' does not exist!</Error>';
		}		
	}	
	/*** ProductById API Functions End ***/	

	/*** Product API Functions Start ***/

	public function productsAction(){

		if(!$this->getPageNoIsValid('Product')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getProducts();
		}
	}

	private function getProductModel(){
		return Mage::getModel('catalog/product');
	}

	private function getProductCollection($pageSize,$pageNo,$lstUpdTm){
		$prodModel = $this->getProductModel();
		$prod_data = $prodModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $prod_data;
	}

	private function getProducts() {
		$content = '';
					$prodCol = $this->getProductCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
					foreach($prodCol as $prod){
						$prod_status = $this->getProductModel()->load($prod['entity_id']);
						if($prod_status->getStatus() == 2 || (!$prod_status->getStatus()))
						{
							continue;
						}
						else 
						{
							$content .= $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
						}
		}	
		return $content;
	}

	private function getProductInfo($id,$LUD){
		$prod = $this->getProductModel()->load($id);
		$type = $prod->getTypeId();
		$actionKey = Mage::getSingleton('adminhtml/url')->getSecretKey("catalog_product", "edit");
		$url = Mage::getBaseUrl()."admin/catalog_product/edit/id/".$id."/key/".$actionKey;
		if($prod->getStatus() == 1)
			$prodStatus = "True";
		else
			$prodStatus = "False";
		if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($prod)->getManageStock() == 1)
			$isInventoried = "True";
		else
			$isInventoried = "False";
		$prod_info = array(
							'Code' => htmlspecialchars($prod->getId().'-sku'),
							'Name' => htmlspecialchars($prod->getName()),
							//'Model' => htmlspecialchars($prod->getModel()),
							'Description'=>htmlspecialchars(strip_tags($prod->getShortDescription())),
							'IsActive'=>$prodStatus,
							'Url'=>htmlspecialchars($url),
							'PublicUrl'=>htmlspecialchars($prod->getProductUrl()),
							'SalePrice'=>htmlspecialchars($prod->getPrice()),
							'SpecialPrice'=>htmlspecialchars($prod->getSpecialPrice()),
							'CostPrice'=>htmlspecialchars($prod->getCost()),
							'StockAtHand'=>htmlspecialchars(Mage::getModel('cataloginventory/stock_item')->loadByProduct($prod)->getQty()),
							'IsInventoried'=>$isInventoried,
							'Type'=>($type=='simple')?'Product':$type
						);
		//Get all product Attributes
		$attributes = array_keys($prod->getData());
		foreach($attributes as $attribute)
		{
			switch($attribute)
			{
				case 'barcode':	
					$custom_attribute = array('Barcode' => htmlspecialchars($prod->getData('barcode')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;				
				case 'manufacturer':
					$custom_attribute = array('PrimaryManufacturer' => htmlspecialchars($prod->getAttributeText('manufacturer')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;
				case 'supplier':
					$custom_attribute = array('PrimarySupplier' => htmlspecialchars($prod->getAttributeText('supplier')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;
				case 'weight':	
					$custom_attribute = array('Weight' => htmlspecialchars($prod->getData('weight')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;	
				case 'height':	
					$custom_attribute = array('Height' => htmlspecialchars($prod->getData('height')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;
				case 'length':	
					$custom_attribute = array('Length' => htmlspecialchars($prod->getData('length')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;
				case 'width':	
					$custom_attribute = array('Width' => htmlspecialchars($prod->getData('width')));
					$prod_info = array_merge($prod_info,$custom_attribute);	
					break;					
			}				
                        $supp = explode('/',$prod->getSku());
                        $custom_attribute = array('PrimarySupplier' => htmlspecialchars($supp[0]));
			$prod_info = array_merge($prod_info,$custom_attribute);	
		}	
		
		if($type == 'grouped' or $type == 'bundle') {
			$aProdsIdsArr = $prod->getTypeInstance()->getChildrenIds($id);
			$comboItems = array();
			foreach($aProdsIdsArr as $aProdsIds){
				foreach($aProdsIds as $aProdId){
					$aProd = $this->getProductModel()->load($aProdId);
					$comboItems[] = array('ProductId'=>$aProdId,'Quantity'=>Mage::getModel('cataloginventory/stock_item')->loadByProduct($aProd)->getQty());
				}
			}
			$comboItemsArr = array('ComboItems'=>$comboItems);
			$prod_info = array_merge($prod_info,$comboItemsArr);
		}
		if($type == 'simple') {
			$parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id);
			if(!empty($parentId)){	
				$product = Mage::getModel('catalog/product')->load($parentId[0]);
				$mastercode = array('MasterCode'=>htmlspecialchars($product->getSku()));	
				$prod_info = array_merge($prod_info,$mastercode);
				if($product->isConfigurable()) {
					$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$options = array(); 
					$attCodes = array();
						foreach ($productAttributeOptions as $productAttribute) {
								$attCodes[] = $productAttribute['attribute_code'];
							}
						foreach($attCodes as $attCode){
								$attribute = $prod->getResource()->getAttribute($attCode);
								$label = $attribute['frontend_label'];
								$value = $attribute ->getFrontend()->getValue($prod);
								$options[] = array('Name'=>htmlspecialchars($label),'Value'=>htmlspecialchars($value));
							}
					unset($attCodes);			
					$optionsArr = array('Options'=>$options);
					$prod_info = array_merge($prod_info,$optionsArr);
					}	
			}
		}     					
		return $this->getInfoXml('Product',$id,$LUD,$prod_info);
	}

	/*** Product API Functions End ***/

	/*** Customer API Functions Start ***/

	public function contactsAction(){
		if(!$this->getPageNoIsValid('Contact')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getCustomers();
		}
	}

	private function getCustomerModel(){
		return Mage::getModel('customer/customer');
	}

	private function getCustomerCollection($pageSize,$pageNo,$lstUpdTm){
		$custModel = $this->getCustomerModel();
		$cust_data = $custModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $cust_data;
	}

	private function getCustomers(){
		$content = '';
		$custCol = $this->getCustomerCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		foreach($custCol as $cust) {
			$content .= $this->getCustomerInfo($cust['entity_id'],$cust['updated_at']);
		}
		return $content;
	}

	private function getCustomerInfo($id,$LUD){
		$cust = $this->getCustomerModel()->load($id);
		$group = Mage::getModel('customer/group')->load($cust->getGroupId());
		$custBillingAdd = Mage::getModel('customer/address')->load($cust->getDefaultBilling());
		$custShippingAdd = Mage::getModel('customer/address')->load($cust->getDefaultShipping());
		$streetBill = $custBillingAdd->getStreet();
		$streetShip = $custShippingAdd->getStreet();
		// Please note this Url requires login
		$url = Mage::getBaseUrl()."admin/customer/edit/id/".$id;

		if(!$salutation = $cust->getPrefix()){$salutation = '';}
		if(!$taxvat = $cust->getTaxvat()){$taxvat = '';}
		$cust_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($cust->getFirstname()),
							'LastName' => htmlspecialchars($cust->getLastname()),
							//'Group' => $group->getCode(),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>htmlspecialchars($cust->getEmail()),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>array(
												'Address type="Billing"'=>array(
																'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																'City'=>htmlspecialchars($custBillingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																),
												'Address type="Shipping"'=>array(
																'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																'City'=>htmlspecialchars($custShippingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																)
											),
							'Url'=>htmlspecialchars($url)
						);
		return $this->getInfoXml('Contact',$id,$LUD,$cust_info);
	}

	/*** Customer API Functions End ***/

	/*** Invoice API Functions Start ***/

	public function invoicesAction(){
		if(!$this->getPageNoIsValid('Invoice')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getInvoices();
		}
	}
	
	private function getInvoiceModel(){
		return Mage::getModel('sales/order_invoice');
	}

	private function getInvoiceCollection($pageSize,$pageNo,$lstUpdTm){
		$invModel = $this->getInvoiceModel();
		$inv_data = $invModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $inv_data;
	}

	private function getInvoices(){
		$content = '';
		$invCol = $this->getInvoiceCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		
		foreach($invCol as $inv){
			$content .= $this->getInvoiceInfo($inv['entity_id'],$inv['order_id'], $inv['updated_at']);
		}
		return $content;
	}

	private function getInvoiceInfo($id, $order_id, $LUD){	
		$invoice = $this->getInvoiceModel()->load($id);
		$order = $this->getOrderModel()->load($order_id);
 		$items = $order->getAllVisibleItems();
 		$items_arr = array();
		foreach($items as $index => $item){
			$product_id = $item->getProductId();
			$order_item = Mage::getModel('catalog/product')->load($product_id);
			$type = $order_item->getTypeId();
			if($type == 'simple' or $type == 'virtual' or $type == 'downloadable')
 			{
				$product_id = $item->getProductId();
 				$product_code = $item->getSku();
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}				
			if($type == 'configurable')
 			{
 				$product_code = $item->getSku();
 				$product_id = Mage::getModel("catalog/product")->getIdBySku($product_code);
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}			
			if($type == 'bundle')
			{
				$sku_type = $order_item->getSkuType();
				$price_type = $order_item->getPriceType();				
				if ($sku_type == 0) // If SKU is dynamically generated
				{	
					$item_Id = $item->getItemId();				
					$colection = Mage::getResourceModel('sales/order_item_collection')->addFieldToFilter('order_id', array('eq' => $id));
					foreach ($colection as $index => $item)
					{	
						if ($item_Id == $item->getParentItemId())
							{
								$product_code = $item->getData('sku');
								$product_id = $item->getData('product_id');
								$parentProdId = $item->getData('product_id');
									if($price_type == 1) // If Bundle Price is set to Fixed
									{
										$bundleItem = $item->getProductOptions();
										if($bundleItem != NULL)
										{
											$parent_prod = Mage::getModel('sales/order_item')->load($item->getParentItemId());
											$tax_rate = $parent_prod->getTaxPercent();	
											$un_bundleItem = unserialize($bundleItem['bundle_selection_attributes']);
											$qty = $un_bundleItem['qty'];
											$unit_price_ex_tax = $un_bundleItem['price']/$qty;
											$unit_tax_amount = $unit_price_ex_tax/$tax_rate;
											$price = $unit_price_ex_tax + $unit_tax_amount;	
                                                                                        $p1 = Mage::getModel('catalog/product')->load($item->getProduct_id());
											$item_arr_inner = array(
															'ProductId'=>$product_id,
															'ProductCode'=>htmlspecialchars($product_code),
															'ProductName'=>htmlspecialchars($p1->getAttributeText('manufacturer')),
															'Quantity'=>htmlspecialchars($item->getQtyOrdered()),
															'Price'=>htmlspecialchars($price),
															'UnitPriceExTax'=>htmlspecialchars($unit_price_ex_tax));


											if ($parent_prod->getBaseDiscountAmount()>0) {$item_arr_inner['Discount'] = htmlspecialchars($parent_prod->getBaseDiscountAmount()/$parent_prod->getQtyOrdered());}
											if ($parent_prod->getBaseTaxAmount()>0) {$item_arr_inner['Taxes'] = array(
													'Tax'=>array(
													'TaxName'=>$this->getTaxNameForItem($order->getFullTaxInfo(), $parent_prod->getTaxPercent()),
													'TaxRate'=>htmlspecialchars((0.0 + $parent_prod->getTaxPercent())/100),
													'TaxAmount'=>htmlspecialchars(($unit_price_ex_tax/$tax_rate)*$item->getQtyOrdered())));}				
											$item_arr_inner['LineTotalIncTax']=($unit_price_ex_tax + $unit_price_ex_tax/$tax_rate)*$item->getQtyOrdered();
										}
									}
									else
									{
										$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);																					
									}
									$items_arr['Item_' . $index]=$item_arr_inner;
							}							
							else
							{	 
								continue;

							}
					}
				}
				if ($sku_type == 1 || $sku_type == NULL) // If SKU is not dynamically generated
				{
							$product_code = $item->getData('sku');
							$product_id = $item->getData('product_id');
							$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
							$items_arr['Item_' . $index]=$item_arr_inner;
				}		
			}
		}
		$actionKey = Mage::getSingleton('adminhtml/url')->getSecretKey("sales_order","view");
		$url = Mage::getBaseUrl()."admin/sales_order/view/order_id/".$id."/key/".$actionKey;
		$payments = $order->getAllPayments();
		$payments_array = array();
		foreach($payments as $key=>$a_payment) {
			try {
				$paymentMethodName = $a_payment->getMethodInstance()->getCode();
			} catch (Exception $e) {
				// Do nothing for now...
			}
			$payments_array['PaymentMethod'] = array(
				'MethodName' => htmlspecialchars($paymentMethodName),
				'Amount'=>'',//htmlspecialchars($a_payment->getBaseAmountPaid())
				);
		}

		$shippings_array = array();
		$carrier_code = '';
		$shipping_carrier = $order->getShippingCarrier();
		if (!is_null($shipping_carrier) && is_object($shipping_carrier)) {
			$carrier_code = $shipping_carrier->getCarrierCode();
		}
		//Mage::getModel('sales/order')->load($id)->getShippingDescription()
		
		$shippings_array = array(
			'ShippingMethod'=>htmlspecialchars($order->getShippingDescription()),
			//'ShippingMethod'=>htmlspecialchars($carrier_code),
			'Amount'=>htmlspecialchars($order->getBaseShippingAmount()+$order->getData('base_shipping_tax_amount')));
			if($order->getData('base_shipping_tax_amount') > 0)
			{
				$tax_info = $order->getFullTaxinfo();		
				$shippings_array['Taxes']= array(
					'Tax'=>array(
					'TaxName'=>htmlspecialchars($tax_info[0]['id']),
					'TaxRate'=>htmlspecialchars($tax_info[0]['percent']/100),
					'TaxAmount'=>htmlspecialchars($order->getData('base_shipping_tax_amount'))));				
			}

		$credits = $order->getCreditmemosCollection();
		$credits_array = array();

		foreach($credits as $key => $a_credit) {
			$comments_array = array();
			foreach ( $a_credit->getCommentsCollection() as $j => $a_comment) {
				$comments_array['Comment_' . $j . ' LastUpdated="' . htmlspecialchars($a_comment->getCreatedAt()) . '"'] = htmlspecialchars($a_comment->getComment());
			}
			$credits_array['Credit LastUpdated="' . htmlspecialchars($a_credit->getUpdatedAt()) . '"'] = array('Date'=>htmlspecialchars($a_credit->getCreatedAt()), 'Amount'=>htmlspecialchars($a_credit->getBaseGrandTotal()), 'Comments'=> $comments_array);
		}
		
		// Contact Info - whether it is a registered user or a guest
		$custBillingAdd = $order->getBillingAddress();
		$custShippingAdd = $order->getShippingAddress();
		if (!$custShippingAdd && $custBillingAdd) {
			$custShippingAdd = $custBillingAdd;
		}
		if ($custShippingAdd && !$custBillingAdd) {
			$custBillingAdd = $custShippingAdd;
		}
		$contact_info = '';
		
		if ($custShippingAdd && $custBillingAdd) {
			$streetBill = $custBillingAdd->getStreet();
			$streetShip = $custShippingAdd->getStreet();
			if(!$salutation = $order->getData('customer_prefix')){$salutation = '';}
			if(!$taxvat = $order->getData('customer_taxvat')){$taxvat = '';}
			$customerFirstname = ($order->getCustomerFirstname() == '')?(($custBillingAdd->getFirstname()=='')?$custShippingAdd->getFirstname():$custBillingAdd->getFirstname()):$order->getCustomerFirstname();
			$customerLastname = ($order->getCustomerLastname() == '')?(($custBillingAdd->getLastname()=='')?$custShippingAdd->getLastname():$custBillingAdd->getLastname()):$order->getCustomerLastname();

			// Billing & Shipping Optional Addresses
			$addresses = array(
							'Address type="Billing"'=>array(
											'Salutation'=>'',//htmlspecialchars(($salutation = $custBillingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>'',//htmlspecialchars($custBillingAdd->getFirstname()),
											'LastName'=>'',//htmlspecialchars($custBillingAdd->getLastname()),
											'OrganizationName'=>'',//htmlspecialchars(($company = $custBillingAdd->getData('company'))?$company:''),
											'Line1'=>'',//htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
											'Line2'=>'',//htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
											'City'=>'',//htmlspecialchars($custBillingAdd->getCity()),
											'PostCode'=>'',//htmlspecialchars($custBillingAdd->getPostcode()),
											'State'=>'',//htmlspecialchars($custBillingAdd->getRegion()),
											'CountryCode'=>'',//htmlspecialchars($custBillingAdd->getCountryId())
											),
							'Address type="Shipping"'=>array(
											'Salutation'=>'',//htmlspecialchars(($salutation = $custShippingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>'',//htmlspecialchars($custShippingAdd->getFirstname()),
											'LastName'=>'',//htmlspecialchars($custShippingAdd->getLastname()),
											'OrganizationName'=>'',//htmlspecialchars(($company = $custShippingAdd->getData('company'))?$company:''),
											'Line1'=>'',//htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
											'Line2'=>'',//htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
											'City'=>'',//htmlspecialchars($custShippingAdd->getCity()),
											'PostCode'=>'',//htmlspecialchars($custShippingAdd->getPostcode()),
											'State'=>'',//htmlspecialchars($custShippingAdd->getRegion()),
											'CountryCode'=>'',//htmlspecialchars($custShippingAdd->getCountryId())
											)
						);
					$fl = $order->getIscimorder() ? 'Cim': 'Cash';	
					$cl = 'Customers';	
			$contact_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($fl),
							'LastName' => htmlspecialchars($cl),
							'BillingFirstName' => htmlspecialchars($fl),
							'BillingLastName' => htmlspecialchars($cl),
							'ShippingFirstName' => htmlspecialchars($fl),
							'ShippingLastName' => htmlspecialchars($cl),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>htmlspecialchars($order->getData('customer_email')),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>$addresses
							/*
							'Addresses'=>array(
												'Address type="Billing"'=>array(
																'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																'City'=>htmlspecialchars($custBillingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																),
												'Address type="Shipping"'=>array(
																'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																'City'=>htmlspecialchars($custShippingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																)
											)
											*/
						);
		} else {
			// If no contact info, then exclude this order and return
			return "";
		}

		// Contact tag
		if ($order->getIscimorder()) {
			$contact_tag = 'Contact id="2"';
		} else {
			$contact_tag = 'Contact id="5776"';
		}

		$order_number = $order->getIncrementId();
		
		$currencyCode   = '';
		$currency       = $order->getBaseCurrency();
		if (is_object($currency)) {
			$currencyCode = $currency->getCurrencyCode();
		}
		
		// OS-3835 & OS-3558
		$customFields_array = array(
			'CustomField Name="CreditPoints"'=>$order->getData('creditpoint_amount'),
			'CustomField Name="OrderProcessingFeeExTax"'=>$order->getData('base_et_payment_extra_charge_excluding_tax'),
			'CustomField Name="OrderProcessingFee"'=>$order->getData('base_et_payment_extra_charge')
		);
		
		$ord_info = array(
							'InvoiceNumber' => htmlspecialchars($invoice->getIncrementId()),
							'OrderNumber' => htmlspecialchars($order_number),
							'Date' => htmlspecialchars($invoice->getCreatedAt()),
							'Type'=>"Invoice",
							'CurrencyCode'=>htmlspecialchars($currencyCode),
							'Notes'=>htmlspecialchars($order->getCustomerNote()),
							'Tags'=>'StoreName:' . htmlspecialchars($order->getStoreName()) .',',
							'Discounts'=>htmlspecialchars(0.0 + abs($order->getBaseDiscountAmount()) + abs($order->getBaseCreditDiscountAmount()) + abs($order->getData('rewardpoints_base_discount')) + abs($order->getData('gift_voucher_discount'))),
							'Total'=>htmlspecialchars($order->getBaseGrandTotal()),
							$contact_tag => $contact_info,
							'Addresses' => $addresses,
							'Items'=>$items_arr,
							'Shipping'=>$shippings_array,
							'Payments'=>$payments_array,
							'Credits'=>$credits_array,
							'CustomFields'=>$customFields_array,
							'Url'=>htmlspecialchars($url)
						);
						
		return $this->getXml('Invoice',array('id'=>$id,'LastUpdated'=>$LUD),$ord_info);
	}
	
	/*** Invoice API Functions End ***/	
	
	/*** Order API Functions Start ***/

	public function ordersAction(){
		if(!$this->getPageNoIsValid('Order')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getOrders();
		}
	}

	private function getOrderModel(){
		return Mage::getModel('sales/order');
	}

	private function getOrderCollection($pageSize,$pageNo,$lstUpdTm){
		$ordModel = $this->getOrderModel();
		$ord_data = $ordModel->getCollection()
                                ->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
                                ->addFieldToFilter('status', 'complete')
                                ->setPageSize($pageSize)
                                ->setCurPage($pageNo)
                                ->getData();
		return $ord_data;
	}

	private function getOrders(){
		$content = '';
		$ordCol = $this->getOrderCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		
		foreach($ordCol as $ord){
			
			// If it is an order that has been updated after submission, the order is cancelled and a new one is created

			$original_id = $ord['entity_id'];
			$content .= $this->getOrderInfo($ord['entity_id'],$original_id, $ord['updated_at']);
		}
		return $content;
	}
	
	// Try to match the tax rate ($rate) with the taxes applied to the order ($fullTaxInfo) to retrieve the name of the tax
	private function getTaxNameForItem($fullTaxInfo, $rate) {
		if (!isset($fullTaxInfo) || !is_array($fullTaxInfo) || count($fullTaxInfo)==0 || !is_numeric($rate)) {
			return "";
		}
		foreach($fullTaxInfo as $taxInfo) {
			if (is_array($taxInfo) && isset($taxInfo['rates']) && is_array($taxInfo['rates']) && count($taxInfo['rates']>0)) {
				foreach($taxInfo['rates'] as $taxRate) {
					if (is_array($taxRate) && isset($taxRate['code']) && isset($taxRate['percent']) && is_numeric($taxRate['percent']) && (abs((0.0 + $taxRate['percent']) - (0.0 + $rate)) < 0.01)) {
						return $taxRate['code'];
					}
				}
			}
		}
		return "";
	}
	
	public function getOrderItemInfo($product_id,$product_code,$item,$order){
            $p1 = Mage::getModel('catalog/product')->load($item->getProduct_id());
		$item_arr_inner = array('ProductId'=>$product_id,
							'ProductCode'=>htmlspecialchars($product_id.'-sku'),
							'ProductName'=>htmlspecialchars($p1->getAttributeText('manufacturer')),
							'Quantity'=>htmlspecialchars($item->getQtyOrdered()),
							'Price'=>htmlspecialchars($item->getBasePriceInclTax()),
							'UnitPriceExTax'=>htmlspecialchars($item->getBasePrice()));
		if ($item->getBaseDiscountAmount()>0) {$item_arr_inner['Discount'] = htmlspecialchars($item->getBaseDiscountAmount()/$item->getQtyOrdered());}
		if ($item->getBaseTaxAmount()>0) {$item_arr_inner['Taxes'] = array(
									'Tax'=>array(
									'TaxName'=>$this->getTaxNameForItem($order->getFullTaxInfo(), $item->getTaxPercent()),
									'TaxRate'=>htmlspecialchars((0.0 + $item->getTaxPercent())/100),
									'TaxAmount'=>htmlspecialchars($item->getBaseTaxAmount())));}

		$item_arr_inner['LineTotalIncTax']=$item->getBaseRowTotalInclTax();
		return $item_arr_inner;			
	}	
	
	private function getOrderInfo($id,$original_id, $LUD){
 		$order = $this->getOrderModel()->load($id);
 		$items = $order->getAllVisibleItems();
 		$items_arr = array();
		foreach($items as $index => $item){
			$product_id = $item->getProductId();
			$order_item = Mage::getModel('catalog/product')->load($product_id);
			$type = $order_item->getTypeId();			
			if($type == 'configurable')
 			{
 				$product_code = $item->getSku();
 				$product_id = Mage::getModel("catalog/product")->getIdBySku($product_code);
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$tax_rate = $item->getTaxPercent()/100;
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}			
			if($type == 'bundle')
			{
				$price_type = $order_item->getPriceType();				
				$item_Id = $item->getItemId();				
				$colection = Mage::getResourceModel('sales/order_item_collection')->addFieldToFilter('order_id', array('eq' => $id));
					foreach ($colection as $index => $item)
					{	
						if ($item_Id == $item->getParentItemId())
							{
								$product_code = $item->getData('sku');
								$product_id = $item->getData('product_id');
								$parentProdId = $item->getData('parent_item_id');
									if($price_type == 1) // If Bundle Price is set to Fixed
									{
										$bundleItem = $item->getProductOptions();
										//var_dump($bundleItem);
										if($bundleItem != NULL)
										{
											$parent_prod = Mage::getModel('sales/order_item')->load($parentProdId);
											$tax_rate = $parent_prod->getTaxPercent();	
											$un_bundleItem = unserialize($bundleItem['bundle_selection_attributes']);			 
											$qty = $un_bundleItem['qty'];
											$unit_price_ex_tax = $un_bundleItem['price']/$qty;
											$unit_tax_amount = $unit_price_ex_tax/$tax_rate;
											$price = $unit_price_ex_tax + $unit_tax_amount;
                                                                                        $p1 = Mage::getModel('catalog/product')->load($item->getProduct_id());
											$item_arr_inner = array(
															'ProductId'=>$product_id,
															'ProductCode'=>htmlspecialchars($product_code),
															'ProductName'=>htmlspecialchars($p1->getAttributeText('manufacturer')),
															'Quantity'=>htmlspecialchars($item->getQtyOrdered()),
															'Price'=>htmlspecialchars($price),
															'UnitPriceExTax'=>htmlspecialchars($unit_price_ex_tax));
											if ($parent_prod->getBaseDiscountAmount()>0) {$item_arr_inner['Discount'] = htmlspecialchars($parent_prod->getBaseDiscountAmount()/$parent_prod->getQtyOrdered());}
											if ($parent_prod->getBaseTaxAmount()>0) {$item_arr_inner['Taxes'] = array(
													'Tax'=>array(
													'TaxName'=>$this->getTaxNameForItem($order->getFullTaxInfo(), $parent_prod->getTaxPercent()),
													'TaxRate'=>htmlspecialchars((0.0 + $parent_prod->getTaxPercent())/100),
													'TaxAmount'=>htmlspecialchars(($unit_price_ex_tax/$tax_rate)*$item->getQtyOrdered())));}				
											$item_arr_inner['LineTotalIncTax']=($unit_price_ex_tax + $unit_price_ex_tax/$tax_rate)*$item->getQtyOrdered();
										}
									}
									else
									{
										$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);																					
									}
									$items_arr['Item_' . $index]=$item_arr_inner;
							}							
							else
							{	 
								continue;
							}
					}	
			}
			if($type == 'simple' || $type == 'downloadable' || $type == 'virtual')
 			{
				$product_id = $item->getProductId();
 				$product_code = $item->getSku();
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$tax_rate = $item->getTaxPercent()/100;
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}
			/*
				if ($sku_type == 1 || $sku_type == NULL) // If SKU is not dynamically generated
				{
							$product_code = $item->getData('sku');
							$product_id = $item->getData('product_id');
							$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
							$items_arr['Item_' . $index]=$item_arr_inner;
				}		
			}*/
		}
		$actionKey = Mage::getSingleton('adminhtml/url')->getSecretKey("sales_order","view");
		$url = Mage::getBaseUrl()."admin/sales_order/view/order_id/".$id."/key/".$actionKey;
		$payments = $order->getAllPayments();
		$payments_array = array();
		foreach($payments as $key=>$a_payment) {
			try {
				$paymentMethodName = $a_payment->getMethodInstance()->getCode();
			} catch (Exception $e) {
				// Do nothing for now...
			}
			$payments_array['PaymentMethod'] = array(
				'MethodName' => htmlspecialchars($paymentMethodName),
				'Amount'=>'',//htmlspecialchars($a_payment->getBaseAmountPaid()),
				'ReferenceNumber'=>htmlspecialchars($a_payment->getPoNumber())
				);
		}

		$shippings_array = array();
		$carrier_code = '';
		$shipping_carrier = $order->getShippingCarrier();
		if (!is_null($shipping_carrier) && is_object($shipping_carrier)) {
			$carrier_code = $shipping_carrier->getCarrierCode();
		}
		//Mage::getModel('sales/order')->load($id)->getShippingDescription()
		
		$shippings_array = array(
			'ShippingMethod'=>htmlspecialchars($order->getShippingDescription()),
			//'ShippingMethod'=>htmlspecialchars($carrier_code),
			'Amount'=>htmlspecialchars($order->getBaseShippingAmount()+$order->getData('base_shipping_tax_amount')));
			if($order->getData('base_shipping_tax_amount') > 0)
			{
			$shipping_tax_rate = $order->getData('base_shipping_tax_amount')/$order->getBaseShippingAmount();
			$epsilon = 0.0005;			
				if(abs($shipping_tax_rate - $tax_rate) < $epsilon)
				{
					$shippings_array['Taxes']= array(
					'Tax'=>array(
					'TaxName'=>"",
					'TaxRate'=>htmlspecialchars($tax_rate),
					'TaxAmount'=>htmlspecialchars($order->getData('base_shipping_tax_amount'))));				
				}
				else
				{
					//Use the Shipping Country Tax. This when items are WO tax, we don't have a tax rate to compare. 	
				}
			}

		$credits = $order->getCreditmemosCollection();
		$credits_array = array();

		foreach($credits as $key => $a_credit) {
			$comments_array = array();
			foreach ( $a_credit->getCommentsCollection() as $j => $a_comment) {
				$comments_array['Comment_' . $j . ' LastUpdated="' . htmlspecialchars($a_comment->getCreatedAt()) . '"'] = htmlspecialchars($a_comment->getComment());
			}
			$credits_array['Credit LastUpdated="' . htmlspecialchars($a_credit->getUpdatedAt()) . '"'] = array('Date'=>htmlspecialchars($a_credit->getCreatedAt()), 'Amount'=>htmlspecialchars($a_credit->getBaseGrandTotal()), 'Comments'=> $comments_array);
		}
		
		// Contact Info - whether it is a registered user or a guest
		$custBillingAdd = $order->getBillingAddress();
		$custShippingAdd = $order->getShippingAddress();
		if (!$custShippingAdd && $custBillingAdd) {
			$custShippingAdd = $custBillingAdd;
		}
		if ($custShippingAdd && !$custBillingAdd) {
			$custBillingAdd = $custShippingAdd;
		}
		$contact_info = '';
		
		if ($custShippingAdd && $custBillingAdd) {
			$streetBill = $custBillingAdd->getStreet();
			$streetShip = $custShippingAdd->getStreet();
			if(!$salutation = $order->getData('customer_prefix')){$salutation = '';}
			if(!$taxvat = $order->getData('customer_taxvat')){$taxvat = '';}
			$customerFirstname = ($order->getCustomerFirstname() == '')?(($custBillingAdd->getFirstname()=='')?$custShippingAdd->getFirstname():$custBillingAdd->getFirstname()):$order->getCustomerFirstname();
			$customerLastname = ($order->getCustomerLastname() == '')?(($custBillingAdd->getLastname()=='')?$custShippingAdd->getLastname():$custBillingAdd->getLastname()):$order->getCustomerLastname();

			// Billing & Shipping Optional Addresses
			$addresses = array(
							'Address type="Billing"'=>array(
											'Salutation'=>'',//htmlspecialchars(($salutation = $custBillingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>'',//htmlspecialchars($custBillingAdd->getFirstname()),
											'LastName'=>'',//htmlspecialchars($custBillingAdd->getLastname()),
											'OrganizationName'=>'',//htmlspecialchars(($company = $custBillingAdd->getData('company'))?$company:''),
											'Line1'=>'',//htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
											'Line2'=>'',//htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
											'City'=>'',//htmlspecialchars($custBillingAdd->getCity()),
											'PostCode'=>'',//htmlspecialchars($custBillingAdd->getPostcode()),
											'State'=>'',//htmlspecialchars($custBillingAdd->getRegion()),
											'CountryCode'=>'',//htmlspecialchars($custBillingAdd->getCountryId())
											),
							'Address type="Shipping"'=>array(
											'Salutation'=>'',//htmlspecialchars(($salutation = $custShippingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>'',//htmlspecialchars($custShippingAdd->getFirstname()),
											'LastName'=>'',//htmlspecialchars($custShippingAdd->getLastname()),
											'OrganizationName'=>'',//htmlspecialchars(($company = $custShippingAdd->getData('company'))?$company:''),
											'Line1'=>'',//htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
											'Line2'=>'',//htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
											'City'=>'',//htmlspecialchars($custShippingAdd->getCity()),
											'PostCode'=>'',//htmlspecialchars($custShippingAdd->getPostcode()),
											'State'=>'',//htmlspecialchars($custShippingAdd->getRegion()),
											'CountryCode'=>'',//htmlspecialchars($custShippingAdd->getCountryId())
											)
						);
					$fl = $order->getIscimorder() ? 'Cim': 'Cash';	
					$cl = 'Customers';
			$contact_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($fl),
							'LastName' => htmlspecialchars($cl),
							'BillingFirstName' => htmlspecialchars($fl),
							'BillingLastName' => htmlspecialchars($cl),
							'ShippingFirstName' => htmlspecialchars($fl),
							'ShippingLastName' => htmlspecialchars($cl),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>'',//htmlspecialchars($order->getData('customer_email')),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>$addresses
							/*
							'Addresses'=>array(
												'Address type="Billing"'=>array(
																'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																'City'=>htmlspecialchars($custBillingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																),
												'Address type="Shipping"'=>array(
																'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																'City'=>htmlspecialchars($custShippingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																)
											)
											*/
						);
		} else {
			// If no contact info, then exclude this order and return
			return "";
		}

		// Contact tag
		if ($order->getIscimorder()) {
			$contact_tag = 'Contact id="2"';
		} else {
			$contact_tag = 'Contact id="5776"';
		}

		$order_number = $order->getIncrementId();
		
		$currencyCode   = '';
		$currency       = $order->getBaseCurrency(); //$order object
		if (is_object($currency)) {
			$currencyCode = $currency->getCurrencyCode();
		}
		
		//OS-6886
		$orderAttributes = Mage::getModel('amorderattr/attribute');
		if ( is_object($orderAttributes))
		{
			$orderAttribute = $orderAttributes->load($order->getId(), 'order_id');
			$SalesRep = $orderAttributes->getData('sales_rep');
			$NetTermsId = $orderAttributes->getData('net_terms_po');
			$attr = Mage::getModel('catalog/resource_eav_attribute')->load(150);
			$NetTerms = $attr->getSource()->getOptionText($NetTermsId);
		}
		else
		{
			$SalesRep = '';
			$NetTerms = '';
		}		
		// OS-3835 & OS-3558 & OS-5925 & OS-6938 & OS-7431 & OS-7607
		$customFields_array = array(
			'CustomField Name="CreditPoints"'=>$order->getData('creditpoint_amount'),
			'CustomField Name="OrderProcessingFeeExTax"'=>$order->getData('base_et_payment_extra_charge_excluding_tax'),
			'CustomField Name="OrderProcessingFee"'=>$order->getData('base_et_payment_extra_charge'),
			'CustomField Name="LastTransactionId"'=>$order->getPayment()->getLastTransId(),
			'CustomField Name="SalesRep"'=>$SalesRep,
			'CustomField Name="NetTerms"'=>$NetTerms,
			'CustomField Name="PointDiscount"'=>$order->getData('rewardpoints_base_discount'),
			'CustomField Name="RewardDiscount"'=>$order->getData('mw_rewardpoint_discount'),
			'CustomField Name="Surcharge"'=>$order->getData('base_fee_amount')
		);
		
		
		if($order->hasInvoices()){
			foreach($order->getInvoiceCollection() as $inv){
				$customFields_array['CustomField Name="Inv-' . $inv->getIncrementId() . '"'] = $inv->getUpdatedAt();
			}
		}
		
		$replaces_order_number_key = 'ReplaceOrderNumber';
		$replaces_order_number = '';
		if (strpos($order_number,'-')) {
		 	$replaces_order_number = $order->getData('relation_parent_real_id');
			$replaced_order_id = $order->getData('relation_parent_id');
			$replaces_order_number_key = 'ReplaceOrderNumber Id="' . $replaced_order_id . '"';
		}

		$ord_info = array(
							'OrderNumber' => htmlspecialchars($order_number),
							$replaces_order_number_key => htmlspecialchars($replaces_order_number),
							'Date' => htmlspecialchars($order->getUpdatedAt()),
							'Type'=>"Order",
							'Status'=>htmlspecialchars($order->getStatus()),
							'CurrencyCode'=>htmlspecialchars($currencyCode),
							'Notes'=>htmlspecialchars($order->getCustomerNote()),
							'Tags'=>'StoreName:' . htmlspecialchars($order->getStoreName()) .',',
							'Discounts'=>htmlspecialchars(0.0 + abs($order->getBaseDiscountAmount()) + abs($order->getBaseCreditDiscountAmount())+ abs($order->getData('rewardpoints_base_discount')) + abs($order->getData('gift_voucher_discount'))),
							'Total'=>htmlspecialchars($order->getBaseGrandTotal()),
							$contact_tag => $contact_info,
							'Addresses' => $addresses,
							'Items'=>$items_arr,
							'Shipping'=>$shippings_array,
							'Payments'=>$payments_array,
							'Credits'=>$credits_array,
							'CustomFields'=>$customFields_array,
							'Url'=>htmlspecialchars($url)
						);

						
		
		return $this->getXml('Order',array('id'=>$original_id,'LastUpdated'=>$LUD),$ord_info);
	}

	/*** Order API Functions End ***/

	/*** Configuration API Functions Start ***/

	public function configurationsAction(){
		$this->xml .= $this->getConfiguration();
	}

	private function getPaymentConfigModel(){
		return Mage::getSingleton('payment/config');
	}

	private function getTaxRateModel(){
		return Mage::getModel('tax/calculation_rate');
	}

	private function getShippingModel(){
		return Mage::getModel('shipping/config');
	}

	private function getConfigCollection($pageSize,$pageNo,$lstUpdTm){
		$paymentModel = $this->getPaymentConfigModel();
		$activeMethods = $paymentModel->getActiveMethods();
		$paymentMethods = array();
		foreach ($activeMethods as $paymentCode=>$paymentModel){
			$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			$paymentMethods[]=array('Name'=>$paymentCode, 'Description'=>htmlspecialchars(strip_tags($paymentTitle)));
		}

		$trModel = $this->getTaxRateModel();
		$tr_data = $trModel->getCollection()->getData();
		$trs = array();
		foreach($tr_data as $tr){
			$stateCode = Mage::getModel('directory/region')->load($tr['tax_region_id'])->getCode();
			$trs[]=array('Name'=>htmlspecialchars($tr['code']),'Description'=>'', 'Rate'=>(0.0 + $tr['rate'])/100, 'CountryCode'=>htmlspecialchars($tr['tax_country_id']), 'StateCode'=>htmlspecialchars($stateCode), 'Zip'=>htmlspecialchars($tr['tax_postcode']),'City'=>'','IsCompound'=>'');
		}

		$shipModel = $this->getShippingModel();
		$aCarriers = $shipModel->getActiveCarriers();
		$ShippingMethods=array();
		foreach ($aCarriers as $carrierCode=>$carrierModel){
			$carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
			$ShippingMethods[]=array('Name'=>htmlspecialchars($carrierCode), 'Description'=>htmlspecialchars(strip_tags($carrierTitle)));
		}
		// Categories
		$mageFilename = 'app/Mage.php';
		require_once $mageFilename;
		Mage::app();

// 		$category = Mage::getModel('catalog/category'); 
// 		$tree = $category->getTreeModel(); 
// 		$tree->load();
// 
// 		$ids = $tree->getCollection()->getAllIds(); 
// 		$Categories = array();
// 
// 		if ($ids){ 
// 			foreach ($ids as $id){ 
// 				$cat = Mage::getModel('catalog/category'); 
// 				$cat->load($id);
// 				if ($cat->getParentId()=='0') {
// 					$Categories[]=array('Id'=> $cat->getId(), 'Name'=>htmlspecialchars($cat->getName()));
// 				} else {
// 					$Categories[]=array('Id'=> $cat->getId(), 'ParentId'=>$cat->getParentId(), 'Name'=>htmlspecialchars($cat->getName()));
// 				}
// 			} 
// 		}
		
		//Order Statuses
		$orderstatuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		foreach ($orderstatuses as $orderstatus){	
			$OrderStatuses[]=array('Name'=>$orderstatus['status'], 'Description'=>htmlspecialchars($orderstatus['label']));
		}
		
		// TimeZones
		$times = '';
		$datetime = Zend_Date::now();
		$datetime->setLocale(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));
		$localUTCtime = $datetime->get(Zend_Date::ISO_8601);
		$datetime->setLocale(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))
		         ->setTimezone(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));		 
		$localtime = $datetime->get(Zend_Date::ISO_8601);
		$times = array('LocalTime'=>$localtime,'LocalUTCTime'=>$localUTCtime);
		
		//Plugin Features
		$features_array = array('BatchStockUpdates'=>'true');
		$price_setting = Mage::getStoreConfig('tax/calculation/price_includes_tax');
		if($price_setting === '0')
			{
				$features['IsSellingPriceIncludingTax'] = 'false';
			}
		else
			{
				$features['IsSellingPriceIncludingTax'] = 'true';
			}		
		foreach($features_array as $feature => $status) {
			$features[] = array('Name'=>$feature,'Value'=>$status);
		}		
		
		//$config=array('Times'=>$times, 'PaymentGateways'=>$paymentMethods,'TaxCodes'=>$trs,'ShippingMethods'=>$ShippingMethods, 'OrderStatuses'=>$OrderStatuses, 'Categories'=>$Categories, 'PluginFeatures'=>$features);
		$config=array('Times'=>$times, 'PaymentGateways'=>$paymentMethods,'TaxCodes'=>$trs,'ShippingMethods'=>$ShippingMethods, 'OrderStatuses'=>$OrderStatuses, 'PluginFeatures'=>$features);
		return $config;
	}

	private function getConfiguration(){
		$content = '';
		$confCol = $this->getConfigCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		$count = 0;
		$found = 0;
		$pageNo = (($this->pageNo-1)*$this->pageSize)+1;
		$endpageNo = $this->pageSize*$this->pageNo;
		foreach($confCol as $key=>$conf){
			$count++;

			if(($pageNo<=$count) AND ($count<=$endpageNo)) {
				$content .= $this->getConfigInfo($key,$conf);
				$found = 1;
			}
		}
		if($found == 0) {
			$content .= '';
		}
		return $content;
	}

	private function getConfigInfo($key,$conf){

		return $this->getInfoXml($key,false,null,$conf);

	}

	/*** Configuration API Functions End ***/

	/*** Order Shipping Tracking API Functions Start ***/

	public function shippingtrackingAction(){
		// Parse posted parameters ShippingTrackingId, OrderNumber, Date, TrackingCode, CarrierCode, CarrierName, Notes
		$xmlRequest = new SimpleXmlElement(file_get_contents("php://input"));
		if ($xmlRequest->getName()==='OrderShippingTracking') {
			foreach ($xmlRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$OrderNumber = "" . $attr;
				}
			}
			foreach ($xmlRequest->children() as $child) {
				switch ($child->getName()) {
					case 'OrderNumber':
						$OrderIncrementId = $child;
						break;
					case 'Date':
						$Date = $child;
						break;
					case 'TrackingCode':
						$TrackingCode = $child;
						break;
					case 'CarrierCode':
						$CarrierCode = $child;
						break;
					case 'CarrierName':
						$CarrierName = $child;
						break;
					case 'Notes':
						$Notes = $child;
						break;
					default:
						// Not interested
						break;
				}
			}
			if ( ($OrderNumber != '') ) {
				try {
					$order = Mage::getModel('sales/order')->load($OrderNumber);
					if($order->canShip()) {
						$itemQty =  $order->getItemsCollection()->count();
						$ship = new Mage_Sales_Model_Order_Shipment_Api();
						$shipmentId = $ship->create($order->getIncrementId());
					}

					$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
					$shipment_collection->addAttributeToFilter('order_id', $order->getId());

					foreach($shipment_collection as $sc) {
						$shipment = Mage::getModel('sales/order_shipment');
						$shipment->load($sc->getId());
						if($shipment->getId() != '') {
							if($shipment->getAllTracks() == NULL)
								{
									$track = Mage::getModel('sales/order_shipment_track')
										 ->setShipment($shipment)
										 ->setData('title', $CarrierName)
										 ->setData('number', $TrackingCode)
										 ->setData('carrier_code', $CarrierCode)
										 ->setData('description', $Notes)
										 ->setData('order_id', $shipment->getData('order_id'))
										 ->save();
								}
								else
								{
									// Warning - Order already shipped.
								}
							}
						if($shipment){
							if(!$shipment->getEmailSent()){
								$shipment->sendEmail(true);
								$shipment->setEmailSent(true);
								$shipment->save();
							}
						}
					}
					$this->xml .= '<Success>Operation Succeeded</Success>';
				} catch (Exception $ex) {
					$this->xml .= '<Error>Shipping tracking update failed: ' . $ex . '. OrderId=' . $OrderNumber . ' OrderIncrementalId=' . $OrderIncrementId . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Order Number = ' . $OrderNumber . '</Error>';
			}
		} else {
			$this->xml .= '<Error>Wrong xml format</Error>';
		}
	}

	/*** Order Shipping Tracking API Functions End ***/

	/*** Update Stock API Functions Start ***/

	public function updateStockAction() {
		// Parse posted parameters StockUpdateId, ProductCode, StockAtHand, StockAllocated, StockAvailable
		$xmlRequest = new SimpleXmlElement(file_get_contents("php://input"));
		$stockUpdateRequests = array();
		$batchMode='false';
		if ($xmlRequest->getName()==='ProductStockUpdate') {
			// Single product stock update
			$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($xmlRequest);
		} elseif ($xmlRequest->getName()==='ProductStockUpdates') {
			// Multiple product stock update
			$batchMode='true';
			foreach ($xmlRequest->children() as $aXmlRequest) {
				$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($aXmlRequest);
			}
		} else {
			// Wrong format
		}

		// Xml Response depends on batchMode
		//$this->xml .= ($batchMode=='true')?'<ProductStockUpdates>':'';
		foreach ($stockUpdateRequests as $stockUpdateRequest) {
			$this->xml .= ($batchMode=='true')?'<Response Id="' . $stockUpdateRequest['ProductCode'] .'">':'';
			if ($stockUpdateRequest['ProductCode'] != '') {
				$product = Mage::getModel('catalog/product')->load($stockUpdateRequest['ProductCode']);
				if (is_object($product)) {
					$stock_obj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
					$stockData = $stock_obj->getData();
					$stockData['qty'] = $stockUpdateRequest['StockAvailable'];
					$stockData['is_in_stock'] = 1;
					$stock_obj->setData($stockData);
					try {
						$stock_obj->save();
						$product->save();
						$this->xml .= '<Success>Operation Succeeded. Batch mode=' . $batchMode . '</Success>';
					} catch (Exception $ex) {
						if(strpos($ex,'constraint') !== false)
							{	
								//OS-4350 Load the correct Product Id.
								$prod_id = Mage::getModel("catalog/product")->getIdBySku($stockUpdateRequest['Code']);
								$product = Mage::getModel('catalog/product')->load($prod_id);
								if (is_object($product)) {								
								$stock_obj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
								$stockData = $stock_obj->getData();
								$stockData['qty'] = $stockUpdateRequest['StockAvailable'];
								$stockData['is_in_stock'] = 1;
								$stock_obj->setData($stockData);
								try {
									$stock_obj->save();
									$product->save();
									$this->xml .= '<Success>Operation Succeeded. Batch mode=' . $batchMode . '</Success>';
								} catch (Exception $ex)	{
									if(strpos($ex,'constraint') !== false)
									{
										$this->xml .= '<Error>Stock update failed: Product code '. $stockUpdateRequest['Code'] . ' does not exist</Error>';
									}
									else
									{
										$this->xml .= '<Error>Stock update failed: ' . htmlspecialchars($ex) . '</Error>';									
									}
								}	
								}
								else {
									$this->xml .= '<Error>Wrong parameters: Product is not object. Id="' . $prod_id . '" StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
								}								
							}
						else
							{						
								$this->xml .= '<Error>Stock update failed: ' . htmlspecialchars($ex) . '</Error>';
							}
						}
				} else {
					$this->xml .= '<Error>Wrong parameters: Product is not object. Id="' . $stockUpdateRequest['ProductCode'] . '" StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Id="' . $stockUpdateRequest['ProductCode'] . '" StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
			}
			$this->xml .= ($batchMode=='true')?'</Response>':'';
		}
		//$this->xml .= ($batchMode=='true')?'</ProductStockUpdates>':'';
	}

	/*** Update Stock API Functions End ***/

	/*** Update Products API Functions Start ***/

	public function updateProductsAction() {
		// Parse resquest
		$xmlRequest = null;
		try {
			$xmlRequest = new SimpleXmlElement(file_get_contents("php://input"));
		} catch (Exception $e) {
			$this->xml .= "<Error>" . $e->getMessage() . "</Error>";
		}
		if (!is_null($xmlRequest) && $xmlRequest->getName()==='OneSaas') {
			foreach ($xmlRequest->children() as $productRequest) {
				$xml_product = new SimpleXMLElement('<ProductResponse></ProductResponse>');
				$errorMsg = $this->validateProductRequest($productRequest);
				$xml_product->Code = $productRequest->Code;
				if (is_null($errorMsg['errors']) || (is_array($errorMsg['errors']) && sizeof($errorMsg['errors'])==0)) {
					try {
						$update = isset($productRequest->attributes()->Id) && !is_null($productRequest->attributes()->Id) && ($productRequest->attributes()->Id != "");
						if ($update) {
							// Update product fields in db
							try {
								$mageFilename = 'app/Mage.php';
								require_once $mageFilename;
								Mage::setIsDeveloperMode(true);
								umask(0);
								Mage::app();
								Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
								
								$existingProduct = $this->getProductModel()->load($productRequest->attributes()->Id);
								$existingProduct->setName($productRequest->Name);
								$existingProduct->setDescription($productRequest->Description);
								$existingProduct->setShortDescription($productRequest->Description); // TODO: Might need to be cut
								$existingProduct->setSku($productRequest->Code);
								$existingProduct->setModel($productRequest->Code);
								
								if (isset($productRequest->Categories) ) {
									$categoriesArray = array();
									foreach ($productRequest->Categories->children() as $category) {
										$categoriesArray[] = (int)($category->Id);
									}
									$existingProduct->setCategoryIds($categoriesArray);
								}
								
								$existingProduct->setPrice($productRequest->SalePrice);
								$existingProduct->setCost($productRequest->CostPrice);
								if (isset($productRequest->IsActive) && ($productRequest->IsActive!="")) {
									if ($productRequest->IsActive=="true") {
										$existingProduct->setStatus(1);//1=Enabled; 2=Disabled;
									} else {
										$existingProduct->setStatus(2);//1=Enabled; 2=Disabled;
									}
								} // else { leave unchanged} 
								
								$existingProduct->setWeight($productRequest->Weight);
								
								$stock_obj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($existingProduct->getId());
								$stockData = $stock_obj->getData();
								$stockData['qty'] = $productRequest->StockAvailable;
								$stockData['is_in_stock'] = (0.0+$productRequest->StockAvailable)>0;
								$stock_obj->setData($stockData);
					
								$stock_obj->save();
								$existingProduct->save();
								
								$xml_product->Success = "Existing product updated in Magento";
								$xml_product->addAttribute("Id",$existingProduct->getId());
							} catch (Exception $e) {
							  $xml_product->ProductError = $e->getMessage();
							}
						} else {
							// Insert product fields in db
							try {
								$mageFilename = 'app/Mage.php';
								require_once $mageFilename;
								Mage::setIsDeveloperMode(true);
								umask(0);
								Mage::app();
								Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));

								$newProduct = $this->getProductModel();
								//$newProduct->setStoreId('default');
								$newProduct->setName($productRequest->Name);
								$newProduct->setDescription($productRequest->Description);
								$newProduct->setShortDescription($productRequest->Description); // TODO: Might need to be cut
								$newProduct->setSku($productRequest->Code);
								$newProduct->setModel($productRequest->Code);
								
								if (isset($productRequest->Categories) ) {
									$categoriesArray = array();
									foreach ($productRequest->Categories->children() as $category) {
										$categoriesArray[] = (int)($category->Id);
									}
									$newProduct->setCategoryIds($categoriesArray);
								} 
								
								$productEntityTypeID = $this->getProductModel()->getResource()->getTypeId();
								$attributeSetId     = Mage::getModel('eav/entity_attribute_set')
													->getCollection()
													->setEntityTypeFilter($productEntityTypeID)
													->addFieldToFilter('attribute_set_name', 'Default')
													->getFirstItem()
													->getAttributeSetId();
								$newProduct->setAttributeSetId($attributeSetId);
								
								$newProduct->setPrice($productRequest->SalePrice);
								$newProduct->setCost($productRequest->CostPrice);
								$newProduct->setStatus(1);//1=Enabled; 2=Disabled;
								$newProduct->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH); //4 = catalog & search.
								$newProduct->setWeight($productRequest->Weight); 
								
								// tax class
								// We need tax class id.  OneSaas can return tax rate code.  Cannot resolve tax class from rate (please see comment 07/Jan/14 6:41 PM on https://onesaas.atlassian.net/browse/SB-385.
								/* Commenting for now
								if (isset($productRequest->TaxCodes) && ($productRequest->TaxCodes->children() != null) && (count($productRequest->TaxCodes->children())>0)) {
									$tax_code = $productRequest->TaxCodes->children()[0]->Name;
									$tr_data = $this->getTaxRateModel()->getCollection()->getData();
									foreach($tr_data as $tr){
										if($tr['code']==$tax_code) {
											$newProduct->setTaxClassId($tr['id']);
											break;
										}
									}
								}
								*/
								// Inventory
								$stock_data=array(
								'use_config_manage_stock' => ($productRequest->IsInventoried=='true')?1:0,
								'qty' => $productRequest->StockAvailable,
								'min_qty' => 0,
								'use_config_min_qty'=>1,
								'min_sale_qty' => 1,
								'use_config_min_sale_qty'=>1,
								'max_sale_qty' => 9999,
								'use_config_max_sale_qty'=>1,
								'is_qty_decimal' => 0,
								'backorders' => 0,
								'use_config_backorders' => 1,
								'notify_stock_qty' => 0,
								'use_config_notify_stock_qty' => 1,
								'is_in_stock' => (0.0+$productRequest->StockAvailable)>0
								);
								$newProduct->setData('stock_data',$stock_data);
								$newProduct->setWebsiteIds(array(1));
								$newProduct->setTypeId('simple');
								
								$newProduct->save(); 
								
								$xml_product->Success = "New product added to Magento";
								$xml_product->addAttribute("Id",$newProduct->getId());
							} catch (Exception $e) {
							  $xml_product->ProductError = $e->getMessage();
							}
						}
					} catch (Exception $e) {
						$xml_product->ProductError = $e->getMessage();
					}
				} else {
					foreach($errorMsg['errors'] as $anErrorMsg) {
						$xml_product->addChild("ProductError", $anErrorMsg);
					}
				}
				if (!is_null($errorMsg['warnings']) && is_array($errorMsg['warnings']) && sizeof($errorMsg['warnings'])>0) {
					foreach($errorMsg['warnings'] as $aWarningMsg) {
						$xml_product->addChild("ProductWarning", $aWarningMsg);
					}
				}
				$output = substr($xml_product->asXML(), strpos($xml_product->asXML(), '?>') + 2);
				$this->xml .= $output;
			}
		} else {
			$this->xml .= "<Error>Wrong xml request format</Error>";
		}
	}
	/*** Update Products API Functions End ***/
	
	/*** General Functions Start ***/
	private function parseSingleStockUpdateRequest(SimpleXmlElement $aRequest) {
		$stockUpdateRequest = array();
		if (!is_null($aRequest) && $aRequest->getName()==='ProductStockUpdate') {
			foreach ($aRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$stockUpdateRequest['ProductCode'] = $attr;
				}
			}		
			foreach ($aRequest->children() as $child) {
				switch ($child->getName()) {
					case 'StockAtHand':
						$stockUpdateRequest['StockAtHand'] = $child;
						break;
					case 'StockAllocated':
						$stockUpdateRequest['StockAllocated'] = $child;
						break;
					case 'StockAvailable':
						$stockUpdateRequest['StockAvailable'] = (int) $child;
						break;
					default:
						// Not interested
						break;
				}
				switch ($child->getName()) {
					case 'Code':
						$stockUpdateRequest['Code'] = $child;
						break;
				}
			}
			$stockUpdateRequest;
		}
		return $stockUpdateRequest;
	}

	private function getInfoXml($entity,$id,$LUD,$info){
		if(!$id){
			$xmlStr = "<$entity>";
		}elseif($id and $LUD == null){
			$xmlStr = "<$entity id=\"$id\">";
		}else{
			$xmlStr = "<$entity id=\"$id\" LastUpdated=\"$LUD\">";
		}
		if(is_array($info)){
			foreach($info as $key=>$val){
				if(!(is_array($val))){
					$xmlStr .= "<$key>$val";
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}else{
					if($entity == 'PaymentGateways'){
						$key = 'PaymentGateway';
					}elseif($entity == 'TaxCodes'){
						$key = 'TaxCode';
					}elseif($entity == 'ShippingMethods'){
						$key = 'ShippingMethod';
					}elseif($entity == 'Categories'){
						$key = 'Category';
					}elseif($entity == 'OrderStatuses'){
						$key = 'OrderStatus';
					}elseif($entity == 'PluginFeatures'){
						$key = 'PluginFeature';
					}					
					$xmlStr .= "<$key>";
					foreach($val as $k=>$v){
						if(!(is_array($v))){
							$xmlStr .= "<$k>$v</$k>";
						}else{
							if($entity == 'Order' and $key == 'Items' and is_int($k)){
								$k = 'Item';
							}elseif($entity == 'Product' and $key == 'ComboItems' and is_int($k)){
								$k = 'ComboItem';
							}elseif($entity == 'Product' and $key == 'Options' and is_int($k)){
								$k = 'Option';
							}
							$xmlStr .= "<$k>";
							foreach($v as $k2=>$v2){
								if($k2 != 'tagAttribs'){
									$xmlStr .= "<$k2>$v2</$k2>";
								}
							}
							$k_arr = explode(' ',$k);
							$xmlStr .= "</$k_arr[0]>";
						}
					}
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}
			}
		}
		$xmlStr .= "</$entity>";
		return $xmlStr;
	}

	private function getXml($entity,$attributes=array(),$info) {
		$xmlStr = '';
		if (!is_null($attributes) && is_Array($attributes) && count($attributes)>0) {
			$xmlStr .= '<' . $entity;
			foreach ($attributes as $name => $value) {
				$xmlStr .= ' ' . $name . '="' . $value .'"';
			}
			$xmlStr .= '>';
		} else {
			$xmlStr .= "<$entity>";
		}
		if (is_array($info)) {
			foreach($info as $key=>$val) {
				if (strrpos($key,'_')) {
					$begin = substr($key,0,strrpos($key,'_'));
					$end = substr($key,strrpos($key,'_'));
					($first_sapce = strpos($end,' '))?$end = substr($end,$first_sapce):$end = '';
					$key = $begin . $end;
				}
				$xmlStr .= $this->getXml($key,null,$val);
			}
		} else {
			$xmlStr .= $info;
		}
		$closingTag = explode(' ', $entity);
		$xmlStr .= '</' . $closingTag[0] . '>';

		return $xmlStr;
	}

	private function getAccessKey(){
		$helper =  $this->getHelper();
		$key = $helper->getKey();
		return $key;
	}

	private function verifyAccessKey($userKey){
		$key = $this->getAccessKey();
		if($userKey === $key){
			return true;
		}
	}

	private function getHelper(){
		return Mage::helper('osconnect');
	}

	private function getOneSaasConnectVersion(){
		//$mod_info = (array)Mage::getConfig()->getNode('modules/Onesaas_Connect')->children();
		return '2.0.6.49';
	}

	private function getPageNoIsValid($entity){

		if($entity == 'Product') {

			$productCollectionSize = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getSize();

			$proCount = ceil($productCollectionSize/$this->pageSize);

			if($this->pageNo>$proCount) {
				return False;
			} else {
      			return True;
      		}
		}
		if($entity == 'Contact') {

			$customerCollectionSize = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getSize();

			$custCount = ceil($customerCollectionSize/$this->pageSize);

			if($this->pageNo>$custCount)

				return False;
			else

      			return True;

		}
		if($entity == 'Order') {

			$orderCollectionSize = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getSize();

			$orderCount = ceil($orderCollectionSize/$this->pageSize);

			if($this->pageNo>$orderCount)

				return False;
			else

      			return True;

		}
		if($entity == 'Invoice') {

			$invoiceCollectionSize = Mage::getModel('sales/order_invoice')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getSize();

			$invoiceCount = ceil($invoiceCollectionSize/$this->pageSize);

			if($this->pageNo>$invoiceCount)

				return False;
			else

      			return True;

		}			
	}

	private function validateProductRequest(SimpleXmlElement &$productRequest) {
		$newProduct = true;
		$existingProduct = null;
		$result = array('errors' => array(), 'warnings' => array());
		if (isset($productRequest->attributes()->Id) && ($productRequest->attributes()->Id != "")) {
			// Check Id Exists else return "Specified Id = " . $productRequest['Id'] . " does not exists in remote system"
			$product = $this->getProductModel()->load($productRequest->attributes()->Id);
			if ($product->isObjectNew()) {
				$result['errors'][] =  "Specified Id = " . $productRequest['Id'] . " does not exists.";
			} else {
				$newProduct = false;
			}
		}
		
		if (!$newProduct) {
			$existingProduct = $this->getProductModel()->load($productRequest->attributes()->Id);
		}
		
		if (!isset($productRequest->Code) || is_null($productRequest->Code) || $productRequest->Code=="") {
			$result['errors'][] =  "Product Code Missing";
		} else {
			$checkExistingId = $this->getProductModel()->load( str_replace('-sku','',$productRequest->Code));
			if ($newProduct) {
				if ($checkExistingId) {
					$result['errors'][] =  "Specified Code = " . $productRequest->Code . " already in use.";
				}
			} else {
				if ($checkExistingId && $checkExistingId!=$existingProduct->getId()) {
					$result['errors'][] =  "Specified Code = " . $productRequest->Code . " already in use.";
				}
			}
		
		}
		if (!isset($productRequest->Name) || is_null($productRequest->Name) || $productRequest->Name=="") {
			$result['errors'][] =  "Product Name Missing";
		}
		if (!isset($productRequest->Description) || is_null($productRequest->Description) || $productRequest->Description=="") {
			$result['errors'][] =  "Product Description Missing";
		}
		if (!isset($productRequest->SalePrice) || is_null($productRequest->SalePrice) || $productRequest->SalePrice=="" ) {
			$result['errors'][] =  "Product SalePrice Missing";
		} else {
			if (!is_numeric(trim($productRequest->SalePrice))) {
				$result['errors'][] =  "Product SalePrice " . $productRequest->SalePrice . " is not a number";
			}
		}
		if (!isset($productRequest->Weight) || is_null($productRequest->Weight) || $productRequest->Weight=="" ) {
			if ($newProduct) {
				$result['warnings'][] =  "Product Weight missing.  Assuming 0";
				$productRequest->Weight = 0;
			} else {
				$result['warnings'][] =  "Product Weight missing.  Leaving unchanged to: " . $existingProduct->getWeight();
				$productRequest->Weight = $existingProduct->getWeight();
			}
		} else {
			if (!is_numeric(trim($productRequest->Weight))) {
				if ($newProduct) {
					$result['warnings'][] =  "Product Weight is not a number.  Assuming 0";
					$productRequest->Weight = 0;
				} else {
					$result['warnings'][] =  "Product Weight is not a number.  Leaving unchanged to " . $existingProduct->getWeight();
					$productRequest->Weight = $existingProduct->getWeight();
				}
			}
		}
		
		return $result;
	}
	
	/*** General Functions End ***/
}
?>
