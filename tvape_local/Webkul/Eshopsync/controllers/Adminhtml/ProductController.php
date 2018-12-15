<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('eshopsync/product')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction()
			->renderLayout();
		if(Mage::getStoreConfig('eshopsync/upload/upload_wsdl') == ""){
			Mage::getSingleton('adminhtml/session')->addError("SOAP client file not uploaded");
		}
	}

	protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('eshopsync/product');
  }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('eshopsync/product')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('eshopsync_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('eshopsync/product');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('eshopsync/adminhtml_product_edit'))
				->_addLeft($this->getLayout()->createBlock('eshopsync/adminhtml_product_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('eshopsync')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('eshopsync/product');
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
				$model = Mage::getModel('eshopsync/product');

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

    public function massDeleteAction() {
        $eshopsyncIds = $this->getRequest()->getParam('eshopsync');
        if(!is_array($eshopsyncIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                //foreach ($eshopsyncIds as $eshopsyncId) {
                    $eshopsync = Mage::getModel('eshopsync/product')
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
							$eshopsync =Mage::getModel('eshopsync/product')
													->getCollection()->addFieldToFilter('magento_id',array('in',$eshopsyncIds));
							foreach ($eshopsync as  $record) {
								$record->setNeedSync($this->getRequest()->getParam('status'))
                       ->save();
							}
                // foreach ($eshopsyncIds as $eshopsyncId) {
                //     $eshopsync = Mage::getSingleton('eshopsync/product')
                //         ->load($eshopsyncId)
                //         ->setNeedSync($this->getRequest()->getParam('status'))
                //         ->save();
                // }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($eshopsyncIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /*
        Fetching Product Export Ids
    */
    public function getAllExportProductIdsAction()
    {
        $export_ids = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            $map_products = array();

            $product_collection = Mage::getModel('eshopsync/product')->getCollection()
                                                    ->addFieldToSelect('magento_id')
																										->addFieldToFilter('error_hints',array('null' => true));

            foreach ($product_collection as $value){
                array_push($map_products, $value['magento_id']);
            }

            if ($map_products){
                $collection = Mage::getModel('catalog/product')->getCollection()
                                    ->addAttributeToFilter('entity_id', array('nin' => $map_products))
                                    ->addAttributeToSelect('entity_id');
            }else{
                $collection = Mage::getModel('catalog/product')->getCollection()
                                    ->addAttributeToSelect('entity_id');
            }
            $export_ids = $collection->getAllIds();
            if(count($export_ids) == 0){
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__("All Products are Already exported at Salesforce."));
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($export_ids));
    }

    public function exportProductsAction()
    {
        $response = 0;
        $params = $this->getRequest()->getParams();
        $product_id = $params['id'];
        $total = $params['total'];
        $counter = $params['counter'];
        $failure = $params['failure'];
        $success = $params['success'];
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if ($client && $product_id) {
            $response = Mage::getModel("eshopsync/product")->syncSpecificProduct($client, $product_id);
        }
        // if($counter == $total){
        //     if($response){
        //         $success = $success + 1;
        //     }else{
        //         $failure = $failure + 1;
        //     }
        //     if($failure > 0){
        //         Mage::getSingleton('adminhtml/session')->addError($failure.$this->__(' Product(s) have not been Exported at Salesforce for more details check logs.'));
        //     }
        //     if($success > 0){
        //         Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Total %s Product(s) has been successfully Exported at Salesforce.",$success));
        //     }
        // }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }

    /*
        Fetching Product Update Ids......
    */

    public function getAllUpdateIdsAction()
    {
        $update_product_ids = array();
        $update_check_ids = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            $mappingcollection = Mage::getModel('eshopsync/product')->getCollection();
            $update_check_ids = $mappingcollection->getAllIds();
            if(count($update_check_ids) == 0){
                Mage::getSingleton('adminhtml/session')->addError($this->__("Sorry, No Magento Categories are found to updated at salesforce."));
            }
            else{
                $product_collection = Mage::getModel('eshopsync/product')->getCollection()
                                                ->addFieldToFilter('need_sync',array('eq'=>'yes'));

                $update_product_ids = $product_collection->getAllIds();
                if(count($update_product_ids) == 0){
                    Mage::getSingleton('adminhtml/session')->addNotice($this->__("All Magento Categories are already updated at salesforce."));
                }
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $response = array('prod_ids'=>$update_product_ids);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /*
        Category Update Action Called Via Ajax...
     */
    public function updateProductAction()
    {
        $error = '';
        $response = false;
        $failure_ids = 0;
        $mapping_id = $this->getRequest()->getParam("id");
        $total = $this->getRequest()->getParam("total");
        $counter = $this->getRequest()->getParam("counter");
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if($client && $mapping_id){
            $mapping_collection = Mage::getModel("eshopsync/product")->getCollection()
                                        ->addFieldToFilter('entity_id',array('eq'=>$mapping_id));

            if($mapping_collection->getSize()){
                $mapping = $mapping_collection->getFirstItem();
                $product_id = $mapping->getMagentoId();
                $response = Mage::getModel("eshopsync/product")->syncSpecificProduct($client, $product_id, "Update", $mapping_id);
            }
        }
        if($counter == $total){
            Mage::getSingleton('adminhtml/session')->addSuccess($total.$this->__(" Product(s) has been successfully updated at salesforce."));
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }

    public function exportCsvAction()
    {
        $fileName   = 'eshopsync_products.csv';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_product_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'eshopsync_products.xml';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_product_grid')
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
