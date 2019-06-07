<?php

class Hatimeria_OrderManager_Model_Period extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('hordermanager/period');
    }

    public function _prepareCollection()
    {

        $collection = Mage::getResourceModel('hordermanager/period_collection');
        $collection->addAttributeToSelect(array('period_id', 'date_from', 'date_to'));
        $collection->addAttributeToSelect('*');

        $collection->addFieldToFilter('date_from', 10);

        return $collection;
    }

    public function getCollectionFormatted($sortOrder, $direction)
    {
        $collection = Mage::getResourceModel('hordermanager/period_collection');
        $collection->setOrder($sortOrder, $direction);

        foreach ($collection as $period) {
            $dateFrom = new DateTime($period->getDateFrom());
            $dateFrom = $dateFrom->format('d-m-y H:i:s');
            $dateTo = new DateTime($period->getDateTo());
            $dateTo = $dateTo->format('d-m-y H:i:s');

            $period->setDateFrom($dateFrom);
            $period->setDateTo($dateTo);
        }
        return $collection;
    }


    public function initPeriods()
    {
        $helper = Mage::helper('hordermanager');
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('created_at', array('gt' => $helper->getConfig('newPeriodsBorder')))
            ->addFieldToFilter('status', array('neq' => 'canceled'))
            ->addFieldToFilter('status', array('neq' => 'closed'))
            ->addFieldToFilter('status', array('neq' => 'holded'))
            ->addFieldToFilter('state', array('neq' => 'pending'))
            ->addFieldToFilter('status', array('neq' => 'pending_payment'))
            ->addFieldToFilter('state', array('neq' => 'fraud'));

        foreach ($orders as $order) {
            try {
                Mage::getSingleton('hordermanager/observer')->applyPeriod($order);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('The Periods have not been initialized.'));
                Mage::log('Order Id: ' . $order->getId() . ': ' . $e->getMessage(), null, 'hordermanager_init_model_exception.log');
                return false;
            }
        }
    }

    /**
     * Truncate table hordermanager_period from admin panel Orders Manager
     *
     * @return mixed
     */
    public function clearPeriods()
    {
        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $periodsTable = $resource->getTableName('hordermanager_period');
        $periodHasOrdersTable = $resource->getTableName('hordermanager_period_has_order');
        $periodOrdersHasItems = $resource->getTableName('hordermanager_period_order_has_item');

        $resource->query("truncate table " . $periodsTable);
        $resource->query("truncate table " . $periodHasOrdersTable);
        $resource->query("truncate table " . $periodOrdersHasItems);

        return;
    }

    public function getPeriodTimeFrames($periodFlag)
    {
        $helper = Mage::helper('hordermanager');
        $period = new Varien_Object();

        if (0 == $periodFlag) {
            $period->setData(
                array(
                    'start_hour' => $helper->getConfig('beginTime', 'firstPeriod'),
                    'end_hour' => $helper->getConfig('endTime', 'firstPeriod'),
                    'start_day_number' => $helper->getConfig('beginDay', 'firstPeriod'),
                    'end_day_number' => $helper->getConfig('endDay', 'firstPeriod'),
                    'shipping_day' => Mage::getStoreConfig(sprintf('hordermanager/%s/%s', 'firstShipping', 'day')),
                    'period_sign' => Mage::getStoreConfig(sprintf('hordermanager/%s/%s', 'periods', 'firstPeriodSign'))
                )
            );
        } else {
            $period->setData(
                array(
                    'start_hour' => $helper->getConfig('beginTime', 'secondPeriod'),
                    'end_hour' => $helper->getConfig('endTime', 'secondPeriod'),
                    'start_day_number' => $helper->getConfig('beginDay', 'secondPeriod'),
                    'end_day_number' => $helper->getConfig('endDay', 'secondPeriod'),
                    'shipping_day' => Mage::getStoreConfig(sprintf('hordermanager/%s/%s', 'secondShipping', 'day')),
                    'period_sign' => Mage::getStoreConfig(sprintf('hordermanager/%s/%s', 'periods', 'secondPeriodSign'))
                )
            );
        }

        return $period;
    }
}