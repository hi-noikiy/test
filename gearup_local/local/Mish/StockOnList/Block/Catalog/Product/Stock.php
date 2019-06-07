<?php
/**
 * Block Availability
 */

class Mish_StockOnList_Block_Catalog_Product_Stock extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('stockonlist/catalog/product/stock.phtml');
    }

    /**
     * Qty
     * @return int
     */
    public function getQty()
    {
        $qty = $this->getProduct()->getQty();
        if (is_null($qty)) {
            $qty = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($this->getProduct())->getQty();
        }

        return $qty;
    }

    /**
     * Is Available?
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->getData('is_available');
    }

    /**
     * Estimation date
     * @return DateTime
     */
    public function getAvailabilityEstimationDate()
    {
        if ($this->getProduct()->hasAvailEstimation() && ($date = $this->getProduct()->getAvailEstimation())) {
            return new DateTime($date);
        }
        else {
            return false;
        }
    }

    /**
     * Date Diff
     */
    public function getDateDiff()
    {
        if (($date = $this->getAvailabilityEstimationDate())) {
            $now = new DateTime('now');
            $diff = $now->diff($date);

            return (int) $diff->format('%a');
        }
        else {
            return false;
        }
    }
}