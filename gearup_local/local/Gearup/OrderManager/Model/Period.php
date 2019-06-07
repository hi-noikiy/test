<?php

class Gearup_OrderManager_Model_Period extends Hatimeria_OrderManager_Model_Period
{

    public function getCollectionFormattedGrid($sortOrder, $direction)
    {
        //$limit = Mage::app()->getRequest()->getParam('limit') ? Mage::app()->getRequest()->getParam('limit') : 50;
        $collection = Mage::getResourceModel('hordermanager/period_collection');
        $collection->setOrder($sortOrder, $direction);
        //$collection->getSelect()->limit($limit);

        /*foreach ($collection as $period) {
            $dateFrom = new DateTime($period->getDateFrom());
            $dateFrom = $dateFrom->format('d-m-y H:i:s');
            $dateTo = new DateTime($period->getDateTo());
            $dateTo = $dateTo->format('d-m-y H:i:s');

            $period->setDateFrom($dateFrom);
            $period->setDateTo($dateTo);
        }*/
        return $collection;
    }

}