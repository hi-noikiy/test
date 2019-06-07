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
require_once Mage::getModuleDir('controllers', 'Mage_Review') . DS . 'ProductController.php';

class MageWorkshop_DetailedReview_ProductController extends Mage_Review_ProductController
{
    /**
     * @var array
     */
    protected $_availableFields = array(
        'customer_email',
        'title',
        'video',
        'image',
        'detail',
        'good_detail',
        'no_good_detail',
        'nickname',
        'location',
        'age',
        'height',
        'response',
        'sizing',
        'body_type',
        'pros',
        'cons',
        'recommend_to'
    );

    /**
     * @return $this|Exception|void
     */
    public function postAction()
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = Mage::helper('detailedreview');
        /** @var MageWorkshop_DetailedReview_Helper_Config $config */
        $config = Mage::helper('detailedreview/config');
        $ajaxSubmit = $config->isAjaxSubmit();
        $responseJson = array('success' => false);
        $helperJson = Mage::helper('core');
        /* @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        if (!$config->isDetailedReviewEnabled()) {
            parent::postAction();
            return $this;
        }
        if ($config->isHoneypotEnabled()) {
            if ($this->getRequest()->getParam('middlename', null)) {
                $this->_setErrorResponse($responseJson, 'Something went wrong. Please try again.');
                return $this;
            }
        }
        $customerInfo = $helper->getCustomerInfo();
        if(strtolower($customerInfo->getType()) == strtolower(MageWorkshop_DetailedReview_Model_CustomerIdentifier::IDENTIFIER_TYPE_ID)) {
            $customerInfo->setCustomerId($customerInfo->getValue());
            $customerInfo->setCustomerEmail(Mage::getModel('customer/customer')->load($customerInfo->getValue())->getEmail());
        } elseif (strtolower($customerInfo->getType()) == strtolower(MageWorkshop_DetailedReview_Model_CustomerIdentifier::IDENTIFIER_TYPE_EMAIL)) {
            $customerInfo->setCustomerEmail($customerInfo->getValue());
        }

        if ($config->isWriteReviewOnce()) {
            $result = array();
            if ($customerInfo->getCustomerId() || $customerInfo->getCustomerEmail()) {
                $result = $helper->getReviewsPerProductByCustomer($customerInfo, $this->getRequest()->getParam('id'));
            }
            if (!empty($result)) {
                $this->_setErrorResponse($responseJson, 'Product already reviewed by You');
                return $this;
            }
        }
        
        $product = $this->_initProduct();
        if ($config::isOnlyVerifiedBuyer() && !$helper->isVerifiedBuyer($product)) {
            $this->_setErrorResponse($responseJson, 'Only verified buyer can write review');
            return $this;
        }

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if ($product && !empty($data)) {
            $captchaIsValid = true;
            if ($config->isCaptchaEnabled()) {
                $validateCaptcha = $session->getCaptchaIsValid();
                if (!$validateCaptcha) {
                    $session->addError($helper->__('You have entered wrong captcha.'));
                    $captchaIsValid = false;
                }
            }
            $data = $helper->prepareFormData($data);
            // Check if customer write reviews without approving
            $autoApproveFlag = $helper->getAutoApproveFlag();
            $availableFields = new Varien_Object($this->_availableFields);
            Mage::dispatchEvent('detailedreview_product_review_post_available_fields', array('available_fields' => $availableFields));
            $this->_availableFields = $availableFields->getData();
            /* @var MageWorkshop_DetailedReview_Model_Review $review */
            $review = Mage::getModel('review/review')->setData($this->_cropReviewData($data));
            $files = $helper->uploadImages();
            if (!empty($files['images']) && array_filter($files['images'])) {
                $review->setData('image', implode(',', $files['images']));
            }
            $validate = $review->validate();
            if ($validate === true && $files['success'] && $captchaIsValid) {
                Mage::dispatchEvent('detailedreview_product_review_post', array(
                    'review' => $review,
                    'form_data' => $data
                ));
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setStatusId($autoApproveFlag ? Mage_Review_Model_Review::STATUS_APPROVED : Mage_Review_Model_Review::STATUS_PENDING)
                            ->setCustomerId($customerInfo->getCustomerId())
                            ->setCustomerEmail($customerInfo->getCustomerEmail())
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->setStores(array(Mage::app()->getStore()->getId()))
                            ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }
                    Mage::dispatchEvent('detailedreview_send_new_review_email_to_admin', array(
                        'review' => $review
                    ));
                    Mage::dispatchEvent('detailedreview_send_new_review_email_to_customer', array(
                        'review' => $review
                    ));
                    $review->aggregate();
                    if ($ajaxSubmit) {
                        $responseJson['success'] = true;
                        if ($autoApproveFlag) {
                            $responseJson['messages'][] = $helper->__('Your review has been added.');
                        } else {
                            $responseJson['messages'][] = $helper->__("Thank you, review has been submitted for approval.");
                        }
                    } else {
                        if($autoApproveFlag) {
                            $session->addSuccess($helper->__('Your review has been added.'));
                        } else {
                            $session->addSuccess($helper->__("Thank you, review has been submitted for approval."));
                        }
                    }
                    Mage::getSingleton('core/session')->addSuccess($helper->__("Thank you, review has been submitted for approval."));
                    
                } catch (Exception $e) {
                    if ($ajaxSubmit) {
                        $responseJson['type'] = 'error';
                        $responseJson['content'] = '<p>' . $helper->__('Unable to post the review.') . '</p>';
                        $this->getResponse()->setBody($helperJson->jsonEncode($responseJson));
                        return $e;
                    } else {
                        $session->setFormData($data);
                        $session->addError($helper->__('Unable to post the review.'));
                    }
                    Mage::getSingleton('core/session')->addError($helper->__('Unable to post the review.'));
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $responseJson['messages'][] = $errorMessage;
                        if (!$ajaxSubmit) {
                            $session->addError($errorMessage);
                        }
                    }
                } else {
                    if ($ajaxSubmit) {
                        $responseJson['type'] = 'error';
                        $responseJson['messages'][] = $helper->__('Unable to post the review.');
                    } else {
                        $session->addError($helper->__('Unable to post the review.'));
                    }
                    Mage::getSingleton('core/session')->addError($helper->__('Unable to post the review.'));
                }
                if (!$files['success']) {
                    $responseJson['type'] = 'notice';
                    foreach ($files['errors'] as $imageName => $errorMessages) {
                        foreach($errorMessages as $message) {
                            $responseJson['messages'][] = $this->__('Image (%s) has the following problem: ', $imageName) . $message;
                            if (!$ajaxSubmit) {
                                $session->addError($message);
                            }
                        }
                    }
                }
            }
            if ($ajaxSubmit) {
                $responseJson['html'] = false;
                if ($responseJson['success'] && $autoApproveFlag) {
                    $this->loadLayout();
                    $block = $this->getLayout()->getBlock('reviews_wrapper');
                    if ($html = $block->getChildHtml('review-wrapper')) {
                        $responseJson['html'] = $this->_escapeTags($html);
                    }
                }
                $responseJson['redirect'] = $this->_getRefererUrl();
                $this->_wrapMessages($responseJson);
                $this->getResponse()
                    ->setBody($this->_escapeTags($helperJson->jsonEncode($responseJson)));
            } else {
                if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
                    $this->_redirectUrl($redirectUrl);
                    return $this;
                }
                $referrerUrl = $this->_getRefererUrl();
                if ( preg_match('/.*\&show_popup=1.*/', $referrerUrl) ) {
                    $this->_redirectUrl(preg_replace('/(.*)\&show_popup=1(.*)/', '$1$2', $referrerUrl));
                    return $this;
                }
                $this->_redirectReferer();
            }
        }
    }

    /**
     * @param string $string
     * @return mixed
     */
    protected function _escapeTags($string)
    {
        return str_replace('<', '[[', $string);
    }

    /**
     * validate captcha
     */
    public function checkCaptchaAction()
    {
        $params = $this->getRequest()->getParams();

        /** @var MageWorkshop_DetailedReview_Model_ReCaptchaWrapper_ReCaptcha $reCaptchaModel */
        $reCaptchaModel = Mage::getModel('detailedreview/reCaptchaWrapper_reCaptcha');

        $response = array('success' => false);

        if (isset($params['g-recaptcha-response'])) {
            $response = $reCaptchaModel->verifyResponse(
                $params['g-recaptcha-response'],
                Mage::getStoreConfig('detailedreview/captcha/private_key')
            );
        }

        Mage::getSingleton('core/session')->setCaptchaIsValid($response['success']);

        $this->getResponse()->setBody($response['success'] ? 'valid' : 'invalid');
    }

    /**
     * Show list of product's reviews
     */
    public function listAction()
    {
        if(!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::listAction();
        } else {
            if ($product = $this->_initProduct()) {
                Mage::register('productId', $product->getId());
                $this->getResponse()->setRedirect($product->getProductUrl());
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * Show details of one review
     */
    public function viewAction()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::viewAction();
        } else {
            $review = $this->_loadReview((int) $this->getRequest()->getParam('id'));
            if (!$review) {
                $this->_forward('noroute');
                return;
            }

            $product = $this->_loadProduct($review->getEntityPkValue());
            if (!$product) {
                $this->_forward('noroute');
                return;
            }
            $this->getResponse()->setRedirect($product->getProductUrl());

            $this->loadLayout();
            $this->_initLayoutMessages('review/session');
            $this->_initLayoutMessages('catalog/session');
            $this->renderLayout();
        }
    }

    public function getReviewsByAjaxAction()
    {
        $responseData = array(
            'html' => '',
            'reviewsCount' => array()
        );
        try {
            if (!$productId = (int) $this->getRequest()->getParam('product_id')) {
                Mage::throwException(Mage::helper('detailedreview')->__('Unable to load review list. Please, contact support is this issue remains.'));
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::register('product', $product);
            Mage::register('current_product', $product);

            $layout = $this->getLayout();
            $layout->getUpdate()->load(strtolower($this->getFullActionName()));
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
            $responseData['html'] = $this->renderLayout()->getResponse()->getBody();

            /** @var MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed $reviewDetailsBlock */
            $reviewDetailsBlock = $this->getLayout()->createBlock('detailedreview/rating_entity_detailed');
            foreach ($reviewDetailsBlock->getAvailableDateRanges() as $key => $val) {
                $responseData['reviewsCount'][$key] = $reviewDetailsBlock->getQtyByRange($key);
            }
            $qtyMarks = $reviewDetailsBlock->getQtyMarks();
            $countReviewsWithRating = 0;
            $avgRating = 0;
            for ($key=5;$key>0;$key--) {
                if (array_key_exists($key, $qtyMarks)) {
                    $responseData['qtyMarks'][$key] = $qtyMarks[$key];
                    $countReviewsWithRating = $countReviewsWithRating + $qtyMarks[$key];
                    $avgRating = $avgRating + $qtyMarks[$key]*$key;
                } else {
                    $responseData['qtyMarks'][$key] = 0;
                }
            }
            $responseData['countReviewsWithRating'] = $countReviewsWithRating;
            $responseData['avgRating'] = $avgRating;
            $reviewSizing = Mage::getSingleton('detailedreview/review_sizing');
            $sizing = $reviewDetailsBlock->getAverageSizing();
            $responseData['averageSizing']['optionWidth'] = $reviewSizing->getOptionWidth($sizing);
            $responseData['averageSizing']['indent'] = $reviewSizing->getIndent($sizing);
            $responseData['averageSizing']['optionValue'] = $reviewSizing->getOptionValue($sizing);
        } catch (Exception $e) {
            Mage::logException($e);
            $messageBlock = $this->getLayout()->createBlock('core/messages');
            $responseData['html'] = $messageBlock->addError($e->getMessage())->toHtml();
        }
        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse();
        $response->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $response->setBody(Mage::helper('core')->jsonEncode($responseData));
    }

    public function getShortLinkAction()
    {
        $bitlyResponse = array();
        $longUrl = urlencode($this->getRequest()->getParam('url'));
        $bitly_login = Mage::getStoreConfig('detailedreview/social_share_optios/bitly_login');
        $bitly_apikey = Mage::getStoreConfig('detailedreview/social_share_optios/bitly_api_key');
        if ($bitly_login && $bitly_apikey) {
            $bitlyResponse = json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login={$bitly_login}&apiKey={$bitly_apikey}&longUrl={$longUrl}&format=json"));
        } else {
            $bitlyResponse['message'] = $this->__('Service Temporarily Unavailable');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($bitlyResponse));
    }

    /**
     * @param array $responseJson
     * @return $this
     */
    protected function _wrapMessages(&$responseJson) {
        $responseJson['content'] = '';
        foreach($responseJson['messages'] as $message) {
            $responseJson['content'] .= '<p>' . $message . '</p>';
        }
        $responseJson['messages'] = $responseJson['content'];
        unset($responseJson['content']);
        return $this;
    }

    public function checkWriteOnceAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $result = array();
        if (Mage::getStoreConfig('detailedreview/settings_customer/write_review_once') && $productId) {
            $customerData = Mage::helper('detailedreview')->getCustomerInfo();
            if ($customerData->getCustomerId() || $customerData->getCustomerEmail()) {
                $result = Mage::helper('detailedreview')->getReviewsPerProductByCustomer($customerData, $productId);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    
    public function checkBuyerProductsAction()
    {
        $result = array();
        try {
            if (Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer') && ($productId = $this->getRequest()->getParam('product_id'))) {
                $result['isVerified'] = false;
                /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
                $helper = Mage::helper('detailedreview');
                /** @var Mage_Catalog_Model_Product $currentProduct */
                $currentProduct   = $helper->getProduct($productId);
                
                if ($helper->isVerifiedBuyer($currentProduct)) {
                    $result['isVerified'] = true;
                }
                $result['status'] = 'success';
                $result['code'] = 200;
            }
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['code'] = 404;
            $result['message'] = $e->getMessage();
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
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
        $data = Mage::helper('detailedreview')->prepareShareReviewData($params);


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

    /**
     * Crops POST values
     * @param array $reviewData
     * @return array
     */
    protected function _cropReviewData(array $reviewData)
    {
        $croppedValues = array();
        $allowedKeys = array_fill_keys($this->_availableFields, true);

        foreach ($reviewData as $key => $value) {
            if (isset($allowedKeys[$key])) {
                $croppedValues[$key] = $value;
            }
        }

        return $croppedValues;
    }

    /**
     * @param array $responseJson
     * * @param string $message
     * @return $this
     */
    protected function _setErrorResponse($responseJson, $message) {
        if (Mage::helper('detailedreview/config')->isAjaxSubmit()) {
            $responseJson['html'] = false;
            $responseJson['redirect'] = $this->_getRefererUrl();
            $responseJson['messages'][] = Mage::helper('detailedreview')->__($message);
            $this->_wrapMessages($responseJson);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseJson));
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper('detailedreview')->__($message));
            $this->_redirectReferer();
        }
        return $this;
    }

}
