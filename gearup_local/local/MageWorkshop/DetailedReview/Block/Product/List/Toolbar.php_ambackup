<?php

/**
 * Product list toolbar
 *
 */
class MageWorkshop_DetailedReview_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * @var array
     */
    protected $_detailedReviewOrderField = array('popularity_by_sells', 'popularity_by_reviews', 'popularity_by_rating');
    
    /**
     * Retrieve Pager URL
     *
     * @param string $order
     * @param string $direction
     * @return string
     */
    public function getOrderUrl($order, $direction)
    {
        if (!Mage::helper('detailedreview/config')->isDetailedReviewEnabled()
            || is_null($order)
            || !in_array($order, $this->_detailedReviewOrderField, true))
        {
            return parent::getOrderUrl($order, $direction);
        }
        
        return $this->getPagerUrl(array(
            $this->getOrderVarName()        => $order,
            $this->getDirectionVarName()    => 'desc',
            $this->getPageVarName()         => null
        ));
    }
}
