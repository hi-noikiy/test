<?php
class EM_Recentview_Block_List extends Mage_Reports_Block_Product_Abstract implements Mage_Widget_Block_Interface
{
    const XML_PATH_RECENTLY_VIEWED_COUNT    = 'catalog/recently_products/viewed_count';

    /**
     * Viewed Product Index model name
     *
     * @var string
     */
    protected $_indexName       = 'reports/product_index_viewed';

    protected function _construct()
    {	
		if($this->getCacheLifeTime())
		{
			$this->addData(array(
				'cache_lifetime'    => $this->getCacheLifeTime(),
				'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG)
			));
		}
		else	
		{
			$this->addData(array(
				'cache_lifetime'    => 7200,
				'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG)
			));
		}
		parent::_construct();
        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
    }  
    
	public function _prepareLayout()
	{	
		return parent::_prepareLayout();
	}

	protected function _toHtml()
	{	
		if($this->getData('choose_template')	==	'custom_template')
		{
			if($this->getData('custom_theme'))
				$this->setTemplate($this->getData('custom_theme'));	
			else 
				$this->setTemplate('em_recentview/custom.phtml');	
		}
		else
		{
			$this->setTemplate($this->getData('choose_template'));	
		}
		return parent::_toHtml();
	}
    
	public function getCategories()
	{
		$strCategories=  $this->getData('new_category');
		$arrCategories = explode(",", $strCategories);
		return $arrCategories;
	}
    
	public function getColumnCount(){
		return $this->getData('column_count');
	}
    
    public function getCustomClass(){
		return $this->getData('custom_class');
	}
    
	public function getLimitCount(){
		if($this->getData('limit_count'))
			return $this->getData('limit_count');
		return 10;
	}
	
	public function getPageSize(){
		return $this->getLimitCount();
	}
    
	public function getFeatureChoosed(){
		return $this->getData('featured_choose');
	}
    
	public function getOrderBy(){
	   return $this->getData('order_by');
	}
	
	public function getCacheLifeTime(){		
	   return $this->getData('cache_lifetime');
	}
    
    public function getThumbnailWidth(){
        $tempwidth = $this->getData('thumbnail_width');
        if (!(is_numeric($tempwidth)))
            $tempwidth = 150;
        return $tempwidth;
	}
    
    public function getThumbnailHeight(){
        $tempheight = $this->getData('thumbnail_height');
       if (!(is_numeric($tempheight)))
            $tempheight = 150;
        return $tempheight;
	}
	
	public function getItemWidth(){
        $tempwidth = $this->getData('item_width');
        if (!(is_numeric($tempwidth)))
            $tempwidth = null;
        return $tempwidth;
	}
    
    public function getItemHeight(){
        $tempheight = $this->getData('item_height');
       if (!(is_numeric($tempheight)))
            $tempheight = null;
        return $tempheight;
	}
	
	public function getItemSpacing(){
        $tempheight = $this->getData('item_spacing');
       if (!(is_numeric($tempheight)))
            $tempheight = null;
        return $tempheight;
	}
    
    public function getFrontendTitle(){
        return $this->getData('frontend_title');
	}
    
    public function getFrontendDescription(){
        return $this->getData('frontend_description');
	}
	
    public function ShowThumb(){
        return $this->getData('show_thumbnail');
	}
    
    public function getAltImg(){
        return $this->getData('alt_img');
	}
    
    public function ShowProductName(){
        return $this->getData('show_product_name');
	}
    
    public function ShowDesc(){
        return $this->getData('show_description');
	}
    
    public function ShowPrice(){
        return $this->getData('show_price');
	}
    
    public function ShowReview(){
        return $this->getData('show_reviews');
	}
    
    public function ShowAddtoCart(){
        return $this->getData('show_addtocart');
	}
    
    public function ShowAddto(){
        return $this->getData('show_addto');
	}
    
    public function ShowLabel(){
        return $this->getData('show_label');
	}

    /**
     * Retrieve Index Product Collection
     *
     * @return Mage_Reports_Model_Resource_Product_Index_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (is_null($this->_collection)) {
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();

            $this->_collection = $this->_getModel()
                ->getCollection()
                ->addAttributeToSelect($attributes);

            if ($this->getCustomerId()) {
                $this->_collection->setCustomerId($this->getCustomerId());
            }

            $this->_collection->excludeProductIds($this->_getModel()->getExcludeProductIds())
                ->addUrlRewrite()
                ->setPageSize($this->getPageSize())
                ->setCurPage(1);

            $ids = $this->getProductIds();
            if (empty($ids)) {
                $this->_collection->addIndexFilter();
            } else {
                $this->_collection->addFilterByIds($ids);
            }
            /* Price data is added to consider item stock status using price index */
            $this->_collection->addPriceData();
            $this->_collection->setAddedAtOrder();
            $this->_collection->addAttributeToFilter('visibility',array("neq"=>1));
        }

        return $this->_collection;
    }
    
    protected function getProductCollection()
	{
		$config2 = $this->getData('order_by');
        if(isset($config2)){      
           $orders = explode(' ',$config2);
        }
		
		$products = $this->getItemsCollection();
		//Filter by categories
		$config1 = $this->getData('new_category');
		if($config1)
		{
			$alias = 'cat_index';
			$categoryCondition = $products->getConnection()->quoteInto(
			$alias.'.product_id=e.entity_id AND '.$alias.'.store_id=? AND ',
			$products->getStoreId()
			);
			$categoryCondition.= $alias.'.category_id IN ('.$config1.')';

			$products->getSelect()->joinInner(
			array($alias => $products->getTable('catalog/category_product_index')),
			$categoryCondition,
			array()
			);
			$products->_categoryIndexJoined = true;
			$products->distinct(true);
		}
        return $products;

	}
}