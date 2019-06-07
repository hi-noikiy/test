<?php
/**
 * Stock Status
 */

class Mish_StockOnList_Model_Resource_Stock_Status extends Mage_CatalogInventory_Model_Resource_Stock_Status
{
    /**
     * Add stock status to prepare index select
     *
     * @param Varien_Db_Select $select
     * @param Mage_Core_Model_Website $website
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function addStockStatusToSelect(Varien_Db_Select $select, Mage_Core_Model_Website $website)
    {
        $websiteId = $website->getId();
        $select->joinLeft(
            array(
                'stock_status' => $this->getMainTable()
            ),
            'e.entity_id = stock_status.product_id AND stock_status.website_id='.$websiteId,
            array(
                'salable' => 'stock_status.stock_status',
                'qty' => 'stock_status.qty'
            )
        );

        return $this;
    }
}