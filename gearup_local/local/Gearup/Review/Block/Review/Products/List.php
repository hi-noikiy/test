<?php

/**
* 
*/
class Gearup_Review_Block_Review_Products_List extends MageWorkshop_DetailedReview_Block_Review_Products_List
{
	
	/**
     * @return Order increment id
     */
    public function getOrderIncrementId()
    {
        $orderId = (string) Mage::app()->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order')->load($orderId);
        return $order->getIncrementId();
    }

    /**
     * @return Order date
     */
    public function getOrderDate()
    {
        $orderId = (string) Mage::app()->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order')->load($orderId);
        return $order->getCreatedAtStoreDate();
    }

    /**
     * @param $product
     * @return Price Html
     */
    public function getPriceHtml($product)
    {
        $this->setTemplate('catalog/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

    /**
     * @param $productId
     * @return Average rating
     */
    public function getAverageRating($productId)
    {

        $reviews = Mage::getModel('review/review')->getResourceCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addEntityFilter('product', $productId)
                //->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
                foreach ($reviews->getItems() as $review) {
                    foreach($review->getRatingVotes() as $vote ) {
                         $ratings[] = $vote->getPercent();
                    }
                }

                $averageRating = array_sum($ratings)/count($ratings);

        return $averageRating;            
    }

    /**
     * @param $productId
     * @return Review count
     */
    public function getCount($productId)
    {
    	
        $reviews = Mage::getModel('review/review')->getResourceCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addEntityFilter('product', $productId)
                //->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
                
        return count($reviews);            
    }
}