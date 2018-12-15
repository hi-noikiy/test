<?php
require_once 'Mage/Catalog/controllers/CategoryController.php';

class MindMagnet_Sort_CategoryController extends Mage_Catalog_CategoryController
{
    protected function _getListHtml()
    {
        $layout = $this->getLayout();
        $layout->getUpdate()->load('catalog_category_ajax_view');
        $layout->generateXml()->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getLayeredNavHtml()
    {
        $layout = $this->getLayout();
        $layout->getUpdate()->load('catalog_category_layered_ajax');
        $layout->generateXml()->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function viewAction()
    {
        $oneRequest = $this->getRequest();
        if ($oneRequest->isXmlHttpRequest() || $oneRequest->getParam('ajax') == 1) {
            if ($category = $this->_initCatagory()) {
                $this->getResponse()->setBody($this->_getLayeredNavHtml());
            }
        } else {
            parent::viewAction();
        }
    }
}
