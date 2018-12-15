<?php
class EM_Apiios_Model_Api2_Products extends Mage_Catalog_Model_Api2_Product_Rest
{
    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;
    protected $_availableOrders;
    protected $_sortBy;
    
    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        $store = $this->_getStore();
        $entityOnlyAttributes = $this->getEntityOnlyAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
        $availableAttributes = array_keys($this->getAvailableAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ));
        // available attributes not contain image attribute, but it needed for get image_url
        $availableAttributes[] = 'image';
        //print_r($collection->getData());exit;
        $collection
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            //->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes))
            ->addPriceData($this->_getCustomerGroupId(), $store->getWebsiteId())
            ->addAttributeToFilter('visibility', array(
            'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        
        //echo (string)$collection->getSelect();exit;
        //$this->_applyCategoryFilter($collection);

        //$this->_applyCollectionModifiers($collection);
        return $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();
    }

    /**
     * Get Product Collection by category or key search
     * @param : int $categoryId
     * @return : Mage_Catalog_Model_Product_Collection
     */
    public function getProductCollection($categoryId){
        $category = null;
        if (is_null($this->_productCollection)) {
            $store = $this->_getStore();
            if($categoryId){
                $layer = Mage::getSingleton('apiios/api2_category_layer')->setStore($store);
                $category = Mage::getModel('catalog/category')
                ->setStoreId($store->getId())
                ->load($categoryId);
                Mage::register('current_category', $category);
                $layer->setCurrentCategory($category);
            }else
            {

                /* sdfdsf */

                $query = Mage::helper('apiios/catalogsearch')->getQuery();
            /* @var $query Mage_CatalogSearch_Model_Query */

            $query->setStoreId($this->_getStore()->getId());

            if ($query->getQueryText() != '') {
                if (Mage::helper('apiios/catalogsearch')->isMinQueryLength()) {
                    $query->setId(0)
                        ->setIsActive(1)
                        ->setIsProcessed(1);
                }
                else {
                    if ($query->getId()) {
                        $query->setPopularity($query->getPopularity()+1);
                    }
                    else {
                        $query->setPopularity(1);
                    }

                    if ($query->getRedirect()){
                        $query->save();
                        //$this->getResponse()->setRedirect($query->getRedirect());
                        //return;
                    }
                    else {
                        $query->prepare();
                    }
                }

                Mage::helper('apiios/catalogsearch')->checkNotes();

                }


                /* sdfdsfdsf */

                $layer = Mage::getSingleton('apiios/api2_catalogsearch_layer')->setStore($store);
            }
            Mage::register('layer',$layer);
            $this->_productCollection = $layer->getProductCollection();
            $layer->prepareAttributeFilterForJson($this->getRequest());
            
            $this->prepareSortableFieldsByCategory($category);
            
        }
        $this->_applySortByFilter();
        return $this->_productCollection;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     */
    protected function _applySortByFilter()
    {
        /* Set limit */
        
        $curPage = $this->getRequest()->getParam('p',1);
        $this->_productCollection->setPageSize($this->getLimit())->setCurPage($curPage);

        /* Set order */
        $order = $this->getRequest()->getParam('order',$this->getSortBy());
        $dir = $this->getRequest()->getParam('dir','DESC');
        $this->_productCollection->setOrder($order, $dir);
        
        return $this;

        
    }

    protected function getLimit(){
        return $this->getRequest()->getParam('limit',Mage::getStoreConfig('product_list_page/limit',$this->_getStore()->getId()));
    }

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product,$additional = array())
    {
        /** @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        //$productData = $product->getData();
        $productData = array();
        $productData['entity_id'] = $product->getId();
        $productData['type_id'] = $product->getTypeId();
        $productData['name'] = $product->getName();
        $product->setWebsiteId($this->_getStore()->getWebsiteId());

        // customer group is required in product for correct prices calculation
        $product->setCustomerGroupId($this->_getCustomerGroupId());

        // calculate prices
        /*$finalPrice = $product->getFinalPrice();
        $productData['minimal_price'] = $this->_applyTaxToPrice($product->getMinimalPrice(), true);
        $productData['min_price'] = $this->_applyTaxToPrice($product->getMinPrice(), true);
        $productData['max_price'] = $this->_applyTaxToPrice($product->getMaxPrice(), true);
        $productData['price'] = $this->_applyTaxToPrice($product->getPrice(), true);
        $productData['final_price'] = $this->_applyTaxToPrice($finalPrice, true);*/
        $productData['prices'] = Mage::helper('apiios')->showPrice($product,true);
        $productData['tier_price'] = $this->_getTierPrices();
        $productData['is_saleable'] = $product->getIsSalable();
        
        $rating = $this->getRating($product);
        $productData['total_reviews_count'] = $rating['total_reviews_count'];
        $productData['rating_summary'] = $rating['rating_summary'];

        if(isset($additional['thumbnail'])){
            $productData['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize($additional['thumbnail']['width'],$additional['thumbnail']['height']);
        }
        else
            $productData['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');
        return $productData;
    }

    public function getAvailableOrders(){
        return $this->_availableOrders;
    }

    public function setAvailableOrders($availableOrders){
        $this->_availableOrders = $availableOrders;
        return $this;
    }

    public function getSortBy(){
        return $this->_sortBy;
    }

    public function setSortBy($sortBy){
        $this->_sortBy = $sortBy;
        return $this;
    }

    public function prepareAvailableOrdersJson(){
        $result = array();
        foreach($this->getAvailableOrders() as $key => $order){
            $result[] = array(
                'value' =>  $key,
                'label' =>  $order
            );
        }
        return $result;
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFieldsByCategory($category = null) {
        if (!$this->getAvailableOrders()) {
            if($category)
                $this->setAvailableOrders($category->getAvailableSortByOptions());
            else{
                $category = Mage::getSingleton('catalog/layer')
                ->getCurrentCategory();
                /* @var $category Mage_Catalog_Model_Category */
                $availableOrders = $category->getAvailableSortByOptions();
                unset($availableOrders['position']);
                $availableOrders = array_merge(array(
                    'relevance' => Mage::helper('apiios')->__('Relevance')
                ), $availableOrders);

                $this->setAvailableOrders($availableOrders)
                    //->setDefaultDirection('desc')
                    ->setSortBy('relevance');
            }
        }
        
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($categorySortBy = $category->getDefaultSortBy()) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * Get Rating for a product
     * input : Mage_Catalog_Model_Product
     * output : Array('total' => ?,'avg' => ?)
     */
    protected function getRating($_product){
        $result = array();
        $store = $this->_getStore();
        $_reviews = Mage::getModel('review/review')->getResourceCollection();
        $_reviews->addStoreFilter( $store->getId() )
                        ->addEntityFilter('product', $_product->getId())
                        ->addStatusFilter( Mage_Review_Model_Review::STATUS_APPROVED )
                        ->setDateOrder()
                        ->addRateVotes();
        $result['total_reviews_count'] = $_reviews->count();
        $avg = 0;
        $ratings = array();
        if ($_reviews->count() > 0){
            foreach ($_reviews as $_review){
                    foreach( $_review->getRatingVotes() as $_vote )
                    $ratings[] = $_vote->getPercent();
            }
            if(count($ratings))
                $avg = array_sum($ratings)/count($ratings);
            else
                $avg = 0;
        }
        $result['rating_summary'] = $avg;
        return $result;
    }
}
?>