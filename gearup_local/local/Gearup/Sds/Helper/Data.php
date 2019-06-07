<?php

/**
 * Helper
 */
class Gearup_Sds_Helper_Data extends Mage_Core_Helper_Abstract {

    const SDS_CAT_ID = '799';
    const ROOT_CAT_ID = '3';
    const ORDER_STATUS_CANCEL = 'canceled';
    const ORDER_STATUS_CLOSE = 'closed';
    const ORDER_STATUS_CANCEL_COLOR = 'red';

    public function getSdsHorder($product, $periodId, $orderId) {
        if (!$product->getId()) {
            return false;
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('gearup_sds_horder');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = " . $product->getId() . " AND `order_id` = " . $orderId . " AND `sds` = 1;";
        $searchs = $readConnection->fetchAll($searchquery);
        if ($searchs) {
            return true;
        } else {
            return false;
        }
    }

    public function saveSdshorder($product, $periodId, $orderId) {
        if (!$product->getId()) {
            return false;
        }
        //-------- save sds for horder manager --------//
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('gearup_sds_horder');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = " . $product->getId() . " AND `order_id` = " . $orderId . ";";
        $searchs = $readConnection->fetchAll($searchquery);
        $sdsStatus = $product->getSameDayShipping();
        if (!$searchs) {
            $query = "INSERT INTO `{$table}` (`entity_id`, `product_id`, `period_id`, `order_id`, `sds`) VALUES (NULL, '" . $product->getId() . "', '" . $periodId . "', '" . $orderId . "', '" . $sdsStatus . "');";
            $writeConnection->query($query);

            return $sdsStatus;
        }

        return $sdsStatus;
    }

    public function assignSDS($productId) {
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        $cate = Mage::getSingleton('catalog/category_api');
        $cates = Mage::getModel('catalog/category')->getCollection();
        $cates->addAttributeToFilter('category_deal', 1);
        foreach ($cates as $catChild) {
            $cateModel = Mage::getModel('catalog/category')->load($catChild->getEntityId());
            if ($cateModel->getEntityId() == Gearup_Sds_Helper_Data::SDS_CAT_ID) {
                $cate->assignProduct($catChild->getId(), $productId);
            }
        }

        $this->assignLable($productId);
    }

    public function unassignSDS($productId) {
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        $cate = Mage::getSingleton('catalog/category_api');
        $cates = Mage::getModel('catalog/category')->getCollection();
        $cates->addAttributeToFilter('category_deal', 1);
        foreach ($cates as $catChild) {
            $cateModel = Mage::getModel('catalog/category')->load($catChild->getEntityId());
            if ($cateModel->getEntityId() == Gearup_Sds_Helper_Data::SDS_CAT_ID) {
                $cate->assignProduct($catChild->getId(), $productId);
                $cate->removeProduct($catChild->getId(), $productId);
            }
        }

        $this->unassignLable($productId);
    }

    public function assignLable($productId) {
        $labelModel = Mage::getModel('prolabels/index');
        $labelModels = $labelModel->getResource()->getLabelProductIds(4);
        if (!in_array($productId, $labelModels)) {
            $labelModel->setRulesId(4);
            $labelModel->setProductId($productId);
            $labelModel->save();
        }
    }

    public function unassignLable($productId) {
        $labelModel = Mage::getModel('prolabels/index');
        $labelModels = $labelModel->getResource()->getLabelProductIds(4);
        if (in_array($productId, $labelModels)) {
            $labelModel->load($productId, 'product_id')->delete();
        }
    }

    public function recordHistory($pid, $action, $prevoiusQty=NULL, $qty=NULL, $orderId=NULL, $previousSds=NULL, $preqty=NULL) 
    {
        $product = Mage::getModel('catalog/product')->load($pid);
        $history = Mage::getModel('gearup_sds/history');
        $admin = Mage::getSingleton('admin/session')->getUser();
        $sdsStatus = $product->getSameDayShipping();
        $sdsQty = null;
        $extQty = null;
        $productCost = null;
        $productCostValue = null;

        if($sdsStatus == 1 || $previousSds == 1){
            if($prevoiusQty > $qty){
                $inOut = round($qty - $prevoiusQty);
            } elseif ($prevoiusQty == $qty) {
                $inOut = '';
            } else {
                $inOut = ('+' . round($qty - $prevoiusQty));
            }
            $productCost = $product->getCost();
            if($productCost != '' || $productCost != null){
                $productCostValue = $productCost * $qty;
            }
 
            if($previousSds == 1){
                $sdsStatus = 1;
            }
            
            $sdsQty = $qty;
            $extQty = null;
            
            if($previousSds == 1 && $qty == 0){
                $productCostValue = $productCost * $preqty;
                $sdsStatus = 1;
                $sdsQty = $qty;
                $extQty = null;
                $inOut = round($qty - $preqty);
            }
        } else {
            $inOut = null;
            $productCost = null;
            $productCostValue = null;
            $sdsQty = null;
            $extQty = $qty;
        }
        
        $history->setProductId($product->getEntityId());
        $history->setSku($product->getSku());
        $history->setPartNumber($product->getPartNr());
        $history->setActions($action);
        $history->setQty($qty);
        $history->setSdsQty($sdsQty);
        $history->setExtQty($extQty);
        $history->setInOut($inOut);
        $history->setSdsStatus($sdsStatus);
        $history->setCreateDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $history->setOrderId($orderId);
        $history->setCost($productCost);
        $history->setCostValue($productCostValue);
        $history->setUser($admin ? $admin->getFirstname() : $this->__('Customer'));
        $history->save();

    }

    public function sdsStatus($sds) {
        if ($sds) {
            return $this->__('Yes');
        } else {
            return $this->__('No');
        }
    }

    public function deletedFile($path) {
        unlink($path);
    }

    public function checkFileType($type) {
        //$allowedTypes = array('text/csv');
        $allowedTypes = array(
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-office',
            'application/octet-stream'
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkExcelType($file) {
        $type = mime_content_type($file);
        $allowedTypes = array(
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-office',
            'application/octet-stream'
        );
        if (in_array($type, $allowedTypes)) {
            return true;
        } else {
            return false;
        }
    }

    public function getHorder($product, $orderId) {
        if (!$product->getId()) {
            return false;
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('gearup_sds_horder');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = " . $product->getId() . " AND `order_id` = " . $orderId . ";";
        $searchs = $readConnection->fetchAll($searchquery);
        return $searchs;
    }

    public function flagSdsAll($orderId, $f = 1) {
        if (!$orderId) {
            return false;
        }
        //-------- save sds for horder manager --------//
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('gearup_sds_horder_flag');
        $searchquery = "SELECT * FROM `{$table}` WHERE `order_id` = " . $orderId . ";";
        $searchs = $readConnection->fetchAll($searchquery);

        if (!$searchs) {
            $query = "INSERT INTO `{$table}` (`entity_id`, `order_id`, `all_sds`) VALUES (NULL, '" . $orderId . "', '{$f}');";
            $writeConnection->query($query);
        }
    }

    public function getSdsAll($orderId) {
        if (!$orderId) {
            return false;
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('gearup_sds_horder_flag');
        $table_coi = $resource->getTableName('configurator_order_item');
        $searchquery = "SELECT * FROM `{$table}` WHERE `order_id` = " . $orderId . " and all_sds = 1;";
        $searchs = $readConnection->fetchAll($searchquery);

        $searchs2 = $readConnection->fetchAll("SELECT * FROM `{$table_coi}` WHERE `order_id` = " . $orderId . " limit 1");

        if ($searchs) {
            if ($searchs2)
                return 'sdsall-blue';
            return 'sdsall';
        } else {
            if ($searchs2)
                return "sdsall-full-blue";
            return '';
        } /* echo '<pre />';
          $order = Mage::getModel('sales/order')->load(1269);
          foreach ($order->getAllItems() as $key => $_item) {
          print_r($_item->getData());
          } exit;
          $product = Mage::getModel('catalog/product')->load($_item->getProduct()?$_item->getProduct()->getId():$_item->getId());
          $period = Mage::getModel('hordermanager/order')->getCollection();
          $period->addFieldToFilter('order_id', $orderId);
          $periodF = $period->getFirstItem();
          if ($period->getSize()) {
          if(Mage::helper('gearup_sds')->getSdsHorder($product,$periodF->getPeriodId(),$orderId)) {
          $sds= 'green';
          }
          } */
    }

    public function getStatistic($ids) {
        $collection = $ids;
        $value = 0;
        $product = 0;
        $qty = 0;
        foreach ($collection as $pid) {
            $dxbs = Mage::getModel('catalog/product')->load($pid);
            if ($dxbs->getStockItem()->getQty() > 0 && $dxbs->getSameDayShipping()) {
                if ($dxbs->getSpecialPrice() > 0) {
                    $value = $value + ($dxbs->getSpecialPrice() * $dxbs->getStockItem()->getQty());
                } else {
                    $value = $value + ($dxbs->getPrice() * $dxbs->getStockItem()->getQty());
                }

                $product++;
                $qty = $qty + $dxbs->getStockItem()->getQty();
            }
        }

        return array('value' => $value, 'product' => $product, 'qty' => $qty);
    }

    public function changeSdsAll($order) {
        if (!$order) {
            return false;
        }

        $sdsAll = $this->getSdsAll($order->getId());
        if ($sdsAll) {
            $itemsCollection = Mage::getModel('hordermanager/order_item')->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());

            foreach ($itemsCollection as $item) {
                $item->setOrdered(1);
                $item->setSupplierNotes('Sklad DXB');
                $item->save();
            }
        }
    }

    public function changeCancel($order) {
        if (!$order) {
            return false;
        }

        $itemsCollection = Mage::getModel('hordermanager/order_item')->getCollection()
                ->addFieldToFilter('order_id', $order->getId());
        if ($itemsCollection->getSize()) {
            foreach ($itemsCollection as $item) {
                $item->setAdminNotes('Canceled, do not Order!');
                $item->save();
            }
        }
    }

    public function checkCancel($order) {
        if ($order->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $order->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
//            $collection = Mage::getModel('hordermanager/order_item')->getCollection();
//            $collection->addFieldToFilter('order_id', $order->getId());
//            $collection->addFieldToFilter('ordered', 1);
            $collection = Mage::getModel('sales/order_status_history')->getCollection();
            $collection->addFieldToFilter('parent_id', $order->getId());
            $collection->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PROCESSING);
            if (!$collection->getSize()) {
                return false;
            }
        }

        return true;
    }

    public function stockLabel($stock) {
        if ($stock) {
            return $this->__('In Stock');
        } else {
            return $this->__('Out Of Stock');
        }
    }

    public function getExcelData($file) {
        require_once Mage::getBaseDir('lib') . DS . 'PHPExcel.php';
        require_once Mage::getBaseDir('lib') . DS . 'PHPExcel/IOFactory.php';


        $inputFileName = $file;
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($inputFileName);


        // for No header
//        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
//        $highestRow = $objWorksheet->getHighestRow();
//        $highestColumn = $objWorksheet->getHighestColumn();
//
//        $r = -1;
//        $namedDataArray = array();
//        for ($row = 1; $row <= $highestRow; ++$row) {
//            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
//            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
//                ++$r;
//                $namedDataArray[$r] = $dataRow[$row];
//            }
//        }


        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();

        $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $headingsArray = $headingsArray[1];

        $r = -1;
        $namedDataArray = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                ++$r;
                foreach ($headingsArray as $columnKey => $columnHeading) {
                    $namedDataArray[$r][preg_replace('/\s+/', '', $columnHeading)] = $dataRow[$row][$columnKey];
                }
            }
        }
        return $namedDataArray;
    }

    public function changeSdsitem($item) {
        if (!$item) {
            return false;
        }

        $itemsCollection = Mage::getModel('hordermanager/order_item')->getCollection()
                ->addFieldToFilter('item_id', $item->getId());

        foreach ($itemsCollection as $item) {
            $item->setOrdered(1);
            $item->setSupplierNotes('Sklad DXB');
            $item->save();
        }
    }

    public function checkCancelCC($order) {
        if ($order->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $order->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
            $invoiceIds = $order->getInvoiceCollection()->getAllIds();
//            $collection = Mage::getModel('sales/order_status_history')->getCollection();
//            $collection->addFieldToFilter('parent_id', $order->getId());
//            $collection->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PROCESSING);
            if (!$invoiceIds) {
                return false;
            }
        }

        return true;
    }

    public function getExcelShipingData($file, $type = Gearup_Shippingffdx_Model_Destination::SHIP_DOSMATIC) {
        require_once Mage::getBaseDir('lib') . DS . 'PHPExcel.php';
        require_once Mage::getBaseDir('lib') . DS . 'PHPExcel/IOFactory.php';


        $inputFileName = $file;
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($inputFileName);


        // for No header
//        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
//        $highestRow = $objWorksheet->getHighestRow();
//        $highestColumn = $objWorksheet->getHighestColumn();
//
//        $r = -1;
//        $namedDataArray = array();
//        for ($row = 1; $row <= $highestRow; ++$row) {
//            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
//            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
//                ++$r;
//                $namedDataArray[$r] = $dataRow[$row];
//            }
//        }

        if ($type == Gearup_Shippingffdx_Model_Destination::SHIP_DOSMATIC) {
            $objWorksheet = $objPHPExcel->setActiveSheetIndexByName('Dom');
        } else if ($type == Gearup_Shippingffdx_Model_Destination::SHIP_INTER) {
            $objWorksheet = $objPHPExcel->setActiveSheetIndexByName('intl');
        }
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();

        $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $headingsArray = $headingsArray[1];

        $r = -1;
        $namedDataArray = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                ++$r;
                foreach ($headingsArray as $columnKey => $columnHeading) {
                    if ($columnHeading == 'Date') {
                        $namedDataArray[$r][preg_replace('/\s+/', '', $columnHeading)] = date($format = "Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($dataRow[$row][$columnKey]));
                    } else {
                        $namedDataArray[$r][preg_replace('/\s+/', '', $columnHeading)] = $dataRow[$row][$columnKey];
                    }
                }
            }
        }
        return $namedDataArray;
    }

    public function deliveredStatus($awb) {
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $collection->addFieldToFilter('tracking_number', array('eq' => $awb));
        $ffdx = $collection->getFirstItem();

        return $ffdx->getChecked();
    }

    public function deliveryDate($order) {
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $collection->addFieldToFilter('order_id', array('eq' => $order->getId()));
        $collection->setOrder('tracking_id', 'DESC');
        $ffdx = $collection->getFirstItem();

        $trackhistories = Mage::getModel('ffdxshippingbox/history')->getCollection();
        $trackhistories->addFieldTofilter('tracking_id', array('eq' => $ffdx->getTrackingId()));
        $trackhistories->addFieldToFilter('activity', array('eq' => 1));

        if ($trackhistories->getSize()) {
            $trackH = $trackhistories->getFirstItem();
            return date('d M Y H:i:s', strtotime($trackH->getCreatedAt()));
        } else {
            return '';
        }
    }

    public function diffItem($previous, $qty) {
        /* $diffQty = abs($previous - $qty);
          if ($previous > $qty) {
          return Mage::helper('gearup_sds')->__('Item Out: ') . round($diffQty);
          } else if ($previous <= $qty) {
          return Mage::helper('gearup_sds')->__('Item In: ') . round($diffQty);
          } */

        return '';
    }

    public function getLaststatus($orderId) {
        $collection = Mage::getModel('sales/order_status_history')->getCollection();
        $collection->addFieldToFilter('parent_id', $orderId);
        $collection->setOrder('created_at', 'DESC');
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
    }

    public function getUseradmin($userId) {
        $adminM = Mage::getModel('admin/user')->load($userId);
        return $adminM->getFirstname();
    }

    public function getUserComment($orderId) {
        $orderhis = Mage::getModel('sales/order_status_history')->getCollection();
        $orderhis->addFieldToFilter('parent_id', $orderId);
        $orderhis->addFieldToFilter('user', array('neq' => null));
        if ($orderhis->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    public function checkSdsProducts($categoryId) {
        if ($categoryId == self::SDS_CAT_ID) {
            return false;
        }

        $_category = Mage::getModel('catalog/layer')->getCurrentCategory();
        if($_category->getShowCategoryFilter()){
            return false;
        }
        
        $storeId = (int) Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        $appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        /*$filters = array();
        foreach ($appliedFilters as $filter) {
            $attributeModel = $filter->getFilter()->getAttributeModel();
            $filters[$attributeModel->getAttributeCode()] = $filter->getValue();
        }
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $from = Mage::getModel('directory/currency')->load($baseCurrencyCode);
        $to = Mage::getModel('directory/currency')->load($currentCurrencyCode);
        $rate = $from->getRate($to);*/
        $collection = Mage::getModel('catalog/category')->load($categoryId)->getProductCollection();
        $collection->addAttributeToSelect('*');
        /*if ($filters) {
            foreach ($filters as $key => $param) {
                if ($key != 'price') {
                    $collection->addFieldToFilter($key, array('eq' => $param));
                } else if ($key == 'price') {
                    if ($param[0] != '') {
                        $usd = round(($param[0] / $rate), 2);
                        $collection->addFieldToFilter('price', array('from' => $usd));
                    }

                    if ($param[1] != '') {
                        $usdTo = round(($param[1] / $rate), 2);
                        $collection->addFieldToFilter('price', array('to' => $usdTo));
                    }
                }
            }
        }*/
//        $params = Mage::app()->getRequest()->getParams();
//        if ($params) {
//            unset($params['id']);
//            unset($params['sds']);
//            foreach ($params as $key => $param) {
//                $collection->addFieldToFilter($key, array('eq'=>$param));
//            }
//        }
        $collection->addFieldToFilter('same_day_shipping', array('eq' => 1));
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load($storeId));
        if ($collection->getSize()) {
            return true;
        }

        return false;
//        $ids = Mage::getSingleton('core/session')->getSdshave();
//        $collection = Mage::getModel('catalog/product')->getCollection();
//        $collection->addFieldToFilter('entity_id', array('in'=>$ids));
//        $collection->addFieldToFilter('same_day_shipping', array('eq'=>1));
//        if ($collection->getSize()) {
//            return true;
//        }
//        Mage::getSingleton('core/session')->unsSdshave();
//        if (Mage::getSingleton('core/session')->getSdshave()) {
//            return true;
//        }
//        Mage::getSingleton('core/session')->unsSdshave();
//        return false;
    }

    public function changeRate($amount, $baseCurrencyCode = NULL, $currentCurrencyCode = NULL, $convert = NULL) {
        if (!$baseCurrencyCode) {
            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        }
        if (!$currentCurrencyCode) {
            $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }

        $from = Mage::getModel('directory/currency')->load($baseCurrencyCode);
        $to = Mage::getModel('directory/currency')->load($currentCurrencyCode);

        $rate = $from->getRate($to);
        if ($convert) {
            $conv = ($amount / $rate);
        } else {
            $conv = ($amount * $rate);
        }

        return $conv;
    }

    public function checkSdsCod($grandtotal) {
        $sdsAll = true;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->getSameDayShipping()) {
                $sdsAll = false;
            }
        }
        if ($sdsAll && $grandtotal > Mage::getStoreConfig('payment/cashondelivery/sdsmax_price_total')) {
            return array('status' => true, 'limit' => Mage::getStoreConfig('payment/cashondelivery/sdsmax_price_total'));
        } else if (!$sdsAll && $grandtotal > Mage::getStoreConfig('payment/cashondelivery/max_price_total')) {
            return array('status' => true, 'limit' => Mage::getStoreConfig('payment/cashondelivery/max_price_total'));
        } else {
            return array('status' => false, 'limit' => 0);
        }
    }

    public function getFeatureProducts() {
        $content = Mage::getModel('cms/block')->load('lastest_feature_product_gala_gear_box')->getContent();
        $feature = array();
        if ($content) {
            $content = preg_replace('/\r\n/', '', $content);
            preg_match('/skus="(.*)if/s', $content, $skulist);
            $skus = str_replace('"', '', $skulist[1]);
            $skus = explode(',', $skus);

            preg_match('/h3(.*)\<\/h3/', $content, $titleformat);
            $title = str_replace('>', '', $titleformat[1]);

            $feature['title'] = $title;
            $feature['skus'] = $skus;
        }

        return $feature;
    }

}
