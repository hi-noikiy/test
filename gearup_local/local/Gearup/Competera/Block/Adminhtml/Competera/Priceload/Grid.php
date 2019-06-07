<?php

/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Block_Adminhtml_Competera_Priceload_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $priceArr;
    protected $priceStr;
    
    public function __construct() {
        parent::__construct();
        $this->setId('competeraPriceloadGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->priceArr = Mage::helper('competera')->getCompeteraPrice();
        $this->priceStr = array_map(array($this, 'cvrstr'), array_keys($this->priceArr ));
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
     
    protected function _getCompeterGridCollection($priceStr){
         $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('manufacturer')                
                ->addAttributeToSelect('part_nr')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('min_price_custom')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('attribute_set_id')
                ->addFieldToFilter('sku', array('in' => $priceStr));
    
        $competeraCustomprice = Mage::getSingleton('core/resource')->getTableName('competera/customprice');
        $collection->getSelect()->joinLeft(array('customprice' => $competeraCustomprice), 'e.entity_id = customprice.entity_id', array('custom_price'));        
        return $collection;
    }

    protected function _prepareCollection() {       
        $collection = $this->_getCompeterGridCollection($this->priceStr);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function cvrstr($value) {
        return (string) $value;
    }

    protected function _prepareColumns() {

        $baseUrl = $this->getUrl();
        $store = $this->_getStore();
        $collection = $this->_getCompeterGridCollection($this->priceStr);

        $this->addColumn('name', array(
            'header' => Mage::helper('competera')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'manufacturer');
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
        ->setAttributeFilter($attribute->getId())
        ->setStoreFilter(0, false);        
       
       foreach($collection as $index){
          $manufaturers[$index->getData('manufacturer')]  = $index->getData('manufacturer');
          if(in_array($index->getData('attribute_set_id'),  array_keys($sets)))
            $collectionSets[$index->getData('attribute_set_id')] =$sets[$index->getData('attribute_set_id')];
       }
        $manufacturer_items = Mage::getModel('eav/entity_attribute_option')->getCollection()->setStoreFilter()->join('attribute','attribute.attribute_id=main_table.attribute_id', 'attribute_code');
        foreach ($valuesCollection as $manufacturer_item) :
            if(in_array($manufacturer_item->getOptionId(), $manufaturers))
                $manufacturer_options[ $manufacturer_item->getOptionId()] = $manufacturer_item->getValue();
        endforeach;
                
        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('catalog')->__('Manufacturer'),
            'width' => '100px',
            'index' => 'manufacturer',
            'type' => 'options',
            'options' => $manufacturer_options,
        ));        

        /*$this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $collectionSets,
        ));*/

        $this->addColumn('category', array(
            'header' => Mage::helper('catalog')->__('Category'),
            'width' => '100px',
            'index' => 'category',
            'sortable'  => false,
            'type' => 'options',
            'renderer' => 'Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_Category',
            'options'  => Mage::getSingleton('competera/system_config_source_category')->toOptionArray(),
            'filter_condition_callback' => array($this, 'filterCallback')
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $this->addColumn('part_nr', array(
            'header' => Mage::helper('catalog')->__('Part Number'),
            'width' => '80px',
            'index' => 'part_nr',
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('competera')->__('Price'),
            'type' => 'price',
            'width' => '80px',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
        ));

      
        $this->addColumn('special_price', array(
            'header' => Mage::helper('competera')->__('Special Price'),
            'type' => 'price',
            'width' => '80px',            
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'special_price',
        ));
         $this->addColumn('min_price_custom', array(
            'header' => Mage::helper('competera')->__('Minimal Price'),
            'type' => 'price',
            'width' => '80px',             
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'min_price_custom',
        ));


        $this->addColumn('newprice', array(
            'header' => Mage::helper('competera')->__('New Price'),
            'align' => 'left',
            'type' => 'price',
            'width' => '80px',            
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'newprice',
            'renderer' => 'Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_NewPrice',
            'filter' => false,
            'sortable' => false,
        ));
        $this->addColumn('custom_price', array(
            'header' => Mage::helper('competera')->__('Custom Price'),
            'align' => 'left',
            'type' => 'price',
            'width' => '80px',            
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'custom_price',
            'renderer' => 'Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_CustomPrice',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('pricediff', array(
            'header' => Mage::helper('competera')->__('Comparison'),
            'align' => 'right',
            'index' => 'pricediff',
            'width' => '100px',
            'filter' => false,
            'sortable' => false,
        ));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('entity_id', array(
            'label' => Mage::helper('competera')->__('Update Price'),
            'url' => $this->getUrl('*/*/massUpdate', array('' => ''))
        ));
        return $this;
    }

    protected function _afterLoadCollection() {
        $priceArr = Mage::helper('competera')->getCompeteraPrice();
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        foreach ($this->_collection as $item) {
            $productCustomPrice = Mage::getModel('competera/customprice')->load($item->getId());

            $item->setNewprice($priceArr[$item->getSku()] / $rates[$currentCurrencyCode]);
            $priceNew = ($productCustomPrice->getCustomPrice()>0)?$productCustomPrice->getCustomPrice():$priceArr[$item->getSku()] / $rates[$currentCurrencyCode];            
            $curPrice = ($item->getSpecialPrice() != '' ? $item->getSpecialPrice() : $item->getPrice());
            $suggestedPrice = $priceNew;
            if ($curPrice > $suggestedPrice) {
//                $count2 = 0;
//                $count1 = $suggestedPrice / $curPrice;
//                if ($count1 != 1)
//                    $count2 = $count1 * 100;
//                $count = number_format($count2, 2);
//                $count = 100 - $count;
//                $count = number_format($count, 2);
                $count = number_format( (1-$suggestedPrice/$curPrice)*100,2);                
                $item->setPricediff('-' . $count . '%');
            } else {
//                $count2 = 0;
//                $count1 = $curPrice / $suggestedPrice;
//                if ($count1 != 1)
//                    $count2 = $count1 * 100;
//                $count = number_format($count2, 2);
//                $count = 100 - $count;
//                $count = number_format($count, 2);
                $count =  number_format( (1-$curPrice/$suggestedPrice)*100,2);         
                $item->setPricediff($count . '%');
            }
        }
        return $this;
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $_category = Mage::getModel('catalog/category')->load($value);
        $collection->addCategoryFilter($_category);
        return $collection;
    }

    public function getGridUrl()
	{
	  return $this->getUrl('*/*/grid', array('_current'=>true));
	}

}
