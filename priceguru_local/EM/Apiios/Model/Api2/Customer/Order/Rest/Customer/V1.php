<?php
class EM_Apiios_Model_Api2_Customer_Order_Rest_Customer_V1 extends Mage_Sales_Model_Api2_Order
{
    /**
     * Retrieve collection instance for orders
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        return parent::_getCollectionForRetrieve()->addAttributeToFilter(
            'customer_id', array('eq' => $this->getApiUser()->getUserId())
        );
    }

    /**
     * Get orders list
     *
     * @return array
     */
    protected function _retrieve()
    {
        $collection = $this->_getCollectionForRetrieve();

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        $this->_addTaxInfo($collection);

        $ordersData = array();
        $helperOrder = Mage::helper('apiios/order')->setStore($this->_getStore());
        foreach ($collection->getItems() as $order) {
            $data = $order->toArray();
			$dataReturn = array();
			$dataReturn['increment_id'] = $data['increment_id'];
			$dataReturn['status'] = $data['status'];
			$dataReturn['created_at'] = $data['created_at'];
			$dataReturn['tax'] = array(
				'value'	=>	$order->formatPriceTxt($order->getTaxAmount()),
				'label'	=>	$helperOrder->__('Tax')
			);
			$dataReturn['order_total'] = $order->formatPriceTxt($order->getGrandTotal());
            $dataReturn['status_label'] = $order->getStatusLabel();
            $dataReturn['shipping_description'] = $order->getShippingDescription();
            $dataReturn['payment_method_name'] = $order->getPayment()->getMethodInstance()->getTitle();
            $dataReturn['subtotal'] = $helperOrder->_initSubtotal($order);
            $dataReturn['grand_total'] = $helperOrder->_initGrandTotal($order);
            $dataReturn['shipping_amount'] = $helperOrder->_initShipping($order);
            $ordersData[$order->getId()] = $dataReturn;
        }
        if ($ordersData) {
            foreach ($this->_getAddresses(array_keys($ordersData)) as $orderId => $addresses) {
                $ordersData[$orderId]['addresses'] = $addresses;
            }
            foreach ($this->_getItems(array_keys($ordersData)) as $orderId => $items) {
                $ordersData[$orderId]['order_items'] = $items;
				$ordersData[$orderId]['order_items_label'] = Mage::helper('customer')->__('Ordered Items');
            }
            foreach ($this->_getComments(array_keys($ordersData)) as $orderId => $comments) {
                $ordersData[$orderId]['order_comments'] = $comments;
            }
        }
		$orderReturn = array();
		foreach($ordersData as $order){
			$orderReturn[] = $order;
		}
		return array(
			'list_order'	=>	$orderReturn,
			'qty_label'		=>	array(
				'qty_ordered' => Mage::helper('sales')->__('Ordered'),
				'qty_shipped' => Mage::helper('sales')->__('Shipped'),
				'qty_canceled' => Mage::helper('sales')->__('Canceled'),
				'qty_refunded' => Mage::helper('sales')->__('Refunded'),
                'order_total'   => Mage::helper('sales')->__('Order Total')
			)
		);
        /*$orderReturn['qty_label'] = array(
            'qty_ordered' => Mage::helper('sales')->__('Ordered'),
            'qty_shipped' => Mage::helper('sales')->__('Shipped'),
            'qty_canceled' => Mage::helper('sales')->__('Canceled'),
            'qty_refunded' => Mage::helper('sales')->__('Refunded')
        );*/
        //return $orderReturn;
    }

    /**
     * Retrieve a list or orders' items in a form of [order ID => array of items, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getItems(array $orderIds)
    {
        $items = array();

        if ($this->_isSubCallAllowed('order_item')) {
            /** @var $items Filter Mage_Api2_Model_Acl_Filter */
            $itemsFilter = $this->_getSubModel('order_item', array())->getFilter();
            // do items request if at least one attribute allowed
            if ($itemsFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
                $collection = Mage::getResourceModel('sales/order_item_collection');
                $helperItem = Mage::helper('apiios/item')->setStore($this->_getStore());
                $collection->addAttributeToFilter('order_id', $orderIds);
                $bundleItem = array();
                foreach ($collection->getItems() as $item) {
                    if($item->getProductType() == 'bundle')
                        $bundleItem[] = $item->getId();
                    if(!$item->getParentItemId() || in_array($item->getParentItemId(),$bundleItem)){
                        $data = $item->toArray();
						$dataReturn = array(
							'name'			=>	$data['name'],
							'sku'			=>	$data['sku'],
							'qty_ordered'	=>	$data['qty_ordered'],
							'qty_shipped'	=>	$data['qty_shipped'],
							'qty_canceled'	=>	$data['qty_canceled'],
							'qty_refunded'	=>	$data['qty_refunded'],
							'parent_item_id'=>	$data['parent_item_id'],
                            'item_id'       =>  $data['item_id']
						);
						//$dataReturn['name'] = $da
                        /*if(isset($data['product_options']))
                            unset($data['product_options']);*/
                        if(!$item->getParentItemId())
                            $dataReturn['options'] = $helperItem->getItemOptionsArray($item);
                        $dataReturn['prices'] = Mage::helper('apiios/order')->getPriceOrderItem($item);
                        $dataReturn['total'] = Mage::helper('apiios/order')->getPriceOrderItem($item,'total');
                        $items[$item->getOrderId()][] = $dataReturn;
                    }
                }
            }
        }
        return $items;
    }
	
	/**
     * Retrieve a list or orders' addresses in a form of [order ID => array of addresses, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getAddresses(array $orderIds)
    {
        $addresses = array();

        if ($this->_isSubCallAllowed('order_address')) {
            /** @var $addressesFilter Mage_Api2_Model_Acl_Filter */
            $addressesFilter = $this->_getSubModel('order_address', array())->getFilter();
            // do addresses request if at least one attribute allowed
            if ($addressesFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Address_Collection */
                $collection = Mage::getResourceModel('sales/order_address_collection');

                $collection->addAttributeToFilter('parent_id', $orderIds);

                foreach ($collection->getItems() as $item) {
					$data = $addressesFilter->out($item->toArray());
					if($data['address_type'] == 'billing')
						$data['label'] = Mage::helper('customer')->__('Default Billing Address');
					else if($data['address_type'] == 'shipping')
						$data['label'] = Mage::helper('customer')->__('Default Shipping Address');
                    $data['address_id'] = $item->getCustomerAddressId();
                    $data['name'] = $item->getName();
                    $addresses[$item->getParentId()][] = $data;
                }
            }
        }
        return $addresses;
    }
}
?>
