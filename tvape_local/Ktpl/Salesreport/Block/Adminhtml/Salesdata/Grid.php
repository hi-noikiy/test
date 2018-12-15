<?php

class Ktpl_Salesreport_Block_Adminhtml_Salesdata_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('salesdata');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function _prepareCollection() {
        $collection = $this->getitems();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getitems() {
        $session = Mage::getSingleton('core/session');
        $data = $session->getMyCustomData();
        $from_date = Mage::getModel('core/date')->gmtDate(null, strtotime($data['start_date'] . ' 00:00:00'));
        $to_date = Mage::getModel('core/date')->gmtDate(null, strtotime($data['end_date'] . ' 23:59:59'));
        
        $collect = Mage::helper('salesreport')->getcollect();
        $collect->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collect->getSelect()->columns(array('SUM(qty_ordered) as order_quantity', 'sku','item_id', 'product_id', 'name', 'price', 'SUM(qty_ordered) * price as revenue'))
                ->where(sprintf('salesord.created_at > "%s" AND salesord.created_at < "%s"', date('Y-m-d H:i:s', strtotime($from_date)), date('Y-m-d H:i:s', strtotime($to_date))))
                ->group('sku');
        
        return $collect;
    }

    protected function _prepareColumns() {
        $this->addColumn('sku', array(
            'header' => $this->__('Sku'),
            'index' => 'sku',
            'type' => 'text',
           )
        );

        $this->addColumn('name', array(
            'header' => $this->__('Product Name'),
            'index' => 'name',
            'type' => 'text',
           )
        );

        $this->addColumn('order_quantity', array(
            'header' => $this->__('Quantity'),
            'index' => 'order_quantity',
            'type' => 'number',
            'default' => '0',
            //'filter' =>FALSE,
            'filter_condition_callback' => array($this, 'filter_orderqty'),
           )
        );
        
        $this->addColumn('price', array(
            'header' => $this->__('Price'),
            'index' => 'price',
            'type' => 'number',
           )
        );

        $this->addColumn('revenue', array(
            'header' => $this->__('Revenue'),
            'index' => 'revenue',
            'type' => 'number',
            'filter' =>FALSE,
            //'filter_condition_callback' => array($this, 'filter_revenue'),
            'default' => '0',
           )
        );

        $this->addColumn('product_id', array(
            'header' => $this->__('Inclusion Rate'),
            'index' => 'product_id',
            'type' => 'number',
            'filter' => FALSE,
            'sortable' => FALSE,
            'renderer'  => 'salesreport/adminhtml_salesdata_renderer_inclusion',
            'default' => '0',
           )
        );
        
        $this->addColumn('item_id', array(
            'header' => $this->__('Position'),
            'index' => 'item_id',
            'type' => 'number',
            'filter' =>FALSE,
            'sortable' => FALSE,
            'renderer'  => 'salesreport/adminhtml_salesdata_renderer_position',
            'default' => '0',
          //  'filter_condition_callback' => array($this, '_customShippingFilterCallBack')
           )
        );

        $this->addExportType('*/*/exportOrderedCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportOrderedXml', $this->__('Excel'));

        return $this;
    }
    
    
//    protected function _afterLoadCollection() {
//        foreach ($this->getCollection() as $item) {
//            $tqty += $item->getData('order_quantity');
//            //echo '<br />'.$tqty1 = $item->getData('order_quantity');
//        }
//        $arr = array();
//        $j=0;
//        foreach ($this->getCollection() as $item) {
//            $qty = $item->getData('order_quantity');
//            $a = number_format(($qty * 100) / $tqty, 2);
//            $item->setInclusion($a);
//
//            $arr[$j] = $item;
//            $j++;
//        }
//
//        /*$collection = new Varien_Data_Collection();
//        for ($i = 0; $i < count($arr); $i++) {
//          // echo '<pre />'; print_r($arr[$i]);exit;
//            $collection->addItem($arr[$i]);
//        }
//        $this->setCollection($collection); */
//
//          usort($this->getCollection()->getIterator(), array('Ktpl_Salesreport_Block_Adminhtml_Salesdata_Grid', '_cmpAscInclusionRate'));
//        return $this;
//    }

    
    public function filter_orderqty($collection, $column){
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        if($value['from']){
            $this->getCollection()->getSelect()->having("SUM(qty_ordered) >= ?", $value['from']);
        }
        if($value['to']){
            $this->getCollection()->getSelect()->having("SUM(qty_ordered) <= ?",$value['to']);
        }
        
        
        return $this;
    }
    public function filter_revenue($collection, $column){
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        $this->getCollection()->getSelect()->having("SUM(qty_ordered) * main_table.price >= ?", $value['from'])
                ->having(" SUM(qty_ordered) * main_table.price <= ?",$value['to']);
        //echo $this->getCollection()->getSelect();exit;
        return $this;
    }
//    protected function _setCollectionOrder($column) {
//        $collection = $this->getCollection();
//        if ($collection) {
//
//            foreach ($collection as $item) {
//                $tqty += $item->getData('order_quantity');
//            }
//
//            switch ($column->getId()) {
//                case 'product_id':
//
//                    $arr = array();
//                    foreach ($collection as $item) {
//                        $qty = $item->getData('order_quantity');
//                        $a = number_format(($qty * 100) / $tqty, 2);
//                        $item->setProductId($a);
//                        $arr[$i] = $item;
//                        $i++;
//                    }
//                    if ($column->getDir() == 'asc') {
//                        $sorted = usort($arr, array($this, '_cmpAscInclusionRate'));
//                    } else {
//                        $sorted = usort($arr, array($this, '_cmpDescInclusionRate'));
//                    }
//
//                    $collection = new Varien_Data_Collection();
//                    for ($i = 0; $i < count($arr); $i++) {
//                        $collection->addItem($arr[$i]);
//                    }
//                    $this->setCollection($collection);
//                    break;
//                default:
//                    parent::_setCollectionOrder($column);
//                    break;
//            }
//        }
//        return $this;
//    }

    public function filter_inclusion($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        print_r($value); exit;
        $this->getCollection()->getSelect()->where(
        "inclusion >= ? AND inclusion <= ?"
        , $value['from'],$value['to']);

        return $this;

    }
//    protected function _customShippingFilterCallBack($collection, $column) {
//        $filter = $column->getFilter()->getValue();
//        $filterData = $this->getFilterData();
//        $arr = array();
//
//        foreach ($collection as $item) {
//
//            //$arr[$i] = $item;
//            //$i++;
//            $fieldValue = $item->getData('product_id');
//            ;
//            $pass = TRUE;
//            if (isset($filter['from']) && $filter['from'] >= 0) {
//                if (floatval($fieldValue) < floatval($filter['from'])) {
//                    $pass = FALSE;
//                }
//            }
//            if ($pass) {
//                if (isset($filter['to']) && $filter['to'] >= 0) {
//                    if (floatval($fieldValue) > floatval($filter['to'])) {
//                        $pass = FALSE;
//                    }
//                }
//            }
//            if ($pass) {
//                $item->setData('product_id', $fieldValue);
//                $arr[] = $item;
//            }
//        }
//        $temp = new Varien_Data_Collection(); // A blank collection 
//        for ($i = 0; $i < count($arr); $i++) {
//            $temp->addItem($arr[$i]);
//        }
//        $this->setCollection($temp);
//    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

//    protected function _cmpAscInclusionRate($a, $b) {
//        return $a->getInclusion() > $b->getInclusion();
//    }
//
//    protected function _cmpDescInclusionRate($a, $b) {
//        return $a->getProductId() < $b->getProductId();
//    }

}
