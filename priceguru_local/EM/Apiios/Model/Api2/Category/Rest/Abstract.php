<?php
class EM_Apiios_Model_Api2_Category_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
    protected function _retrieveCollection(){
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);

        /* Load Category list */
        $cate = array();
        $store_id = $this->_getStore()->getId();
        if(isset($store_id)){
            $root_id = Mage::app()->getStore($store_id)->getRootCategoryId();
        }else{
            $root_id = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId();
        }
        $root = Mage::getModel('catalog/category')->setStoreId($store_id)->load($root_id);
        $cate['menu']['id'] = $root->getId();
        $cate['menu']['name'] =	$root->getName();
        if($root->getThumbnail() != "" )
            $cate['menu']['thumb'] = Mage::getBaseUrl('media').'catalog/category/'.$root->getThumbnail();
        else
            $cate['menu']['thumb'] = $root->getThumbnail();
        $cate['menu']['products_num'] =	$root->getProductCount();
        $sub_cate = $this->buildCate($root,$store_id);
		$cate['menu']['category_list'] = $sub_cate;
        return $cate;
    }

    public function _retrieve(){
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        
        Mage::register('curent_store',$this->_getStore());
        $categoryId = $this->getRequest()->getParam('category_id');
        $products = $this->getProductCollection($categoryId);
        $products = $this->_addProductAttributesAndPrices($products);
        $thumbnailWidth = $this->getRequest()->getParam('thumbnail_width',25);
        $thumbnailHeight = $this->getRequest()->getParam('thumbnail_height',25);
         /** @var Mage_Catalog_Model_Product $product */
        $productsJson = array();
        
        foreach ($products as $product) {
            $this->_setProduct($product);
            $productsJson[] = $this->_prepareProductForResponse($product,array('thumbnail'=>array('width'=>$thumbnailWidth,'height'=>$thumbnailHeight)));
            
        }

        /* Load Category Page */
         if($categoryId){
            $name = Mage::registry('current_category')->getName();
        }
        /* Load Catalog Search Page */
        else {
            $name = $this->getRequest()->getParam('q');
        }

        //$result = new Varien_Object();
        $result = array();
        $result['result'] = array(
            'name'      => $name,
            'total'     => $products->getSize(),
            'limit'     => (int)$this->getLimit(),
            'cur_page'  => (int)$this->getRequest()->getParam('p',1),
            'products'  => $productsJson
        );
        $result['sortby'] = array(
            'available_orders' =>   $this->prepareAvailableOrdersJson(),
            'current_order'    =>   $this->getRequest()->getParam('order',$this->getSortBy()),
            'direction'        =>   $this->getRequest()->getParam('dir','ASC')
        );
        $result['attributes_filter'] = Mage::registry('layer')->getFilterableAttributesForJson();
        $result['active_filter'] = Mage::registry('layer')->getActiveFilterForJson();
        return $result;
    }

    protected function buildCate($root,$storeId = Mage_Core_Model_App::ADMIN_STORE_ID){
        $parentChild = $root->getCollection()
            ->addAttributeToSelect(array('name','thumbnail'))
            ->addAttributeToSelect('all_children')
            ->addIdFilter($root->getChildren())
            ->setProductStoreId($storeId)
            ->setLoadProductCount(true)
            ->setStoreId($storeId);
        
        foreach($parentChild as $value){
            $cat['id'] = $value->getId();
            $cat['name'] = $value->getName();
            if($value->getThumbnail() != "" )
                $cat['thumb'] =	Mage::getBaseUrl('media').'catalog/category/'.$value->getThumbnail();
            else
                $cat['thumb'] =	$value->getThumbnail();
            $cat['products_num'] = $value->getProductCount();
            if($value->hasChildren() == 1)
                $cat['category_list'] =	$this->buildCate($value,$storeId);
            else
                $cat['category_list'] =	array();
            $rs[] = $cat;
        }
        return $rs;
    }

}
?>