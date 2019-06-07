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

class MageWorkshop_CommentOnReview_Adminhtml_Mageworkshop_Commentonreview_CommentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Reviews and Ratings'))
            ->_title($this->__('Customer Comments'));

        $this->_title($this->__('All Comments'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('reviewGrid');
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('mageworkshop_commentonreview/adminhtml_comment_main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Reviews and Ratings'))
            ->_title($this->__('Customer Comments'));

        $this->_title($this->__('pending Comments'));

        if ($this->getRequest()->getParam('ajax')) {
            Mage::register('usePendingFilter', true);
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        Mage::register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('mageworkshop_commentonreview/adminhtml_comment_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Comment and Ratings'))
            ->_title($this->__('Customer Comment'));

        $this->_title($this->__('Edit Comment'));
        $comment = Mage::getModel('review/review')->load($this->getRequest()->getParam('id'));
        $mainReview = Mage::getModel('review/review')->load($comment->getEntityPkValue());
        $storeId = $mainReview->getStoreId();
        if (!Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MODULE_ENABLE, $storeId)) {
            $configPath = Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/mageworkshop_commentonreview');
            $message = "It seems like MageWorkshop_CommentOnReview extension is disabled. Check <a href=\"%s\" target='_blank'>module configurations</a> to use all features.";
            Mage::getSingleton('core/session')->addNotice(sprintf($message, $configPath));
        }
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('mageworkshop_commentonreview/adminhtml_comment_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {

            $helper = Mage::helper('mageworkshop_commentonreview');

            $review = Mage::getModel('review/review')->load($reviewId);
            $session = Mage::getSingleton('adminhtml/session');
            if (!$review->getId()) {
                $session->addError($helper->__('The comment was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data);
                    $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                    if (isset($data['created_at']) && $data['created_at']) {
                        $dateCreated = Mage::app()->getLocale()->date($data['created_at'], $format);
                        $review->setCreatedAt(Mage::getModel('core/date')->gmtDate(null, $dateCreated->getTimestamp()));
                    } else {
                        $review->setCreatedAt(Mage::getModel('core/date')->gmtDate());
                    }
                    $review->save();

                    $session->addSuccess($helper->__('The comment has been saved.'));
                } catch (Mage_Core_Exception $e) {
                    $session->addError($e->getMessage());
                } catch (Exception $e){
                    $session->addException($e, $helper->__('An error occurred while saving this comment.'));
                }
            }

            return $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $reviewId   = $this->getRequest()->getParam('id', false);
        $session    = Mage::getSingleton('adminhtml/session');

        $helper = Mage::helper('mageworkshop_commentonreview');

        try {
            Mage::getModel('review/review')->setId($reviewId)
                ->aggregate()
                ->delete();
            
            $session->addSuccess($helper->__('The comment has been deleted'));
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            
            return;
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e){
            $session->addException($e, $helper->__('An error occurred while deleting this comment.'));
        }

        $this->_redirect('*/*/edit/',array('id'=>$reviewId));
    }

    public function massDeleteAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($reviewsIds)) {
            $session->addError(Mage::helper('mageworkshop_commentonreview')->__('Please select comment(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e){
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while deleting record(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateStatusAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('adminhtml/session');

        $helper = Mage::helper('mageworkshop_commentonreview');

        if(!is_array($reviewsIds)) {
            $session->addError($helper->__('Please select comment(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->setStatusId($status)
                        ->save()
                        ->aggregate();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $helper->__('An error occurred while updating the selected comment(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massVisibleInAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('adminhtml/session');

        $helper = Mage::helper('mageworkshop_commentonreview');

        if(!is_array($reviewsIds)) {
            $session->addError($helper->__('Please select comment(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $helper->__('An error occurred while updating the selected comment(s).'));
            }
        }

        $this->_redirect('*/*/pending');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/mageworkshop_commentonreview');
    }
}
