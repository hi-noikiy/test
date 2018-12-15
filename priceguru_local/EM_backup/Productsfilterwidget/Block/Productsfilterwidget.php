<?php
class EM_Productsfilterwidget_Block_Productsfilterwidget 
extends Mage_Catalog_Block_Product_Abstract
implements Mage_Widget_Block_Interface
{
    /**
     * Retrieve loaded category collection
     *	$midM = round(memory_get_usage()/1048576,2) . "\n"; // 36640
		$usedM = $midM-$startM;
		echo "<br>Dung 1 : {$usedM}</br>";
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
	protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';
    protected $_productCollection;
	protected $_productIds;
	protected $_arr;
	
	public function getLoadedProductCollection()
	{	
		return $this->getProductCollection();
	}
	
    protected function getProductCollection()
    {
		$display_out_of_stock = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			
		$in = $this->getConfigs();
		$reg = Mage::registry('config');
		
		if($reg)
			Mage::unregister('config');
		Mage::register('config',$in);
		
		$actionsArr =unserialize($in['conditions']);
		
		if($actionsArr['conditions'])
		{			
			$conditions=$actionsArr['conditions'];		
			$strAttribute=$this->getStrAttribute($conditions);
			$arrAttribute=$this->getArrAttribute($strAttribute);			
		}
		else
		{
			$arrAttribute=array();
		}	
		
		if(Mage::registry('arrAttribute'))
			Mage::unregister('arrAttribute');
		if(!empty($arrAttribute))	
			Mage::register('arrAttribute',$arrAttribute);	
		
		$catalogRule = Mage::getModel('productsfilterwidget/rule');
		if (!empty($actionsArr) && is_array($actionsArr))
		{
			$catalogRule->getConditions()->loadArray($actionsArr);
		}
		
		$catarule=Mage::registry('catalogRule');
		if($catarule)Mage::unregister('catalogRule');	
		Mage::register('catalogRule',$catalogRule);		
						
		$storeId = Mage::app()->getStore()->getId();		
		
		$lib_multicache	=	Mage::helper('productsfilterwidget/multicache');		
		$productIds	=	$lib_multicache->get('conditions_'.$storeId.'_'.$in['time']);
		if(!$productIds)
		{	
			$productIds=Mage::getModel('productsfilterwidget/productrule')->getMatchingProductIds();
			if(!$productIds) $productIds = 'empty';
			$lib_multicache	=	Mage::helper('productsfilterwidget/multicache');
			$lib_multicache->set('conditions_'.$storeId.'_'.$in['time'],$productIds,$in['cache_time']*60);			
		}		
		
		if($productIds	==	'empty')	$productIds = '';
		
		if($in['special']	==	2){ // New Product
			$result = Mage::getModel('productsfilterwidget/product')->getCollection()->addAttributeToFilter('entity_id',array(
														'in' => $productIds
														))
					->addAttributeToSelect('*');									;
			$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
					
					$result->addAttributeToFilter('news_from_date', array('or'=> array(
						0 => array('date' => true, 'to' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					->addAttributeToFilter('news_to_date', array('or'=> array(
						0 => array('date' => true, 'from' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					->addAttributeToFilter(
						array(
							array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
							array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
							)
					)
					->addAttributeToSort('news_from_date', 'desc');
		}
		elseif($in['special']	==	3){	//	Bestselling
			$result = Mage::getResourceModel('reports/product_collection');
			$result->addAttributeToSelect('*')
											->addOrderedQty()
											->setOrder('ordered_qty', 'desc')
											->addAttributeToFilter('entity_id',array(
													'in' => $productIds
													));
		}
		elseif($in['special']	==	4){	//	Most Viewed
			$result = Mage::getResourceModel('reports/product_collection')->addAttributeToSelect('*');
			 //Join if use catalog flat
			$result->getSelect()
						->join(
							array('entity_table'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
							'e.entity_id=entity_table.entity_id',
							array()
						);
				
			$result->addViewsCount()->addAttributeToFilter('entity_id',array('in' => $productIds));
			
		}
		elseif($in['special']	==	5){	//	Is special
			
			$result = Mage::getModel('productsfilterwidget/product')->getCollection()->addAttributeToFilter('entity_id',array(
														'in' => $productIds
														))
						->addAttributeToSelect('*');
			
			$todayDate = date('m/d/y');
			$tomorrow = mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
			$tomorrowDate = date('m/d/y', $tomorrow);
						
			$result->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
				->addAttributeToFilter('special_to_date', array('or'=> array(
				0 => array('date' => true, 'from' => $tomorrowDate),
				1 => array('is' => new Zend_Db_Expr('null')))
				), 'left');
			
		}
		else{	//	Normal
			$result = Mage::getModel('productsfilterwidget/product')->getCollection()->addAttributeToFilter('entity_id',array(
														'in' => $productIds
														))
						->addAttributeToSelect('*');
		}
		
		if($in['sort_by'] == 'random'){
			
			$result->getSelect()->order(new Zend_Db_Expr('RAND()'));
		}
		else{
			
			$result->setOrder($in['sort_by'],$in['sort_direction']);
		}
		if($display_out_of_stock==0)//hide product out of stock
		{		
			$table	=	Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_status');
			$result->getSelect()->joinLeft(array('cain'=>$table),'e.entity_id=cain.product_id',array('cain.stock_status'))->where('cain.stock_status =1');
		}
		
		$result->setPageSize($in['limit_count'])->setcurPage($this->getRequest()->getParam('p',1));			
		
		$this->setCollection($result);	
		$this->_defaultToolbarBlock = 'productsfilterwidget/toolbar';
		
		return $this->_productCollection;	

    }
	 
	function getStrAttribute($conditions)
	{
	
		foreach($conditions as $attribute)
				{
					if($attribute['attribute'])
					{
						
							$this->_arr.=$attribute['attribute'].",";
					}
					if(isset($attribute['conditions']))
					{	
						$conditions=$attribute['conditions'];
						$this->getStrAttribute($conditions);
					}
				}
		return $this->_arr;
	}
	
	public function getArrAttribute($str)
	{
		
		$arr=explode(',',$str,-1);
		$n=count($arr);
		$arr1=array();
		$arr1[]=$arr[0];
			for($i=1;$i<$n;$i++)
			{
				if($this->check($arr[$i],$arr1))
					$arr1[]=$arr[$i];
			}
		return $arr1;
	}
	public function check($x,$arr)
	{
		$n=count($arr);
		for($i=0;$i<$n;$i++)
		{
			if ($arr[$i]==$x)
				return false;
		}
		return true;
	}
    public function getProductsFilterWidget()     
    { 
        if (!$this->hasData('productsfilterwidget')) {
            $this->setData('productsfilterwidget', Mage::registry('productsfilterwidget'));
        }
        return $this->getData('productsfilterwidget');
        
    }
	
	public function getConfigs()
	{	
		$input['cache_time']		=	$this->getData('cache_time');
		$input['col_count']			=	$this->getData('col_count');
		$input['limit_count']		=	$this->getData('limit_count');
		$input['sort_by']			=	$this->getData('sort_by');
		$input['sort_direction']	=	$this->getData('sort_direction');		
		$input['special']			=	$this->getData('special');
		$input['toolbar']			=	$this->getData('toolbar');
		$plit	=	explode('-',$this->getData('conditions'));
		$count	=	count($plit);
		$tam	=	$plit[0];
		for($i=1;$i<$count-1;$i++){
			$tam	.=	'-'.$plit[$i];
		}
		$input['conditions']		=	Mage::helper('core')->urlDecode($tam);
		
		$input['time']				=	$plit[$count-1];
		
		return $input;
	}
	
	protected function _toHtml()
    {
		if($this->getData('choose_template')	==	'custom_template')
		{
			if($this->getData('custom_theme'))
				$this->setTemplate($this->getData('custom_theme'));	
			else 
				$this->setTemplate('em_productsfilterwidget/productsfilterwidget_custom.phtml');	
		}
		else
		{
			$this->setTemplate($this->getData('choose_template'));	
		}
		return parent::_toHtml();
    }
	
	public function getColumnCount()
	{
		return $this->getData('col_count');
	}
	
	
	public function getProductItemWidth(){
        $tempwidth = $this->getData('product_width');
        if (!(is_numeric($tempwidth)))
            $tempwidth = 300;
        return $tempwidth;
	}
    
    public function getProductItemHeight(){
        $tempheight = $this->getData('product_height');
       if (!(is_numeric($tempheight)))
            $tempheight = 300;
        return $tempheight;
	}
	
	public function getFrontendTitle(){
        return $this->getData('frontend_title');
	}
	public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }
	
	/**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }
	
	public function getToolbarHtml() 
    { 
        $this->setToolbar($this->getLayout()->createBlock('catalog/product_list_toolbar', 'Toolbar'));
        $toolbar = $this->getToolbar();
        $toolbar->enableExpanded();
        $toolbar->setAvailableOrders(array(
        'ordered_qty'  => $this->__('Most Purchased'),
        'name'      => $this->__('Name'),
        'price'     => $this->__('Price')
        ))
        ->setDefaultOrder('ordered_qty')
        ->setDefaultDirection('desc')
        ->setCollection($this->_productCollection);
        
        $pager = $this->getLayout()->createBlock('page/html_pager', 'Pager');
        $pager->setCollection($this->_productCollection);
        $toolbar->setChild('product_list_toolbar_pager',$pager);
        //$toolbar->addToChildGroup('product_list_toolbar',$pager);
        //print_r($pager->_toHtml());exit;
        //parent::getToolbarHtml();
        return $toolbar->_toHtml();
    }
	
	/**
     * Retrieve Toolbar block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }
	
}
