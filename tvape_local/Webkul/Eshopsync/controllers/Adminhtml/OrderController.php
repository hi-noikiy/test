<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
 {
		$this->loadLayout()
			->_setActiveMenu('eshopsync/order')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}

	protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('eshopsync/order');
  }

    public function indexAction()
    {
    	$this->_initAction()
    		->renderLayout();
			if(Mage::getStoreConfig('eshopsync/upload/upload_wsdl') == ""){
				Mage::getSingleton('adminhtml/session')->addError("SOAP client file not uploaded");
			}
    }

		public function getAllExportOrderIdsAction()
  {
        $export_ids = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            $map_orders = array();

            $order_collection = Mage::getModel('eshopsync/order')->getCollection()
                                                    ->addFieldToSelect('magento_id')
																										->addFieldToFilter('error_hints',array('null' => true));

            foreach ($order_collection as $value){
                array_push($map_orders, $value['magento_id']);
            }

            if ($map_orders){
                $collection = Mage::getModel('sales/order')->getCollection()
                                    ->addAttributeToFilter('entity_id', array('nin' => $map_orders))
                                    ->addAttributeToSelect('entity_id');
            }else{
                $collection = Mage::getModel('sales/order')->getCollection()
                                    ->addAttributeToSelect('entity_id');
            }
            $export_ids = $collection->getAllIds();
            if(count($export_ids) == 0){
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__("All Orders are Already exported at Salesforce."));
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($export_ids));
    }

		public function exportOrdersAction()
  {
        $response = 0;
        $params = $this->getRequest()->getParams();
        $order_id = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if ($client && $order_id) {
            $response = Mage::getModel("eshopsync/order")->exportSpecificOrder($client, $order_id);
        }
        // if($counter == $total){
        //     if($response){
        //         $success = $success + 1;
        //     }else{
        //         $failure = $failure + 1;
        //     }
        //     if($failure > 0){
        //         Mage::getSingleton('adminhtml/session')->addError($failure.$this->__(' Order(s) have not been Exported at Salesforce for more details check logs.'));
        //     }
        //     if($success > 0){
        //         Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Total %s Order(s) has been successfully Exported at Salesforce.",$success));
        //     }
        // }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }

    public function syncorderAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids',array());
        if(!is_array($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        }
        else {
            try {
                $order_model = Mage::getModel('eshopsync/order');
                $client = Mage::helper('eshopsync/connection')->getSforceConnection();
                $connection = Mage::getSingleton('adminhtml/session')->getConnection();
                if($client){
                    $count = 0;
                    $fail_ids = '';
                    sort($orderIds);
                    foreach ($orderIds as $order_id) {
                        $This_order = Mage::getModel('sales/order')->load($order_id);
                        if($This_order->getStatus() == 'canceled'){
                            continue;
                        }
                        $increment_id = $This_order->getIncrementId();
                        $ordercollection = $order_model->getCollection()
                                        ->addFieldToFilter('magento_id',array('eq'=>$order_id));
                        if (!$ordercollection->getSize()){
                            $response = $order_model->exportSpecificOrder($client, $order_id);
                            if($response){
                                $count = $count + 1;
                            }else{
                                $fail_ids .= $increment_id.',';
                            }
                        }else{
                            $this->_getSession()->addError($this->__("Magento Order(s) %d are already synced.",$increment_id));
                        }
                    }
                    if($count){
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('adminhtml')->__(
                                $this->__('Total of %d record(s) were successfully synchronized at salesforce.'), $count)
                            );
                    }
                    if($fail_ids){
                        Mage::getSingleton('adminhtml/session')->addError($this->__("Sync Failed, Order(s) %s. For More Details See eshopsync_connector.log file.",rtrim($fail_ids,',')));
                    }
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/sales_order/index');
    }

	public function saveAction()
 {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('eshopsync/order');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));

			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('eshopsync')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('eshopsync')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('eshopsync/order');

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction()
    {
        $eshopsyncIds = $this->getRequest()->getParam('eshopsync');
        if(!is_array($eshopsyncIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                //foreach ($eshopsyncIds as $eshopsyncId) {
                    $eshopsync = Mage::getModel('eshopsync/order')
																->getCollection()->addFieldToFilter('magento_id',array('in',$eshopsyncIds));
                    foreach ($eshopsync as $record) {
                    	$record->delete();
                    }
                //}
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($eshopsyncIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $eshopsyncIds = $this->getRequest()->getParam('eshopsync');
        if(!is_array($eshopsyncIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($eshopsyncIds as $eshopsyncId) {
                    $eshopsync = Mage::getSingleton('eshopsync/order')
                        ->load($eshopsyncId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($eshopsyncIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'eshop_order.csv';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_order_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'eshop_order.xml';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_order_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        //die;
    }
}
