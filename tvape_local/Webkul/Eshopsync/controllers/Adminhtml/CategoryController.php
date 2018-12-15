<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('eshopsync/category')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}
	protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('eshopsync/category');
  }

    /****
    @function: will test the salesforce connection
    @extra: will fetch all pricebook and folders
    */
		public function testConnectionAction(){
			$data = $this->getRequest()->getParams();
			$user = $data['user'];
			$pwd = $data['pwd'];
			$token = $data['token'];
			$clientData = $data['client'];
			$client =  Mage::helper('eshopsync/connection')->getTestConnection($user,$pwd,$token,$clientData);
			$connection = Mage::getSingleton('adminhtml/session')->getConnection();

			if($client){
				Mage::getModel('core/config')->saveConfig('eshopsync/setting/user',$data['user']);
				Mage::getModel('core/config')->saveConfig('eshopsync/setting/pwd',$data['pwd']);
				Mage::getModel('core/config')->saveConfig('eshopsync/setting/token',$data['token']);
				Mage::getModel('core/config')->saveConfig('eshopsync/setting/client',$data['client']);
				// Mage::getModel('core/config')->cleanCache();
				Mage::app()->getConfig()->reinit();

					Mage::getSingleton('adminhtml/session')->addSuccess($connection);

					/* Fetching salesforce folders*/
					$folders = Mage::getModel('eshopsync/folder')->fetchSalesforceFolders($client);
					if($folders){
							Mage::getSingleton('adminhtml/session')->addSuccess("Salesforce folders are successfully fetched!!!");
					}else{
							Mage::getSingleton('adminhtml/session')->addError("Error while fetching salesforce folders!!!");
					}
					/*End Fetching Folders*/

					/* Fetching salesforce Pricebooks*/
					$pricebook = Mage::getModel('eshopsync/pricebook')->fetchSalesforcePricebooks($client);
					if($pricebook){
							Mage::getSingleton('adminhtml/session')->addSuccess("Salesforce pricebooks are successfully fetched!!!");
					}else{
							Mage::getSingleton('adminhtml/session')->addError("Error while fetching salesforce pricebooks!!!");
					}
					/*End Fetching Folders*/

					Mage::getModel('core/config')->saveConfig('eshopsync/setting/status', "Successfully Connected");
			}else{
					Mage::getSingleton('adminhtml/session')->addError($connection);
					Mage::getModel('core/config')->saveConfig('eshopsync/setting/status', "Not Connected");
			}

			// Mage::getConfig()->cleanCache();
			// $this->_redirect('adminhtml/system_config/edit/section/eshopsync');

		}

    public function sforceConnectionAction() {
        $client =  Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            Mage::getSingleton('adminhtml/session')->addSuccess($connection);

            /* Fetching salesforce folders*/
            $folders = Mage::getModel('eshopsync/folder')->fetchSalesforceFolders($client);
            if($folders){
                Mage::getSingleton('adminhtml/session')->addSuccess("Salesforce folders are successfully fetched!!!");
            }else{
                Mage::getSingleton('adminhtml/session')->addError("Error while fetching salesforce folders!!!");
            }
            /*End Fetching Folders*/

            /* Fetching salesforce Pricebooks*/
            $pricebook = Mage::getModel('eshopsync/pricebook')->fetchSalesforcePricebooks($client);
            if($pricebook){
                Mage::getSingleton('adminhtml/session')->addSuccess("Salesforce pricebooks are successfully fetched!!!");
            }else{
                Mage::getSingleton('adminhtml/session')->addError("Error while fetching salesforce pricebooks!!!");
            }
            /*End Fetching Folders*/

            Mage::getModel('core/config')->saveConfig('eshopsync/setting/status', "Successfully Connected");
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
            Mage::getModel('core/config')->saveConfig('eshopsync/setting/status', "Not Connected");
        }
        Mage::getConfig()->cleanCache();
        $this->_redirect('adminhtml/system_config/edit/section/eshopsync');
    }

    /****
    @function: will reset all salesforce mapping table
    */
    public function resetMappingAction()
    {
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/folder');
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/pricebook');
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/category');
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/product');
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/customer');
        Mage::helper('eshopsync')->deleteMappingData('eshopsync/order');
        Mage::getSingleton('adminhtml/session')->addSuccess("All Salesforce mapping data has been successfully deleted, now you can configure new salesforce api.");
        Mage::getConfig()->cleanCache();
        $this->_redirect('adminhtml/system_config/index');
    }

    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
				if(Mage::getStoreConfig('eshopsync/upload/upload_wsdl') == ""){
					Mage::getSingleton('adminhtml/session')->addError("SOAP client file not uploaded");
				}
    }

    public function editAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('eshopsync/category')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('eshopsync_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('eshopsync/category');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('eshopsync/adminhtml_category_edit'))
                ->_addLeft($this->getLayout()->createBlock('eshopsync/adminhtml_category_edit_tabs'));

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

            $model = Mage::getModel('eshopsync/category');
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


    /*
        Fetching Category Export Ids
     */
    public function getAllExportIdsAction()
    {
        $sync_categories = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            $mapping_collection = Mage::getModel('eshopsync/category')->getCollection()
                                                    ->addFieldToSelect('magento_id')
																										->addFieldToFilter('error_hints',array('null' => true));
						// echo '<pre>'; print_r($mapping_collection->getData()); die;
            $mapped_categories = array();
            foreach ($mapping_collection as $mapping){
                array_push($mapped_categories, $mapping['magento_id']);
            }
            if($mapped_categories){
                $category_collection = Mage::getModel('catalog/category')->getCollection()
                                    ->addAttributeToSelect('entity_id')
                                    ->addAttributeToFilter('level', array('gt' => 0))
                                    ->addAttributeToFilter('entity_id', array('nin' => $mapped_categories));
            }else{
                $category_collection = Mage::getModel('catalog/category')->getCollection()
                                                            ->addAttributeToSelect('entity_id')
                                                            ->addAttributeToFilter('level', array('gt' => 0));
            }
            $export_ids = $category_collection->getAllIds();
            if(!$category_collection->getSize()){
                Mage::getSingleton('adminhtml/session')->addNotice($this->__("All Magento Categories are already exported at salesforce."));
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $response = array('categ_ids'=>$export_ids);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function exportCategoryAction()
    {
        //$response = 0;
				$response = array();
        $total = $this->getRequest()->getParam("total");
        $counter = $this->getRequest()->getParam("counter");
        $failure = $this->getRequest()->getParam("failure");
        $success = $this->getRequest()->getParam("success");
        $category_id = $this->getRequest()->getParam("id");
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if($client && $category_id){
            $response = Mage::getModel('eshopsync/category')->exportSpecificCategory($client, $category_id);
        }
        //if($counter == $total){
						// $session = Mage::getSingleton('core/session');
            // if($response['error'] == 0){
						// 	if($session->getData('succ')){
						// 		$succ = $session->getData('succ');
						// 	}
            //   $succ = $succ + 1;
						// 	$session->setData('succ',$succ);
            // }else{
						// 	if($session->getData('fail')){
						// 		$fail = $session->getData('fail');
						// 	}
						// 	$fail = $fail + 1;
						// 	$session->setData('fail',$fail);
            // }
            // if($session->getData('fail')){
            //     Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to export '.$session->getData('fail').' category(s) at salesforce for more details check salesforce_connector.log'));
            // }
            // if($session->getData('succ')){
            //     Mage::getSingleton('adminhtml/session')->addSuccess($session->getData('succ').$this->__(" category(s) has been successfully Exported at salesforce."));
            // }
						// $session->unsetData('succ');
						// $session->unsetData('fail');
        //}
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }


    /*
        Fetching Category Update Ids
    */

    public function getAllUpdateIdsAction()
    {
        $update_category_ids = array();
        $update_check_category_ids = array();
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        $connection = Mage::getSingleton('adminhtml/session')->getConnection();
        if($client){
            $mappingcollection = Mage::getModel('eshopsync/category')->getCollection();
            $update_check_category_ids = $mappingcollection->getAllIds();
            if(count($update_check_category_ids) == 0){
                Mage::getSingleton('adminhtml/session')->addError($this->__("Sorry, No Magento Categories are found to updated at salesforce."));
            }
            else{
                $category_collection = Mage::getModel('eshopsync/category')->getCollection()
                                                ->addFieldToFilter('need_sync',array('eq'=>'yes'));

                $update_category_ids = $category_collection->getAllIds();
                if(count($update_category_ids) == 0){
                    Mage::getSingleton('adminhtml/session')->addNotice($this->__("All Magento Categories are already updated at salesforce."));
                }
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($connection);
        }
        $response = array('categ_ids'=>$update_category_ids);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /*
        Category Update Action Called Via Ajax...
     */
    public function updateCategoryAction()
    {
        $error = '';
        $response = false;
        $failure_ids = 0;
        $mapping_id = $this->getRequest()->getParam("id");
        $total = $this->getRequest()->getParam("total");
        $counter = $this->getRequest()->getParam("counter");
        $client = Mage::helper('eshopsync/connection')->getSforceConnection();
        if($client && $mapping_id){
            $response = Mage::getModel('eshopsync/category')->updateSpecificCategory($client, $mapping_id);
        }
        if($counter == $total){
            Mage::getSingleton('adminhtml/session')->addSuccess($total.$this->__(" Category(s) has been successfully updated at salesforce."));
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('response'=>$response)));
    }

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('eshopsync/category');

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
			$ids = array();
        $eshopsyncIds = $this->getRequest()->getParam('eshopsync'); //echo '<pre>'; print_r($eshopsyncIds); die;
        if(!is_array($eshopsyncIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                    $eshopsync =Mage::getModel('eshopsync/category')
																->getCollection()->addFieldToFilter('magento_id',array('in',$eshopsyncIds));
										foreach ($eshopsync as  $record) {
											$record->delete();
										}
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
							$eshopsync =Mage::getModel('eshopsync/category')
													->getCollection()->addFieldToFilter('magento_id',array('in',$eshopsyncIds));
							foreach ($eshopsync as  $record) {
								$record->setNeedSync($this->getRequest()->getParam('status'))
                       ->save();
							}
                // foreach ($eshopsyncIds as $eshopsyncId) {
                //     $eshopsync = Mage::getSingleton('eshopsync/category')
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

    public function exportCsvAction()
    {
        $fileName   = 'Eshop_Category.csv';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_category_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'Eshop_Category.xml';
        $content    = $this->getLayout()->createBlock('eshopsync/adminhtml_category_grid')
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
