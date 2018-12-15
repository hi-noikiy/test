<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('eshopsync/customer')
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
        return Mage::getSingleton('admin/session')->isAllowed('eshopsync/eshopsync_customer_menu');
  }

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('eshopsync/customer');
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

    public function getAllExportIdsAction()
    {
        $export_array = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if($client){
            $mapping_array = array();
            $model = Mage::getModel('eshopsync/customer');
            $mappingcollection =  $model->getCollection()
																	->addFieldToSelect('magento_id')
																	->addFieldToFilter('error_hints',array('null' => true));
            foreach ($mappingcollection as $map) {
                array_push($mapping_array, $map->getMagentoId());
            }
            if($mapping_array){
                $customer_collection = Mage::getModel('customer/customer')->getCollection()
                                            ->addAttributeToSelect('entity_id')
                                            ->addAttributeToFilter('entity_id',array('nin'=>$mapping_array));

            }else{
                $customer_collection = Mage::getModel('customer/customer')->getCollection()
                                                                ->addAttributeToSelect('entity_id');
            }

            $export_array = $customer_collection->getAllIds();

            if(count($export_array) == 0){
                Mage::getSingleton('adminhtml/session')->addNotice($this->__("All Magento Customer are already Exported at Salesforce!!!"));
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($export_array));
    }

    public function exportCustomerAction()
    {
        $params = $this->getRequest()->getParams();
        $response = false;
        $total = $params['total'];
        $counter = $params['counter'];
        $customer_id = $params['id'];
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if ($customer_id && $client) {
            $response = Mage::getModel("eshopsync/customer")->syncSpecificCustomer($client, $customer_id);
        }
        // if($counter == $total){
        //     Mage::getSingleton('adminhtml/session')->addSuccess($total.$this->__(" Customer has been successfully synchronized."));
        // }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('eshopsync/customer');

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
                    $eshopsync = Mage::getModel('eshopsync/customer')
																->getCollection()->addFieldToFilter('magento_id',array('in',$eshopsyncIds));
										foreach ($eshopsync as $recordCustomer) {
											$magento_id = $recordCustomer->getMagentoId();
											$contacts = Mage::getModel('eshopsync/contact')
																	->getCollection()->addFieldToFilter('customer_id',$magento_id);
											if(count($contacts)){
												foreach ($contacts as $contact) {
													$contact->delete();
												}
											}
											$recordCustomer->delete();
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
                    $eshopsync = Mage::getSingleton('eshopsync/customer')
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
        $fileName   = 'Eshop_Customer.csv';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_customer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'Eshop_Customer.xml';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_customer_grid')
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
