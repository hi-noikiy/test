<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */


/**
 * Class MageWorkshop_DetailedReview_ComplaintController
 */
class MageWorkshop_DetailedReview_ComplaintController extends Mage_Core_Controller_Front_Action
{
    /**
     * Save complaint on review
     */
    public function saveAction()
    {
        /** @var Mage_Core_Helper_data $helperJson */

        $helperJson = Mage::helper('core');

        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $params = $this->getRequest()->getParams();
        $config = Mage::helper('detailedreview/config');
        $reviewModel = Mage::getModel('review/review');
        $review = $reviewModel->load($params['review_id']);
        $complaint = Mage::getModel('detailedreview/complaintType')->load($params['complaint_id']);
        if (!$review->getId() || !$complaint->getId() || !Mage::getSingleton('customer/session')->isLoggedIn() || !$config->isDetailedReviewEnabled()
        ) {
            $responseJson['type']     = 'error';
            $responseJson['messages'] = $this->__('Unable to post the complaint.');
            $response->setBody($helperJson->jsonEncode($responseJson));
            return $this;
        }
        $customerId = Mage::helper('detailedreview')->getCustomerInfo()->getCustomerId();
        /* @var MageWorkshop_DetailedReview_Model_ComplaintType $complaintTypeModel */
        $complaintModel = Mage::getModel('detailedreview/reviewCustomerComplaint');

        $complaintCollection = $complaintModel->getCollection()
            ->addFieldToFilter('review_id', $params['review_id'])
            ->addFieldToFilter('customer_id', $customerId);

        if(!$complaintCollection->getSize()) {
            try {
                $complaintModel
                    ->setReviewId($params['review_id'])
                    ->setCustomerId($customerId)
                    ->setComplaintId($params['complaint_id'])
                    ->save();

                $responseJson['type']     = 'success';
                $responseJson['messages'] = $this->__('Your complaint has been added.');
                Mage::dispatchEvent('detailedreview_send_complaint_email_to_admin', array(
                    'complaint'  => $complaintModel,
                ));
            } catch (Exception $e) {
                $responseJson['type']     = 'error';
                $responseJson['messages'] = $this->__('Unable to post the complaint.');
                Mage::logException($e);
            }
        } else {
            $responseJson['type']     = 'error';
            $responseJson['messages'] = $this->__('You have already posted a complaint.');
        }
        $response->setBody($helperJson->jsonEncode($responseJson));
    }
}
