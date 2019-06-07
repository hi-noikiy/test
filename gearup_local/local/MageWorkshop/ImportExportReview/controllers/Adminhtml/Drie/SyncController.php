<?php

class MageWorkshop_ImportExportReview_Adminhtml_Drie_SyncController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title('Reviews Sync Stores');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $sync = $this->_getSyncModel();
        if ($syncId = $this->getRequest()->getParam('id', false)) {
            $sync->load($syncId);
            if ($sync->getId() < 1) {
                $this->_getSession()->addError($this->__('This sync store no longer exists.'));
                $this->_redirect('adminhtml/drie_sync/index');
                return;
            }
            $this->_title('Edit Sync Store');
        } else {
            $this->_title('New Sync Store');
        }
        if ($postData = $this->getRequest()->getPost('syncData')) {
            try {
                // Save Object
                $this->_handlePostData($sync, $postData);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $sync->getId()));
                    return;
                }
                $this->_redirect('adminhtml/drie_sync/index');
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        Mage::register('current_drie_sync', $sync);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->_getSyncModel();
                $model->setId($id);
                $model->delete();
                $this->_getSession()->addSuccess($this->_getHelper()->__('The sync store has been deleted.'));
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_getSession()->addError(Mage::helper('cms')->__('Unable to find a sync store to delete.'));
        $this->_redirect('*/*/');
    }

    public function syncNowAction()
    {
        if ($syncId = $this->getRequest()->getParam('id', false)) {
            /** @var MageWorkshop_ImportExportReview_Model_Sync $sync */
            $sync = $this->_getSyncModel()->load($syncId);
            try {
                $sync->syncReviews(3000, true);
                $filter      = 'store_name=' . $sync->getStoreName();
                $filter      = base64_encode($filter);
                $historyUrl  = $this->getUrl('adminhtml/drie_history', array('filter' => $filter));
                $this->_getSession()->addSuccess($this->_getHelper()->__(
                    "All reviews were pulled successfully. You can check the <a href='%s' target='_blank'>history</a>.", $historyUrl
                ));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            $this->_redirect('*/*/edit', array('id' => $syncId));
            return;
        } else {
            $this->_getSession()->addError($this->_getHelper()->__('Error. Please reload the page and try again'));
        }
    }

    public function syncSkuNowAction()
    {
        if ($syncId = $this->getRequest()->getParam('id', false)) {
            /** @var MageWorkshop_ImportExportReview_Model_Sync $sync */
            $sync = $this->_getSyncModel()->load($syncId);
            if ($skuList = $this->getRequest()->getParam('sku_list')) {
                try {
                    $sync->syncReviewsBySkuList($skuList);
                    $this->_getSession()->addSuccess($this->_getHelper()->__('All reviews by SKU were pulled successfully'));
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->_getHelper()->__('Please specify the SKU list, comma separated'));
            }
        } else {
            $this->_getSession()->addError($this->_getHelper()->__('Error. Please reload the page and try again'));
        }

        $this->_redirect('*/*/edit', array('id' => $syncId));
        return;
    }

    /**
     * Process object save
     *
     * @param MageWorkshop_ImportExportReview_Model_Sync $sync
     * @param array $postData
     * @throws Exception
     */
    protected function _handlePostData(MageWorkshop_ImportExportReview_Model_Sync $sync, Array $postData)
    {
        $sync->addData($postData);
        $sync->save();
        $this->_getSession()->addSuccess($this->__('The sync has been saved.'));
    }

    /**
     * @return MageWorkshop_ImportExportReview_Model_Sync
     */
    protected function _getSyncModel()
    {
        return Mage::getModel('mageworkshop_importexportreview/sync');
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/drie_sync');
    }
}