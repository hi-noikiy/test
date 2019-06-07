<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class Dv_Drw_SearchController
 */
class MageWorkshop_ReviewWall_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Ajax loading reviews
     */
    public function reviewsAction()
    {
        $reviewsCollection  = Mage::getSingleton('reviewwall/review')->getPreparedReviewsCollection();
        $responseData = Mage::helper('reviewwall')->prepareResponseData($reviewsCollection);

        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $response->setBody(Mage::helper('core')->jsonEncode($responseData));
    }

    /**
     * Vote for review - is it helpful or not
     */
    public function voteAction()
    {
        $params = $this->getRequest()->getParams();
        $response = array();
        if (!empty($params) && $reviewId = (int) $this->getRequest()->getParam('review_id')) {
            $helper = Mage::helper('detailedreview');
            /** @var MageWorkshop_DetailedReview_Model_Review_Helpful $helpful */
            $helpful = Mage::getModel('detailedreview/review_helpful')->setData($params);

            if (Mage::getSingleton('customer/session')->IsLoggedIn()) {
                $helpful->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            }
            $helpful->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr());

            if ($validationErrors = $helpful->validate()) {
                $response = array(
                    'msg' => array(
                        'type' => 'error',
                        'text' => $helper->__('Unable to add your vote. %s', implode(', ', $validationErrors))
                    )
                );
            } else {
                try {
                    $helpful->save();
                    $helpfulVotes   = $helpful->getQtyHelpfulVotesForReview($reviewId);
                    $unhelpfulVotes = $helpful->getQtyVotesForReview($reviewId) - $helpfulVotes;
                    $response = array(
                        'helpful'   => $helpfulVotes,
                        'unhelpful' => $unhelpfulVotes,
                        'msg'       => array(
                            'type' => 'success',
                            'text' => $helper->__('Your vote has been added successfully.')
                        )
                    );
                } catch (Exception $e) {
                    $response = array(
                        'msg' => array(
                            'type' => 'error',
                            'text' => $helper->__('Unable to add your vote.')
                        )
                    );
                }
            }
        } else {
            $response = array(
                'msg' => array(
                    'type' => 'error',
                    'text' => $this->__('Unable to add your vote.')
                )
            );
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Share Review By Email
     */
    public function shareEmailAction()
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $reply */
        $params = Mage::app()->getRequest()->getParams();

        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');

        /** @var Mage_Core_Helper_Data $helperJson */
        $helperJson = Mage::helper('core');

        $responseJson = array('status' => false);

        $data = Mage::helper('reviewwall')->prepareShareReviewData($params);
        /** @var MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData */
        $mailersData = Mage::getModel('detailedreview/review_mailersData')->setData($data);

        /** @var MageWorkshop_DetailedReview_Model_Review $reviewModel */
        $reviewModel = Mage::getModel('detailedreview/review');

        try {
            $reviewModel->sendEmail($mailersData);

            if ($reviewModel->getEmailSent() === true) {
                $responseJson = array(
                    'status'  => 'success',
                    'message' => $this->__('Review was successfully shared')
                );
            } else {
                $responseJson = array(
                    'status'  => 'error',
                    'message' => $this->__('An error has occurred.')
                );
            }
        } catch(Exception $e) {
            $responseJson['status']  = 'error';
            $responseJson['message'] = $this->__('An error has occurred.');
            Mage::logException($e);
        }

        $response->setBody($helperJson->jsonEncode($responseJson));
    }
}
