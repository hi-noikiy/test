<?php
class EM_Apiios_Model_Api2_Reviews_Rest_Abstract extends Mage_Api2_Model_Resource
{
	const API_ADD_SUCCESS = 'API add to cart';

    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
		$result	=	array();
		if($this->getRequest()->getParam('p'))	$p	=	$this->getRequest()->getParam('p');
		else	$p	=	1;
		if($this->getRequest()->getParam('limit'))	$limit	=	$this->getRequest()->getParam('limit');
		else	$limit	=	10;
		$start	=	($p-1)*$limit;
		$end	=	$start+$limit;
		$id	=	$this->getRequest()->getParam('id');

		$revs	=	array();
		$reviews = Mage::getModel('review/review')
                ->getResourceCollection()
                ->addStoreFilter($this->_getStore()->getId())
                ->addEntityFilter('product', $id)
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
		
		if (count($reviews) > 0) {
			$i	=	0;
			foreach ($reviews->getItems() as $key=>$review) {
				if($i >= $start && $i < $end  ){
					$rev	=	array();
					$rev['review_id']	=	$review->getId();
					$rev['created_at']	=	$review->getData('created_at');
					$rev['status_id']	=	$review->getData('status_id');
					$rev['title']		=	$review->getData('title');
					$rev['detail']		=	$review->getData('detail');
					$rev['nickname']	=	$review->getData('nickname');
					$rev['customer_id']	=	$review->getData('customer_id');
					$rev['rating_vote']	=	array();
					foreach( $review->getRatingVotes() as $vote ) {
						$v['vote_id']		=	$vote->getId();
						$v['rating_code']	=	$vote->getData('rating_code');
						$v['percent']		=	$vote->getData('percent');
						$rev['rating_vote'][]	=	$v;
					}
					$revs[]	=	$rev;
				}
				$i++;
			}
		}
		
		$sumObject = Mage::getModel('review/review_summary')
            ->setStoreId($this->_getStore()->getId())
            ->load($id);
			
		$result['list_review']	=	$revs;
		$result['count_review']	=	$sumObject->getReviewsCount();
		$result['summary'] = $sumObject->getRatingSummary();
		return $result;
    }

}
?>