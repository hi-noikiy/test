<?php

/**
 * Class Hatimeria_OrderManager_Block_Catalog_Product_Shipping
 */
class Hatimeria_OrderManager_Block_Catalog_Product_Shipping extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * @return mixed
     */
    public function getDayOfWeekNumber()
    {
        return Mage::helper('hordermanager')->getDayOfWeekNumber();
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return Mage::helper('hordermanager')->getTime();
    }

    /**
     * @param null $dayFlag
     * @return mixed
     */
    public function checkDayOfShipping($dayFlag = null)
    {
        return Mage::helper('hordermanager')->checkDayOfShipping($dayFlag);
    }

    /**
     * @return mixed
     */
    public function getSameDay()
    {
        return Mage::helper('hordermanager')->getSameDay();
    }

    /**
     * @param $productIsSpecial
     * @return mixed
     */
    public function getDayOfShipping($productIsSpecial)
    {
        return Mage::helper('hordermanager')->getDayOfShipping('', $productIsSpecial);
    }
}