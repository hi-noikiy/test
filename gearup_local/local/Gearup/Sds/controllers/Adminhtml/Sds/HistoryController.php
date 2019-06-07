<?php


class Gearup_Sds_Adminhtml_Sds_HistoryController extends Gearup_Sds_Controller_Adminhtml_Sds
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteallAction()
    {
        try {
            $historyCollec = Mage::getModel('gearup_sds/history')->getCollection();
            if ($historyCollec->getSize()) {
                foreach ($historyCollec as $history) {
                    $history->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('History are cleaned')
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $e->getMessage()
            );
        }

        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'DXB_storage_history.csv';
        $content    = $this->getLayout()->createBlock('gearup_sds/adminhtml_history_grid')->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $this->_prepareDownloadResponse($fileName, $content, $contentType);
    }
}