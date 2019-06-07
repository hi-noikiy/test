<?php

class MageWorkshop_ImportExportReview_Adminhtml_Drie_HistoryController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title('Reviews Sync History');
        $historyBlock = $this->getLayout()->createBlock('mageworkshop_importexportreview_adminhtml/history');
        $this->loadLayout()
            ->_addContent($historyBlock)
            ->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/drie_sync');
    }
}