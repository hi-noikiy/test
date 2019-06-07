<?php

class Gearup_Blog_RateController extends Mage_Core_Controller_Front_Action {

    public function postAction() {
        if (!$this->_validateFormKey() && !Mage::getSingleton('customer/session')->isLoggedIn()) {
            // returns to the product item page
            $result['error'] = $this->__('Unknown user.');
            $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode($result));
        }

        try {
            $ratings = $this->getRequest()->getParam('ratings');
            foreach ($ratings as $ratingId => $optionId) {
                Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        //->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $this->getRequest()->getParam('blog_id'));
            }
            Mage::getModel('gearup_blog/RateAggregate')->aggregateEntityByRatingId($ratingId, $this->getRequest()->getParam('blog_id'));

            //Mage::getModel('rating/rating')->aggregate($rating->getVoteId());
            $result['success'] = $this->__('Thank you for your rating!');
        } catch (Exception $e) {
            $result['error'] = $this->__('Unable to post the review.');
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($result));
    }

}
