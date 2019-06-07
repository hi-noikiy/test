<?php

/**
 * Class Hatimeria_OrderManager_Model_Observer
 */
class Hatimeria_OrderManager_Model_Observer
{
    const SUNDAY = 0;

    protected $hourBeginPeriod = array();
    protected $hourEndPeriod = array();
    public $orderDay;
    protected $weekCreatedAt;
    protected $orderDayOfWeekNumber;
    protected $startDayOfFirstPeriodNumber;
    protected $endDayOfFirstPeriodNumber;
    public $hourCreatedAt;
    public $dateCreatedAt;
    public $edgeDayOfPeriodNumber;
    protected $endHourOfFirstPeriod;
    protected $createdAt;


    /**
     * @param Varien_Event_Observer $observer
     */
    public function insertPeriodHasOrders(Varien_Event_Observer $observer)
    {
        try {
            $this->applyPeriod($observer->getOrder());
        } catch (Exception $exc) {
            echo'<pre>';
            var_dump($exc);
            echo'</pre>';
            die();
        }


    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function applyPeriod(Mage_Sales_Model_Order $order)
    {
        /** @var $helper Hatimeria_OrderManager_Helper_Data */
        $helper = Mage::helper('hordermanager');

        $this->setCreatedAtTimeData($order->getCreatedAtStoreDate());

        $this->orderDay = $helper->getCurrentDate($this->dateCreatedAt);
        $orderTime = $helper->getCurrentTime($this->hourCreatedAt);

        $this->setOrderDayNumber($orderTime);

        $this->edgeDayOfPeriodNumber = $helper->getConfig('day');

        $newPeriod = $this->checkExistedPeriods();
        if (!$newPeriod) {
            $newPeriod = $this->checkPeriod();
            $currentPeriod = $this->initPeriod($newPeriod);
        } else {
            $currentPeriod = $newPeriod;
        }

        $this->initOrder($order, $currentPeriod);
        $this->initOrderItems($order, $currentPeriod);
    }

    /**
     * Set local variables containing time data of order
     * @param $createdAt
     */
    public function setCreatedAtTimeData($createdAt)
    {
        $this->createdAt = new DateTime($createdAt);
        $this->createdAt = $this->createdAt->format('Y-m-d H:i:s');

        $this->hourCreatedAt = new DateTime($createdAt);
        $this->hourCreatedAt = $this->hourCreatedAt->format('H:i:s');

        $this->dateCreatedAt = new DateTime($createdAt);
        $this->dateCreatedAt = $this->dateCreatedAt->format('Y-m-d');
    }

    /**
     * @param $periodEgdes
     */
    public function setWeekNumber($periodEgdes)
    {
        $weekNumber = new DateTime($periodEgdes['date_to']);
        $this->weekCreatedAt = $weekNumber->format('W');
    }

    /**
     * @param $orderTime
     */
    public function setOrderDayNumber($orderTime)
    {
        /**
         * @var $helper Hatimeria_OrderManager_Helper_Data
         */
        $helper = Mage::helper('hordermanager');

        if($orderTime < $this->hourCreatedAt) {
            $orderDay = new DateTime($this->dateCreatedAt);
            $orderDay->modify('+1 day');
            $orderDay = $orderDay->format('Y-m-d');
            $this->orderDayOfWeekNumber = $helper->getDayOfWeekNumber($orderDay);
            $this->orderDay = $orderDay;
        } else {
            $this->orderDayOfWeekNumber = $helper->getDayOfWeekNumber($this->dateCreatedAt);
        }
    }

    /**
     * @return bool
     */
    public function checkExistedPeriods()
    {
        $collection = Mage::getModel('hordermanager/period')->getCollection();

        $select = $collection->getSelect();
        $select->where("date_from < '{$this->createdAt}'")->where("date_to > '{$this->createdAt}'");

        if (0 == $collection->getSize()) {
            return false;
        }

        return $collection->getLastItem();
    }

    /**
     * @internal param $hourBeginPeriod
     * @internal param $hourEndPeriod
     * @internal param $this ->orderDayOfWeekNumber
     * @internal param $orderDay
     * @return array
     */
    public function checkPeriod()
    {
        $signOfPeriod = '';

        $firstPeriod = Mage::getModel('hordermanager/period')->getPeriodTimeFrames(0);
        $secondPeriod = Mage::getModel('hordermanager/period')->getPeriodTimeFrames(1);

        if ($this->orderDayOfWeekNumber == $this->edgeDayOfPeriodNumber) {
            if ($this->hourCreatedAt < $firstPeriod->getEndHour()) {
                $periodEdges = $this->getPeriodEdges($firstPeriod);
            } else {
                $periodEdges = $this->getPeriodEdges($secondPeriod);
            }
        } elseif ($this->orderDayOfWeekNumber == $firstPeriod->getStartDayNumber() || $this->orderDayOfWeekNumber == $firstPeriod->getEndDayNumber()) {
            if ($this->hourCreatedAt < $firstPeriod->getStartHour() || $this->hourCreatedAt < $secondPeriod->getEndHour()) {
                $periodEdges = $this->getPeriodEdges($secondPeriod);
            } else {
                $periodEdges = $this->getPeriodEdges($firstPeriod);
            }
        } elseif ($this->orderDayOfWeekNumber == $secondPeriod->getStartDayNumber() || $this->orderDayOfWeekNumber == $secondPeriod->getEndDayNumber()) {
            if ($this->hourCreatedAt < $firstPeriod->getStartHour() || $this->hourCreatedAt < $secondPeriod->getEndHour()) {
                $periodEdges = $this->getPeriodEdges($secondPeriod);
            } else {
                $periodEdges = $this->getPeriodEdges($firstPeriod);
            }
        } elseif ($this->orderDayOfWeekNumber < $firstPeriod->getEndDayNumber() && $this->orderDayOfWeekNumber > $firstPeriod->getStartDayNumber()) {
            $periodEdges = $this->getPeriodEdges($firstPeriod);
        } elseif ($this->orderDayOfWeekNumber > $secondPeriod->getEndDayNumber() && $this->orderDayOfWeekNumber < $secondPeriod->getStartDayNumber()) {
            $periodEdges = $this->getPeriodEdges($secondPeriod);
        } elseif ($this->orderDayOfWeekNumber > $secondPeriod->getStartDayNumber()) {
            $periodEdges = $this->getPeriodEdges($secondPeriod);
        } else {
            if ($this->hourCreatedAt < $firstPeriod->getStartHour() || $this->hourCreatedAt < $secondPeriod->getEndHour()) {
                $periodEdges = $this->getPeriodEdges($secondPeriod);
            } else {
                $periodEdges = $this->getPeriodEdges($firstPeriod);
            }
        }

        $shippingDate = Mage::helper('hordermanager')->getEstimatedShippingDate($periodEdges);

        $periodEdges['shipping_date'] = $shippingDate;

        $existedPeriodsCollection = Mage::getModel('hordermanager/period')->getCollection()
            ->addFieldToFilter('custom_period_id', $signOfPeriod);

        if ($existedPeriodsCollection->getSize() > 0) {
            $lastPeriod = $existedPeriodsCollection->getLastItem();
            $periodEdges['date_from'] = $lastPeriod->getDateFrom();
            $periodEdges['date_to'] = $lastPeriod->getDateTo();
            $periodEdges['period_sign'] = $lastPeriod->getCustomPeriodId();
        }

        return $periodEdges;
    }

    /**
     * @param $periodModel
     * @return mixed
     */
    public function getPeriodEdges($periodModel)
    {
        $helper = Mage::helper('hordermanager');
        $this->hourBeginPeriod = $helper->explodeHour($periodModel->getStartHour());
        $this->hourEndPeriod = $helper->explodeHour($periodModel->getEndHour());
        $periodEdges = $helper->modifyDateEdges($this->orderDay, $this->orderDayOfWeekNumber, $periodModel->getStartDayNumber(), $periodModel->getEndDayNumber());
        $periodEdges['date_from']->setTime($this->hourBeginPeriod['hours'], $this->hourBeginPeriod['minutes'], $this->hourBeginPeriod['seconds']);
        $periodEdges['date_to']->setTime($this->hourEndPeriod['hours'], $this->hourEndPeriod['minutes'], $this->hourEndPeriod['seconds']);
        $periodEdges['date_from'] = $periodEdges['date_from']->format('Y-m-d H:i:s');
        $periodEdges['date_to'] = $periodEdges['date_to']->format('Y-m-d H:i:s');

        $this->setWeekNumber($periodEdges);

        $periodEdges['period_sign'] = $this->weekCreatedAt . $periodModel->getPeriodSign();
        $periodEdges['shipping_day'] = $periodModel->getShippingDay();

        return $periodEdges;
    }

    /**
     * Return the current period
     * @param $newPeriod
     * @internal param $dateFrom
     * @internal param $dateTo
     * @return mixed
     */
    public function initPeriod($newPeriod)
    {
        // entity create:
        $collection = Mage::getModel('hordermanager/period')
            ->getCollection()
            ->addFieldToFilter('custom_period_id', $newPeriod['period_sign'])
            ->load();
        $collection->getSelect()->where("date_from < '{$this->createdAt}'")->where("date_to > '{$this->createdAt}'");

        if ($collection->getSize() > 0) {
            $currentPeriod = $collection->getFirstItem();
        } else {
            $currentPeriod = Mage::getModel('hordermanager/period')
                ->setCustomPeriodId(date('y', strtotime($newPeriod['date_from'])).'-'.$newPeriod['period_sign'])
                ->setDateFrom($newPeriod['date_from'])
                ->setDateTo($newPeriod['date_to'])
                ->setEstimatedShipping($newPeriod['shipping_date'])
                ->setShippingDay($newPeriod['shipping_day'])
                ->save();
        }

        return $currentPeriod;
    }

    /**
     * @param $order
     * @internal param $orderPeriod
     */
    public function initOrderItems($order, $currentPeriod=NULL)
    {
        $orderId = $order->getId();
        $items = $order->getAllItems();
        $sdsAll = 0;
        foreach ($items as $item) {
            $itemId = $item->getItemId();
            Mage::getModel('hordermanager/item')
                ->setItemId($itemId)
                ->setOrderId($orderId)
                ->save();
            if ($currentPeriod && ((Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder') || Mage::app()->getRequest()->getControllerName() == 'sales_order_edit' || Mage::app()->getRequest()->getControllerName() == 'sales_order_create'))
            {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $previousSds = $product->getSameDayShipping();
                if (Mage::app()->getRequest()->getControllerName() == 'sales_order_edit') {
                    $orderExplode = explode('-', $order->getIncrementId());
                    if ($orderExplode[1]) {
                        $orderInPre = (int)$orderExplode[1] - 1;
                        if ($orderInPre > 0) {
                            $orderInPre = $orderExplode[0].'-'.$orderInPre;
                        } else {
                            $orderInPre = $orderExplode[0];
                        }
                    }
                    $orderPre = Mage::getModel('sales/order')->loadByIncrementId($orderInPre);
                    $horderSDS = Mage::helper('gearup_sds')->getHorder($product, $orderPre->getId());
                    $product->setSameDayShipping($horderSDS[0]['sds']);
                    $saveSDS = Mage::helper('gearup_sds')->saveSdshorder($product,$currentPeriod->getId(),$orderId);
                    if ($saveSDS) {
                        $sdsAll++;
                    }
                    $previousSds = $horderSDS[0]['sds'];
                } else {
                    $saveSDS = Mage::helper('gearup_sds')->saveSdshorder($product,$currentPeriod->getId(),$orderId);
                    if ($saveSDS) {
                        $sdsAll++;
                    }
                }
                if ($product->getDxbs()) {
                    if ((Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder')) {
                        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                    }
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
                    $previousQty = $stockItem->getData('qty') + $item->getData('qty_ordered');
                    if ($stockItem->getData('is_in_stock')) {
                        //$product->setSameDayShipping(1);
                    } else {
                        $product->setSameDayShipping(0);
                        $product->setSpecialPrice(null);
                    }
                    $product->save();
                    $sdsStatus = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
                    $stockStatus = Mage::helper('gearup_sds')->stockLabel($stockItem->getData('is_in_stock'));

                    if ($previousSds) {
                        $action = '"'.$previousQty.' In Stock; SDS is Yes", '.$item->getData('qty_ordered').' pcs sold and order number is '.$order->getData('increment_id').' and marked green on this item, "'.round($stockItem->getData('qty')).' '.$stockStatus.'; SDS is '.$sdsStatus.'" ';
                    } else {
                        $action = '"'.$previousQty.' In Stock; SDS is No", '.$item->getData('qty_ordered').' pcs sold and order number is '.$order->getData('increment_id').', "'.round($stockItem->getData('qty')).' '.$stockStatus.'; SDS is '.$sdsStatus.'" ';
                    }

                    Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $previousQty, round($stockItem->getData('qty')), $order->getData('increment_id'), $previousSds, $previousQty);
                }
                unset($product);

            }
        }

        if (count($items) == $sdsAll) {
            Mage::helper('gearup_sds')->flagSdsAll($orderId);
        }
    }

    /**
     * Check if order already exists in table hordermanager_period_has_order
     * @param $currentPeriod
     * @param $order
     */
    public function initOrder($order, $currentPeriod)
    {
        $orderId = $order->getId();
        $currentPeriodId = $currentPeriod->getId();

        $orderPeriod = Mage::getModel('hordermanager/period_order')
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->load();

        $item = $orderPeriod->getFirstItem();
        $itemId = $item->getId();

        if (empty($itemId)) {
            Mage::getModel('hordermanager/period_order')
                ->setPeriodId($currentPeriodId)
                ->setOrderId($orderId)
                ->save();
        }
    }

    /**
     * rewrite table hordermanager_period after save changes in config System>Configuration>OrdersManager and after fire PeriodController::refreshAction()
     */
    public function regeneratePeriodsOrders()
    {
        $helper = Mage::helper('hordermanager');
        $periodModel = Mage::getModel('hordermanager/period');

        try{
            $periodModel->clearPeriods();
            $periodModel->initPeriods();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('The Periods have not been initialized.'));
            Mage::log('Regenerate Periods : ' . $e->getMessage(), null, 'hordersmanager_init_model_exception.log');
            return false;
        }
    }

    /**
     * update period edge dates by id
     */
    public function updatePeriods()
    {
        /* @var $helper Hatimeria_OrderManager_Helper_Data */
        $helper = Mage::helper('hordermanager');

        $newStartHourOfFirstPeriod = $helper->getConfig('beginTime', 'firstPeriod');
        $newStartDayOfFirstPeriodNumber = $helper->getConfig('beginDay', 'firstPeriod');
        $newEndHourOfFirstPeriod = $helper->getConfig('endTime', 'firstPeriod');
        $newEndDayOfFirstPeriodNumber = $helper->getConfig('endDay', 'firstPeriod');

        $newStartHourOfSecondPeriod = $helper->getConfig('beginTime', 'secondPeriod');
        $newStartDayOfSecondPeriodNumber = $helper->getConfig('beginDay', 'secondPeriod');
        $newEndHourOfSecondPeriod = $helper->getConfig('endTime', 'secondPeriod');
        $newEndDayOfSecondPeriodNumber = $helper->getConfig('endDay', 'secondPeriod');

        $periodsCollection = Mage::getModel('hordermanager/period')->getCollection();

        foreach ($periodsCollection as $period) {
            $periodId = $period->getPeriodId();

            if ($periodId % 2 == 0) {
                $periodEdges = $this->getNewPeriodEdges($period, $newStartHourOfSecondPeriod, $newEndHourOfSecondPeriod, $newStartDayOfSecondPeriodNumber, $newEndDayOfSecondPeriodNumber);
            } else {
                $periodEdges = $this->getNewPeriodEdges($period, $newStartHourOfFirstPeriod, $newEndHourOfFirstPeriod, $newStartDayOfFirstPeriodNumber, $newEndDayOfFirstPeriodNumber);
            }

            Mage::getModel('hordermanager/period')
                ->load($periodId)
                ->setDateFrom($periodEdges['new_date_from'])
                ->setDateTo($periodEdges['new_date_to'])
                ->save();
        }

        try {
            $this->regeneratePeriodsOrders();
        } catch (Exception $e) {
            Mage::log('ERROR DURING REGENERATION: ' . $e->getMessage(), null, 'update_periods.log');
        }
    }

    /**
     * @param $period
     * @param $newStartHourOfPeriod
     * @param $newEndHourOfPeriod
     * @param $newStartDayOfPeriodNumber
     * @param $newEndDayOfPeriodNumber
     * @return array
     */
    public function getNewPeriodEdges($period, $newStartHourOfPeriod, $newEndHourOfPeriod, $newStartDayOfPeriodNumber, $newEndDayOfPeriodNumber)
    {
        /* @var $helper Hatimeria_OrderManager_Helper_Data */
        $helper = Mage::helper('hordermanager');
        $periodId = $period->getPeriodId();

        $oldTimeFrom = $period->getDateFrom();
        $oldTimeTo = $period->getDateTo();

        $oldDateFrom = strstr($oldTimeFrom, ' ', true);
        $oldDateTo = strstr($oldTimeTo, ' ', true);

        $newDateFrom = $helper->modifyDate($oldDateFrom, $newStartDayOfPeriodNumber, $periodId, $dateFromFlag = 1);
        $newDateTo = $helper->modifyDate($oldDateTo, $newEndDayOfPeriodNumber, $periodId);

        $newDateFrom = $newDateFrom . ' ' . $newStartHourOfPeriod;
        $newDateTo = $newDateTo . ' ' . $newEndHourOfPeriod;

        return array('new_date_from' => $newDateFrom, 'new_date_to' => $newDateTo);
    }
}