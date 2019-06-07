<?php


class Gearup_Sds_Adminhtml_Sds_SdsController extends Gearup_Sds_Controller_Adminhtml_Sds
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/gearup_sds');
    }

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

    /* Ticket5492-DXB Storage Manager - Report In/Out 
        As per michal suggestion we add sds status field into qty update popup.*/
    public function updatespqtyAction()
    {
        try {
            $fieldId = (int) $this->getRequest()->getParam('id');
            $qty = $this->getRequest()->getParam('qty');
            $sprice = $this->getRequest()->getParam('specialprice');
            $desc = $this->getRequest()->getParam('desc');

            /*Ticket5492-get sds status in param */
            $sdsStatus = $this->getRequest()->getParam('sds');
            $cost = $this->getRequest()->getParam('cost');

            if ($fieldId) {
                $model = Mage::getModel('catalog/product')->load($fieldId);
                if ($model->getSameDayShipping()) {
                    $previousSDS = 'Yes';
                    $previousValue = 1;
                } else {
                    $previousSDS = 'No';
                    $previousValue = 0;
                }

                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                $prevoiusQty = round($stockItem->getData('qty'));
                if ($stockItem->getData('is_in_stock')) {
                    $previousStockStatus = 'In Stock';
                } else {
                    $previousStockStatus = 'Out of Stock';
                }
                $stockItem->setData('qty',$qty);
                $stockItem->setData('is_in_stock',$qty ? 1 : 0);
                $stockItem->save();
                if ($stockItem->getData('is_in_stock')) {
                    if (!$previousValue) {
                        $action = '"'.$prevoiusQty.' '.$previousStockStatus.'; SDS is '.$previousSDS.'", Updated QTY to '.$qty.' and set SDS to Yes ';
                        if ($desc) {
                            $action .= ", Description: ".$desc;
                        }
                    } else {
                        $action = '"'.$prevoiusQty.' '.$previousStockStatus.'; SDS is '.$previousSDS.'", Updated QTY to '.$qty.' and set SDS to '.$previousSDS;
                        if ($desc) {
                            $action .= ", Description: ".$desc;
                        }
                    }
                    $model->setSameDayShipping(1);
                    Mage::helper('gearup_sds')->assignSDS($model->getId());
                } else {
                    $action = '"'.$prevoiusQty.' '.$previousStockStatus.'; SDS is '.$previousSDS.'", Updated QTY to '.$qty.' and set SDS to No ';
                    if ($desc) {
                        $action .= ", Description: ".$desc;
                    }
                    $model->setSameDayShipping(0);
                    Mage::helper('gearup_sds')->unassignSDS($model->getId());
                }
                $model->setSpecialPrice($sprice);

                // Ticket5492-get sds status in param */
                // If status is received no then and only then we need to update otherwise no need to perform any action.
                if ($sdsStatus == '0'){
                    $model->setSameDayShipping(0);
                }
                $model->setCost($cost);
                $model->save();

                if($model->getSameDayShipping() && $previousSDS == 'Yes'){
                	$preQty = $prevoiusQty;
                	$qty = $stockItem->getQty();
                }else{
                	$preQty = $stockItem->getQty() - $qty;
                }

                Mage::helper('gearup_sds')->recordHistory($model->getId(), $action, $preQty, $qty, null, $previousValue, $prevoiusQty);
                Mage::getModel('gearup_sds/observer')->checkQtychange($model, $prevoiusQty);
                $track = Mage::getModel('gearup_sds/tracking')->load($model->getId(), 'product_id');

                if (!$model->getLowStock()) {
                    $color = '';
                } else {
                    if (round($stockItem->getData('qty')) >= $model->getLowStock() && $stockItem->getData('qty') != 0) {
                        $color = '#00e600';
                    } elseif (round($stockItem->getData('qty')) < $model->getLowStock() && $stockItem->getData('qty') != 0) {
                        $color = '#ffff00';
                    } elseif (round($stockItem->getData('qty')) < $model->getLowStock() && $stockItem->getData('qty') == 0) {
                        $color = '#ff0000';
                    }
                }
                //$trackDate = Mage::helper('core')->formatDate($model->getUpdateLastAt(), 'medium', false);
                $trackDate = Mage::helper('core')->formatDate($track->getUpdateLastAt(), 'medium', false);
                header('Content-type: application/json');
                echo json_encode(array('stocksta' => $stockItem->getData('is_in_stock'), 'rowid' => $model->getId(), 'colore' => $color, 'track'=> $trackDate));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function massDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        } else {
            if (!empty($productIds)) {
                try {
                    foreach ($productIds as $productId) {
                        $product = Mage::getSingleton('catalog/product')->load($productId);
                        Mage::dispatchEvent('catalog_controller_product_delete', array('product' => $product));
                        $product->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($productIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        $status     = (int)$this->getRequest()->getParam('status');

        try {
            $this->_validateMassStatus($productIds, $status);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('status' => $status), $storeId);
            Mage::dispatchEvent('catalog_controller_product_mass_status', array('product_ids' => $productIds));

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/', array('store'=> $storeId));
    }

    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            if (!Mage::getModel('catalog/product')->isProductsHasSku($productIds)) {
                throw new Mage_Core_Exception(
                    $this->__('Some of the processed products have no SKU value defined. Please fill it prior to performing operations on these products.')
                );
            }
        }
    }

    public function exportSdsAction() {
        try {
            $filename = 'DXB Storage List.csv';
            $content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_export_sds');
            $this->_prepareDownloadResponse($filename, $content->getCsvFile());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error exporting users.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

    public function exportSdsLowAction() {
        try {
            $filename = 'DXB Storage Low Stock Report.csv';
            $content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_export_sdslow');
            $this->_prepareDownloadResponse($filename, $content->getCsvFile());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error exporting report.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

    public function exportSdsRedAction() {
        try {
            $filename = 'DXBS Red Report.csv';
            $content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_export_sdsred');
            $this->_prepareDownloadResponse($filename, $content->getCsvFile());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error exporting report.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

    public function updateLowsAction()
    {
        try {
            $fieldId = (int) $this->getRequest()->getParam('id');
            $lowstock = $this->getRequest()->getParam('lowstock');
            if ($fieldId) {
                $model = Mage::getModel('catalog/product')->load($fieldId);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                $previousLowstock = $model->getLowStock();
                $model->setLowStock($lowstock);
                $model->save();

                $action = '"Low Stock is '.$previousLowstock.'", Updated low stock to '.$lowstock;
                Mage::helper('gearup_sds')->recordHistory($model->getId(), $action);
                if (!$model->getLowStock()) {
                    $color = '';
                } else {
                    if (round($stockItem->getData('qty')) >= $model->getLowStock() && $stockItem->getData('qty') != 0) {
                        $color = '#00e600';
                    } elseif (round($stockItem->getData('qty')) < $model->getLowStock() && $stockItem->getData('qty') != 0) {
                        $color = '#ffff00';
                    } elseif (round($stockItem->getData('qty')) < $model->getLowStock() && $stockItem->getData('qty') == 0) {
                        $color = '#ff0000';
                    }
                }
                header('Content-type: application/json');
                echo json_encode(array('rowid' => $model->getId(), 'colore' => $color));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function updateSdsAction()
    {
        try {
            $fieldId = (int) $this->getRequest()->getParam('id');
            if ($fieldId) {
                $model = Mage::getModel('catalog/product')->load($fieldId);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                $qty = round($stockItem->getData('qty'));    
                $previousSds = Mage::helper('gearup_sds')->sdsStatus($model->getSameDayShipping());
                if ($model->getData('same_day_shipping') == 1) {
                    $model->setSameDayShipping(0);
                } else {
                    $model->setSameDayShipping(1);
                }
                $model->save();
                $labelModel =  Mage::getModel('prolabels/index');
                $labelModels = $labelModel->getResource()->getLabelProductIds(4);
                $cate = Mage::getSingleton('catalog/category_api');
                $cates = Mage::getModel('catalog/category')->getCollection();
                $cates->addAttributeToFilter('category_deal', 1);
                if ($model->getData('same_day_shipping') == 1) {
                    $color = '#00e600';
                    if (!in_array($model->getId(), $labelModels)) {
                        $labelModel->setRulesId(4);
                        $labelModel->setProductId($model->getId());
                        $labelModel->save();
                    }
                    foreach ($cates as $catChild) {
                        $cateModel = Mage::getModel('catalog/category')->load($catChild->getEntityId());
                        if ($cateModel->getEntityId() == Gearup_Sds_Helper_Data::SDS_CAT_ID) {
                            $cate->assignProduct($catChild->getId(), $model->getId());
                        }
                    }
                    $action = '"SDS is '.$previousSds.'", SDS set to Yes';
                } else {
                    $color = '#ff0000';
                    if (in_array($model->getId(), $labelModels)) {
                        $labelModel->load($model->getId(),'product_id')->delete();
                    }
                    foreach ($cates as $catChild) {
                        $cateModel = Mage::getModel('catalog/category')->load($catChild->getEntityId());
                        if ($cateModel->getEntityId() == Gearup_Sds_Helper_Data::SDS_CAT_ID) {
                            $cate->assignProduct($catChild->getId(), $model->getId());
                            $cate->removeProduct($catChild->getId(), $model->getId());
                        }
                    }
                    $action = '"SDS is '.$previousSds.'", SDS set to No';
                }
                Mage::helper('gearup_sds')->recordHistory($model->getId(), $action, $qty, $qty);
                header('Content-type: application/json');
                echo json_encode(array('rowid' => $model->getId(), 'colore' => $color, 'sta' => $model->getData('same_day_shipping')));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function updateSpriceAction()
    {
        try {
            $fieldId = (int) $this->getRequest()->getParam('id');
            $sprice = $this->getRequest()->getParam('specialprice');
            if ($fieldId) {
                $model = Mage::getModel('catalog/product')->load($fieldId);
                $model->setSpecialPrice($sprice);
                $model->save();
                $price = Mage::app()->getLocale()->currency('USD')->toCurrency($model->getSpecialPrice());
                header('Content-type: application/json');
                echo json_encode(array('rowid' => $model->getId(), 'sprice' => $price));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function inboundAction()
    {
        try {
            $oReports = explode(',', $this->getRequest()->getParam('inbounds'));
            foreach ($oReports as $oReport) {
                if (!$oReport) {
                    continue;
                }
                $qty = explode('_', $oReport);
                $track = Mage::getModel('gearup_sds/tracking');
                if ($track->load($qty[0], 'product_id')->getSdsTrackingId()) {
                    $track->setInbound($track->getInbound() + $qty[1]);
                    $track->save();
                } else {
                    $track->setProductId($qty[0]);
                    $track->setUpdateLastAt(date('Y-m-d H:i:s'));
                    $track->setOrderId(NULL);
                    $track->setInbound($track->getInbound() + $qty[1]);
                    $track->save();
                }
            }
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index')));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function inboundToSaveAction()
    {
        try {
            $inbounds = explode(',', $this->getRequest()->getParam('inbounds'));
            $error = false;
            $pError = array();
            foreach ($inbounds as $inbound) {
                if (!$inbound) {
                    continue;
                }
                $qty = explode('_', $inbound);
                $model = Mage::getModel('catalog/product')->load($qty[0]);
//                if (number_format((float)$model->getPrice(), 2, '.', '') == $qty[2]) {
//                    $pError[] = $model->getPartNr();
//                    $error = true;
//                }
            }
//            if ($error) {
//                header('Content-type: application/json');
//                echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index', array('inbound_filter'=>1, 'reset'=>1)), 'status' => 0, 'partnr' => $pError));
//                return false;
//            }
            foreach ($inbounds as $inbound) {
                if (!$inbound) {
                    continue;
                }
                $qty = explode('_', $inbound);
                $track = Mage::getModel('gearup_sds/tracking');
                if ($track->load($qty[0], 'product_id')->getSdsTrackingId()) {
                    $track->setInbound($qty[1]);
                    $track->save();
                }

//                $model = Mage::getModel('catalog/product')->load($qty[0]);
//                $model->setPrice($qty[2]);
//                $model->save();
            }
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index', array('inbound_filter'=>1, 'reset'=>1)), 'status' => 1));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    /* Ticket5492- 
       Add cost field into inboud grid.
       sepcial price shodld be store from inboud grid 
       Add price and cost into download csv file */
    public function inboundToStockAction()
    {
        try {
            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode(); 
            $inbounds = explode(',', $this->getRequest()->getParam('inbounds'));

            $report = array();
            $error = false;
            foreach ($inbounds as $inbound) {
                if (!$inbound) {
                    continue;
                }
                $qty = explode('_', $inbound);
                $model = Mage::getModel('catalog/product')->load($qty[0]);

                if (number_format((float)$model->getPrice(), 2, '.', '') == $qty[2]) {
                    $pError[] = $model->getPartNr();
                    $error = true;
                }
            }
            if ($error) {
                header('Content-type: application/json');
                echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index'), 'status' => 0, 'partnr' => $pError));
                return false;
            }
            foreach ($inbounds as $inbound) {
                if (!$inbound) {
                    continue;
                }
                $inboundre = explode('_', $inbound);

                $model = Mage::getModel('catalog/product')->load($inboundre[0]);

                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($inboundre[0]);
                $previousSds = Mage::helper('gearup_sds')->sdsStatus($model->getSameDayShipping());
                $prevoiusQty = round($stockItem->getData('qty'));
                if ($stockItem->getData('is_in_stock')) {
                    $previousStockStatus = 'In Stock';
                } else {
                    $previousStockStatus = 'Out of Stock';
                }
                if ($model->getSameDayShipping()) {
                    $qty = $stockItem->getQty() + $inboundre[1];
                } else {
                    $qty = $inboundre[1];
                    $model->setSameDayShipping(1);
                }
                $stockItem->setData('qty',$qty);
                $stockItem->setData('is_in_stock',1);
                $stockItem->save();
                $model->setPrice($inboundre[2]);
                $model->setCost($inboundre[3]);
                $model->setSpecialPrice($inboundre[4]);
                $model->save();
                Mage::helper('gearup_sds')->assignSDS($model->getId());
                if ($stockItem->getData('is_in_stock')) {
                    $nowStockStatus = 'In Stock';
                } else {
                    $nowStockStatus = 'Out of Stock';
                }
                $nowSds = Mage::helper('gearup_sds')->sdsStatus($model->getSameDayShipping());

                $track = Mage::getModel('gearup_sds/tracking')->load($inboundre[0], 'product_id');
                $track->setInbound(NULL);
                $track->save();
                $preQty = $stockItem->getData('qty') - $inboundre[1];
                $action = '"'.$prevoiusQty.' '.$previousStockStatus.'; SDS is '.$previousSds.'", '.$inboundre[1].' Pcs Inbounds to stock and set SDS to Yes, "'.$stockItem->getData('qty').' '.$nowStockStatus.'; SDS is '.$nowSds.'" ';
                Mage::helper('gearup_sds')->recordHistory($model->getId(), $action, $preQty, $stockItem->getData('qty'));
                $report[] = array(
                                'product_name'  => $model->getName(),
                                'part_number'   => $model->getPartNr(),
                                'inbound'       => $inboundre[1],
                                'price'       => $inboundre[2].' '.$baseCurrencyCode,
                                'cost'       => $inboundre[3].' '.$baseCurrencyCode
                            );
                Mage::getSingleton('core/session')->setLastReport($report);
            }
            Mage::getSingleton('core/session')->setInboundReport($report);
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index'), 'status' => 1));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }

    }

    public function exportInboundAction() {
        Mage::getModel('gearup_sds/tracking')->downloadLastInbound();
    }

    public function exportInboundNowAction() {
        try {
            $filename = 'Inbound Report.csv';
            $content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_export_inbound');
            $this->_prepareDownloadResponse($filename, $content->getCsvFile());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error exporting report.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index', array('inbound_filter'=>1, 'reset'=>1));
    }

    public function reloadstatAction() {
        $statistic = Mage::helper('gearup_sds')->getStatistic(Mage::getSingleton('core/session')->getDxbsCollection());
        $statisticWord = '<span class="statistic">Value: <span class="number">' . number_format($statistic['value'], 2) . '</span> USD  Product: <span class="number">' . number_format($statistic['product']) . '</span>  Quantity: <span class="number">' . number_format($statistic['qty']) . '</span></span>';
        header('Content-type: application/json');
        echo json_encode(array('statistic'=> $statisticWord));
    }

    public function popupqtyAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function storagepopupAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


    /*  Ticket5492 - DXB Storage Manager - Report In/Out 
        Update Cost from Grid */
    public function updateCostAction()
    {
        try {
            $fieldId = (int) $this->getRequest()->getParam('id');
            $cost = $this->getRequest()->getParam('cost');
            if ($fieldId) {
                $product = Mage::getModel('catalog/product')->load($fieldId);
                $product->setCost($cost);
                $product->save();

                header('Content-type: application/json');
                echo json_encode(array('rowid' => $product->getId()));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'gearupsds.log');
        }
    }

    public function getLastInboundReportAction(){
        try {
            $report = Mage::getSingleton('core/session')->getLastReport();
            Mage::getSingleton('core/session')->setInboundReport($report);
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/*/index'), 'status' => 1));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error in get last inbound report.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index/inbound_filter/1');
    }

    public function exportStorageAction(){
        try {
            $filename = 'DXB Storage Report.csv';
            $this->getRequest()->setParam('storage_filter',1);
            $this->getRequest()->setParam('startDate', $_GET['startDate']);           
            $this->getRequest()->setParam('endDate', $_GET['endDate']);
            //$content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_export_storage');
            $content  = $this->getLayout()->createBlock('gearup_sds/adminhtml_sds_grid');
            $this->_prepareDownloadResponse($filename, $content->getCsvFile());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_sds')->__('There was an error exporting report.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

}