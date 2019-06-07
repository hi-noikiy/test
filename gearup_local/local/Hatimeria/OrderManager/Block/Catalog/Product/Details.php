<?php

/**
 * Class Hatimeria_OrderManager_Block_Catalog_Product_Details
 */
class Hatimeria_OrderManager_Block_Catalog_Product_Details extends Mage_Catalog_Block_Product_Abstract
{
    const PERIOD_NOT_FOUND = '';

    /**
     * @internal param $productIsSpecial
     * @return mixed
     */
    public function getPopUpText()
    {
        $result = '';

        /** @var $helper Hatimeria_OrderManager_Helper_Data */
        $helper = Mage::helper('hordermanager');

        $productIsSpecial = $this->getRequest()->getParam('is_special');
        $dayOfFirstShipping = $helper->getConfig('day', 'firstShipping');
        $dayOfSecondShipping = $helper->getConfig('day', 'secondShipping');

        if ($productIsSpecial) {
            $result = $helper->getConfig('sameDayShippingText', 'popUpText');
        } else {
            $appropriateDayOfShipping = $helper->getDayOfShipping('', $productIsSpecial);
            if ($appropriateDayOfShipping != self::PERIOD_NOT_FOUND) {
                $appropriateDayOfShippingNumber = $helper->getDayOfWeekNumber(strstr($appropriateDayOfShipping, ' ', true));
                if ($dayOfFirstShipping == $appropriateDayOfShippingNumber) {
                    $result = $helper->getConfig('firstShippingText', 'popUpText');
                } elseif ($dayOfSecondShipping == $appropriateDayOfShippingNumber) {
                    $result = $helper->getConfig('secondShippingText', 'popUpText');
                }
            } else {
                $result = 'Period Not Found';
            }
        }

        return $result;
    }

    /**
     * @return DateTime|string
     */
    public function getDayOfWeekFullName()
    {
        $toDay = Mage::helper('hordermanager')->getConfig('date', 'testDate');

        if (!$toDay) {
            $toDay = new DateTime();
            $toDay = $toDay->format('l d.m.Y');
        } else {
            $toDay = new DateTime($toDay);
            $toDay = $toDay->format('l d.m.Y');
        }

        return $toDay;
    }

    /**
     * @param $productIsSpecial
     * @return mixed
     */
    public function getProductShippingDay($productIsSpecial)
    {
        return Mage::helper('hordermanager')->getDayOfShipping('', $productIsSpecial);
    }

    /**
     * To get time delivery time.
     * @param $currentTime
     */
    public function getDelivaryTimeText($currentTime = null){
        if(is_null($currentTime))
            $currentTime = time();
                    
        $time = date("H",$currentTime);                 
        if ($time < "12")
            return "morning";
        else if ($time >= "12" && $time < "17") 
            return "afternoon";
        else if ($time >= "17" && $time < "19") 
            return "Next Day morning";        
    }
}