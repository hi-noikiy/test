<?php

class MindMagnet_Review_Block_Product_View_List extends Mage_Review_Block_Product_View_List
{
    /**
     *
     * @param $productId int
     * @return $collection
     */
    public function getFilteredReviewCollection($productId)
    {
        $collection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->load();

        return $collection->addEntitySummaryToItem($productId, Mage::app()->getStore()->getId());
    }
}