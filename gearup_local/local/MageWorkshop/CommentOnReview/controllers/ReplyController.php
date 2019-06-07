<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

require_once Mage::getModuleDir('controllers', 'MageWorkshop_DetailedReview') . DS . 'ProductController.php';

/**
 * Class MageWorkshop_CommentOnReview_ReplyController
 */
class MageWorkshop_CommentOnReview_ReplyController extends MageWorkshop_DetailedReview_ProductController
{
    /**
     * Review entity codes
     *
     */
    const ENTITY_REVIEW_CODE = 'review';

    /**
     * Available Crop fields
     *
     * @var $_availableFields array
     */
    protected $_availableFields = array(
        'entity_pk_value',
        'title',
        'detail',
        'nickname'
    );

    /**
     * Save Reply on Review
     */
    public function saveAction()
    {
        /** @var Mage_Core_Helper_Data $helperJson */
        $helperJson = Mage::helper('core');

        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');

        /* @var MageWorkshop_DetailedReview_Model_Review $reply */
        $reply = Mage::getModel('review/review')->setData($this->_cropReviewData($this->getRequest()->getParams()));

        $responseJson = array('success' => false);

        /** @var MageWorkshop_CommentOnReview_Helper_Data $helper */
        $helper = Mage::helper('mageworkshop_commentonreview');

        // Check if customer write reviews without approving
        $autoApproveFlag = $helper->getAutoApproveFlag();

        /** @var MageWorkshop_CommentOnReview_Helper_Validate $validator */
        $validator = Mage::helper('mageworkshop_commentonreview/validate');
        $validate  = $validator->validate($reply->getData());

        if ($validate === true) {
            try {
                $reply->setEntityId($reply->getEntityIdByCode(self::ENTITY_REVIEW_CODE))
                    ->setStatusId($autoApproveFlag ? Mage_Review_Model_Review::STATUS_APPROVED : Mage_Review_Model_Review::STATUS_PENDING)
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setStores(array(Mage::app()->getStore()->getId()))
                    ->save();

                Mage::dispatchEvent('commentonreview_send_new_reply_email_to_admin', array(
                    'reply'  => $reply,
                ));

                if (Mage::getSingleton('customer/session')->getCustomerId()) {
                    Mage::dispatchEvent('commentonreview_send_new_reply_email_to_customer', array(
                        'reply' => $reply
                    ));
                }

                $responseJson['success'] = true;
                $responseJson['type'] = 'success';

                if ($autoApproveFlag) {
                    $responseJson['messages'][] = $this->__('Your reply has been added.');
                } else {
                    $responseJson['messages'][] = $this->__('Your reply has been accepted for moderation.');
                }

                $this->loadLayout();

                /** @var MageWorkshop_CommentOnReview_Block_Reply_Item $block */
                $block = $this->getLayout()->getBlock('mageworkshop.commentonreview.reply.item');

                $responseJson['html'] = false;

                if ($autoApproveFlag) {
                    $newReply = $reply->getCollection()
                        ->addFieldToFilter('main_table.review_id', array('eq' => $reply->getReviewId()))
                        ->addHelpfulInfo();

                    $block->setNewReply($newReply);

                    $responseJson['html'] = $block->toHtml();
                }

            } catch (Exception $e) {
                $responseJson['type']       = 'error';
                $responseJson['messages'][] = $this->__('An error occurred while saving your reply.');
                Mage::logException($e);
            }

        } else {
            $responseJson['type']     = 'error';
            $responseJson['messages'] = $validate['messages'];
        }

        $response->setBody($helperJson->jsonEncode($responseJson));
    }

    /**
     * Approve reply by email action
     */
    public function approveAction()
    {
        /* @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');

        /** @var array $params */
        $params = Mage::app()->getRequest()->getParams();

        /* @var MageWorkshop_DetailedReview_Model_Review $reply */
        $reply = Mage::getModel('review/review')->load($params['reply_id']);

        $result = false;

        try {
            if ($reply->getStatusId() != Mage_Review_Model_Review::STATUS_APPROVED
                && base64_encode($reply->getId() . MageWorkshop_CommentOnReview_Helper_Data::SALT_REPLY) == $params['hash'])
            {
                $reply->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)->save();

                $result = true;
            }
        } catch(Exception $e) {
            $session->addError($e->getMessage());
        }

        if ($result === true) {
            $message = $this->__('Reply was approved successfully');
            $session->addSuccess($message);
        } else {
            $message = $this->__('Reply already approved');
            $session->addNotice($message);
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * validate captcha
     */
    public function checkCaptchaReplyAction()
    {
        $params = $this->getRequest()->getParams();

        /** @var MageWorkshop_DetailedReview_Model_ReCaptchaWrapper_ReCaptcha $reCaptchaModel */
        $reCaptchaModel = Mage::getModel('detailedreview/reCaptchaWrapper_reCaptcha');

        $response = array('success' => false);

        if (isset($params['g-recaptcha-response'])) {
            $response = $reCaptchaModel->verifyResponse(
                $params['g-recaptcha-response'],
                Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_CAPTCHA_PRIVATE_KEY)
            );
        }

        Mage::getSingleton('core/session')->setCaptchaIsValid($response['success']);

        $this->getResponse()->setBody($response['success'] ? 'valid' : 'invalid');
    }
}