<?php
class EM_Apiios_Model_Api2_Blockproducts_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{	
	protected function _construct()
    {
		$this->addData(array(
			'cache_lifetime'    => 7200,
			'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG)
		));
		parent::_construct();
    }   

    protected function _retrieveCollection(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $result = new Varien_Object();

		$storeId 	= $this->_getStore()->getId();
		if($this->getRequest()->getParam('types'))
			$type	= $this->getRequest()->getParam('types');
		else	$type	= 'new';
		$thumb_w 	= $this->getRequest()->getParam('thumbnail_width');
		$thumb_h  	= $this->getRequest()->getParam('thumbnail_height');
		$limit 		= $this->getRequest()->getParam('limit');

		if($limit == 0)	$limit = 10;
		if($thumb_w == 0)	$thumb_w	=	100;
		if($thumb_h == 0)	$thumb_h	=	$thumb_w;

		if($type == 'featured')
			$products	=	$this->getFeatured($storeId,$limit);
		else if($type == 'popular')
			$products	=	$this->getPopularProducts($storeId,$limit);
		else if($type == 'bestseller')
			$products	=	$this->getBestSellerProducts($storeId,$limit);
		else
			$products	=	$this->getNew($storeId,$limit);
		if($exclude = Mage::getStoreConfig('apiios/general/exclude_products_sku')){
			$products->addAttributeToFilter('sku',array('nin' => explode(',',$exclude)));
		}
		
		//print_r($products->getData());exit;
        $productsJson = array();
        $additional = array('thumbnail' => array(
            'width' =>  $thumb_w,
            'height'=>  $thumb_h
        ));
        foreach($products as $item){
            $this->_setProduct($item);
            $productsJson[] = $this->_prepareProductForResponse($item,$additional);
        }
		$result->list_products	=	$productsJson;
    	return $result;
    }

	protected function getNew($storeId,$limit){
		/********** New Product	************/
		$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $products = Mage::getResourceModel('catalog/product_collection')->setStoreId($storeId)->addStoreFilter();
        $products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $products = $this->_addProductAttributesAndPrices($products)

            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
            ->addAttributeToSort('entity_id', 'desc')
			->setPageSize($limit);
		return $products;
	}

	protected function getFeatured($storeId,$limit){
		$products = Mage::getResourceModel('catalog/product_collection')->setStoreId($storeId)->addStoreFilter();
        $products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $products = $this->_addProductAttributesAndPrices($products)
			->addAttributeToFilter('featured_product',1)
			->setPageSize($limit);
		return $products;
	}

	protected function getBestsellerProductArray($storeId,$limit = 10)
	{		
		$websiteId = $this->_getStore()->getWebsite()->getId();
		if($exclude = Mage::getStoreConfig('apiios/general/exclude_products_sku')){
			$limit += count(explode(',',$exclude));
		}
		$query = "	SELECT SUM( order_items.qty_ordered ) AS  `ordered_qty` ,  `order_items`.`name` AS  `order_items_name` ,  `order_items`.`product_id` AS  `entity_id` ,  `e`.`entity_type_id` ,  `e`.`attribute_set_id` , `e`.`type_id` ,  `e`.`sku` ,  `e`.`has_options` ,  `e`.`required_options` ,  `e`.`created_at` ,  `e`.`updated_at` 
					FROM  `".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item')."` AS  `order_items` 
					INNER JOIN  `".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')."` AS  `order` ON  `order`.entity_id = order_items.order_id
					AND  `order`.state <>  'canceled'
					LEFT JOIN  `".Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')."` AS  `e` ON e.entity_id = order_items.product_id
					INNER JOIN  `".Mage::getSingleton('core/resource')->getTableName('catalog_product_website')."` AS  `product_website` ON product_website.product_id = e.entity_id
					AND product_website.website_id =  '".$websiteId."'
					WHERE (
					parent_item_id IS NULL
					)
					GROUP BY  `order_items`.`product_id` 
					HAVING (
					SUM( order_items.qty_ordered ) >0
					)
					ORDER BY  `ordered_qty` DESC 
					LIMIT 0 ,".$limit."
					";
		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
        return $readConnection->fetchAll($query);

	}
    
    public function getBestSellerProducts($storeId,$limit){
		$_bestseller_products = $this->getBestsellerProductArray($storeId,$limit);
		$_temp_productIds = array();
		$count=0; 
		
        /**
         * Build up a case statement to ensure the order of ids is preserved
         */
        $orderString = array('CASE e.entity_id');

        foreach ($_bestseller_products as $i => $_product){
		
			if(in_array($_product['entity_id'],$_temp_productIds))
			{
				continue;
			}
			else
			{
				$_temp_productIds[] = $_product['entity_id'];
                $orderString[] = 'WHEN '.$_product['entity_id'].' THEN '.$i;
				$count++;
				if($count == $limit)
				{
					break;
				}
			}
		}

        $orderString[] = 'END';
        $orderString = implode(' ', $orderString);

		$products= Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('status', array('neq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED))
		    ->addAttributeToFilter('visibility',array("neq"=>1))
			->addAttributeToFilter('entity_id',array('in' => $_temp_productIds))
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
		if($_temp_productIds){
			$products->getSelect()
				->order(new Zend_Db_Expr($orderString));
		}
		return $products;	
	}
	
	public function getPopularProducts($storeId, $limit){
		$collection = Mage::getResourceModel('reports/product_collection');
		$this->_addProductAttributesAndPrices($collection);
		$collection
			->setStoreId($storeId)
			->addStoreFilter($storeId)
			->addViewsCount()
			->setPageSize($limit)
			->setCurPage(1)
			->setOrder('views_count', 'desc');

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		return $collection;
	}
}
?>