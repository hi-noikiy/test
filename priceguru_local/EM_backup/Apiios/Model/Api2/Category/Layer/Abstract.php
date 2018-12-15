<?php
class EM_Apiios_Model_Api2_Category_Layer_Abstract extends Mage_Catalog_Model_Layer
{
    protected $_attributesFilterForJson;
    protected $_activeFilterForJson;
    protected $_displayProductCount;
    protected $_store;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'STORE_'.$this->getStore()->getId()
                . '_CAT_' . $this->getCurrentCategory()->getId()
                . '_CUSTGROUP_' . Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        return $this->_stateKey;
    }

    /**
     * Retrieve current store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return $this->getStore();
    }

    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection
     */
    public function getFilterableAttributes()
    {
//        $entity = Mage::getSingleton('eav/config')
//            ->getEntityType('catalog_product');

        $setIds = $this->_getSetIds();
        if (!$setIds) {
            return array();
        }
        /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setAttributeSetFilter($setIds)
            ->addStoreLabel($this->getStore()->getId())
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }

    /**
     * Initialize product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return EM_Apiios_Model_Api2_Category_Layer
     */
    public function prepareProductCollection($collection)
    {
        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        return $this;
    }

    public function  prepareAttributeFilterForJson(Zend_Controller_Request_Abstract $request) {

        $attributesFilter = $this->getFilterableAttributes();
        $this->_attributesFilterForJson = array();
        
        $this->_attributesFilterForJson[] = array(
           'attribute_code' =>  'cat',
           'name'           =>  Mage::helper('core')->__('Category'),
           'items'          =>  $this->getCategoryItemsFilterToJson($request)
        );
        
        foreach($attributesFilter as $attribute){
           $this->_attributesFilterForJson[] = array(
               'attribute_code' =>  $attribute->getAttributeCode(),
               'name'           =>  $attribute->getStoreLabel(),
               'items'          =>  $this->getItems($request,$attribute)
           );
        }
        $this->prepareActiveFilterForJson();
        return $this->_attributesFilterForJson;
    }

    public function prepareActiveFilterForJson(){
        $this->_activeFilterForJson = array();
        foreach($this->getActiveFilters() as $item){
            $data = array(
                'name'              =>  $item->getName(),
                'label'             =>  $item->getLabel(),
                'value'             =>  $item->getValue(),
                'count'             =>  $item->getCount(),
                //'reset'             =>  $item->getFilter()->getResetValue()
            );
            try {
                $attributeCode = $item->getFilter()->getAttributeModel()->getAttributeCode();
            } catch (Exception $exc) {
                $attributeCode = 'cat';
            }
            $data['attribute_code'] = $attributeCode;
            $this->_activeFilterForJson[] = $data;

        }//exit;
        return $this->_activeFilterForJson;
    }

    /*
     * Get children categories of current category
     * @param  : Zend_Controller_Request_Abstract $request, int $storeId
     * @return : Array(
                    Array(
                        'label'   => string
                        'count'   => int
                        'item_id' => int
                    )
                )
     */
    public function getCategoryItemsFilterToJson(Zend_Controller_Request_Abstract $request){
        $_filterModel = Mage::getModel('apiios/api2_category_layer_filter_category')->setStore($this->getStore())->setLayer($this)->applyios($request);
        $items = $_filterModel->getItems();
        $itemsToJson = array();
        if(is_array($items)){
            foreach($items as $item){
                $itemsToJson[] = array(
                    'label'         =>  $item->getLabel(),
                    'count'         =>  $item->getCount(),
                    'value'   =>  $item->getValue()
                );
            }
        }
        return $itemsToJson;
    }

    /*
     * Get Item Value for an attribute
     * @param  : Zend_Controller_Request_Abstract $request,Mage_Catalog_Model_Resource_Eav_Attribute $attribute, int $storeId
     * @return : Array(
                    Array(
                        'label'   => string|array
                        'count'   => int
                        'item_id' => int
                    )
                )
     */
    public function getItems(Zend_Controller_Request_Abstract $request,$attribute){
        if ($attribute->getAttributeCode() == 'price') {
            $_filterModelName = 'apiios/api2_category_layer_filter_price';
        } elseif ($attribute->getBackendType() == 'decimal') {
            $_filterModelName = 'apiios/api2_category_layer_filter_decimal';
        } else {
            $_filterModelName = 'apiios/api2_category_layer_filter_attribute';
        }
        $_filterModel = Mage::getModel($_filterModelName)
             ->setStore($this->getStore())
             ->setLayer($this)
             ->setAttributeModel($attribute)
             ->applyios($request);
        $itemsToJson = array();
        $items = $_filterModel->getItems();
        if(is_array($items)){
            foreach ($items as $item){

                    $data = array(
                        'label'     =>  $item->getLabel(),
                        'value'   =>  $item->getValue()
                    );


                if($this->shouldDisplayProductCount($this->getStore()->getId()))
                    $data['count'] = $item->getCount();
                $itemsToJson[] = $data;
            }
        }
        return $itemsToJson;
    }

    public function getFilterableAttributesForJson(){
        return $this->_attributesFilterForJson;
    }

    public function getActiveFilterForJson(){
        return $this->_activeFilterForJson;
    }

    /**
     * Getter for $_displayProductCount
     * @param int $storeId
     * @return bool
     */
    public function shouldDisplayProductCount($storeId = null)
    {
        if ($this->_displayProductCount === null) {
            $this->_displayProductCount = Mage::helper('catalog')->shouldDisplayProductCountOnLayer($storeId);
        }
        return $this->_displayProductCount;
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    public function getActiveFilters()
    {
        $filters = $this->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }
        return $filters;
    }
}
?>
