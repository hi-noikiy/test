<?php
/**
pradeep.kumarrcs67@gmail.com

*/
class Sugarcode_Bestsellersort_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
   
        public function getAvailableOrders()
    {
		$this->addOrderToAvailableOrders('mostviewd', 'Most Popular');
		//$this->addOrderToAvailableOrders('bestseller', 'Best Seller');
		$this->setDefaultOrder('mostviewd');
		$this->setDefaultDirection('asc');
		return $this->_availableOrder;
    }
		
	
	    public function setCollection($collection)
    {
	   $this->_collection = $collection;
	    $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
		
		$this->_collection->getSize();
      	if($this->getCurrentOrder()=='mostviewd') {
		$this->_collection->getSelect()->
                 joinLeft('ln_mostviewed AS _table_views',
                         ' _table_views.entity_id = e.entity_id',
                         '_table_views.view_count AS views')->
                 order('views DESC');
				 //$this->_collection->printlogquery(true);  
			$sql = $this->_collection->getSelectSql(true);
           $this->_collection->getSelect()->reset()->from(
                   array('e' =>new Zend_Db_Expr("({$sql})")),
                   array('e' => "*")
               );  
		}
		
		// if($this->getCurrentOrder()=='bestseller') {
			//$this->_collection->getSelect()->order('ordered_qty DESC');
			// $this->_collection->getSelect()->
                 // joinLeft('sales_flat_order_item AS order_item',
                         // 'e.entity_id = order_item.product_id',
                         // 'SUM(order_item.qty_ordered) AS ordered_qty')->
                 // group('e.entity_id')->order('ordered_qty DESC');;
		 
				//$this->_collection->printlogquery(true);  
			// $sql = $this->_collection->getSelectSql(true);
           // $this->_collection->getSelect()->reset()->from(
                   // array('e' =>new Zend_Db_Expr("({$sql})")),
                   // array('e' => "*")
               // );  
				 
		// }
			 
		  	    
      $this->_collection->load();
		//  	$this->_collection->printlogquery(true);  
		parent::setCollection($this->_collection);
        return  $this;
    }
	

	
    /**
     * Get grit products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->_orderField;

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->getRequest()->getParam($this->getOrderVarName());
		/*pradeep to select deafult order or sort  start */
		if($order =='') {
			$order=$this->_orderField;
		}
		/*pradeep to select deafult order or sort  end */
        if ($order && isset($orders[$order])) {
            if ($order == $defaultOrder) {
                Mage::getSingleton('catalog/session')->unsSortOrder();
            } else {
                $this->_memorizeParam('sort_order', $order);
            }
        } else {
            $order = Mage::getSingleton('catalog/session')->getSortOrder();
        }
        // validate session value
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }
        $this->setData('_current_grid_order', $order);
        return $order;
    }

	
	
    public function getTotalNum()
    {
	
	//$this->getCollection()->printlogquery(true); exit;
        return $this->getCollection()->getSize();
    }
	
    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = array('asc', 'desc');
        $dir = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));
		/*pradeep to select deafult direction  start */
		if($dir =='') {
			$dir=$this->_direction;
			
		}
		/*pradeep to select deafult direction  start */
		
        if ($dir && in_array($dir, $directions)) {
            if ($dir == $this->_direction) {
                Mage::getSingleton('catalog/session')->unsSortDirection();
            } else {
                $this->_memorizeParam('sort_direction', $dir);
            }
        } else {
            $dir = Mage::getSingleton('catalog/session')->getSortDirection();
        }
        // validate direction
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }
        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

	
	
	
	}