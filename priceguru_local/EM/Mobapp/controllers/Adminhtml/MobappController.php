<?php
class EM_Mobapp_Adminhtml_MobappController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage');
                break;
        }
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('mobapp/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}

	public function indexAction() {
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage')){
			$generalinfo = Mage::helper("mobapp")->getGeneralInfo();
			Mage::register('mobapp_generalinfo', $generalinfo);

			$this->_initAction()
				->renderLayout();
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
			$this->_redirect('*/dashboard/');
		}
	}

	public function newAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('mobapp/store')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			$model->setData('google_id',Mage::helper('mobapp')->__('please insert your Google analytics id'));
			$model->setData('language',1);
			$model->setData('release',2);
			$model->setData('register',Mage::helper('mobapp')->__("Submit"));
			$generalinfo = Mage::helper("mobapp")->getGeneralInfo();

			Mage::register('mobapp_generalinfo', $generalinfo);
			Mage::register('mobapp_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('mobapp/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit'))
				->_addLeft($this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('App does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function editAction() {
		if($this->getRequest()->getPost()){
			$check	=	$this->getRequest()->getPost('check');
			$app	=	$this->getRequest()->getPost('app_license');
			if($check == "ok" && $app) $this->checklicense();
			return;
		}
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('mobapp/store')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			$generalinfo = Mage::helper("mobapp")->getGeneralInfo();

			Mage::register('mobapp_generalinfo', $generalinfo);
			Mage::register('mobapp_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('mobapp/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			
			if($model->getStatus() == 0)
				$this->_addContent($this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit'))
					->_addLeft($this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tabs2'));
			else
				$this->_addContent($this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit'))
					->_addLeft($this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('App does not exist'));
			$this->_redirect('*/*/');
		}
	}

	protected function checklicense() {
		if ($data = $this->getRequest()->getPost()) {
			$app	=	$this->getRequest()->getPost('app_license');
			$id		=	$this->getRequest()->getPost('id_app');
			if($app	== "123456"){
			
				$info['license']	=	$app;
				$info['status']		=	1;
				$model = Mage::getModel('mobapp/store');
				$model->setData($info)
					->setId($id);

				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	

				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mobapp')->__('Register success'));
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('License not valid'));
			}
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
	
	public function changeAction() {
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/save')){
			if ($data = $this->getRequest()->getPost()) {
				//echo '<pre>';print_r($_FILES);
				//echo '<pre>';print_r($data);exit;

				if($data['color'])
					$info['color']		=	$data['color'];
				if($data['paygate'])
					$info['paygate']	=	Mage::helper("mobapp")->jsonEncode($data['paygate']);

				for($i=1;$i<=4;$i++){
					if($i == 1){	$str = "";$str2 = "";}
					else{	$str = "_".$i;$str2 = $i;}
					$path = Mage::getBaseDir('media') . DS . 'em_mobapp' . DS . 'slideshow'.$str2 . DS;

					$old_img	=	$data['deloldimg'.$str2];
					$plit	=	explode("_!_",$old_img);
					if(count($plit) > 1){
						foreach($plit as $pkey=>$pval){
							if($pkey > 0){
								if(is_file($path.$pval))
									unlink($path.$pval);
								/* Remove old image resize */
								if(is_file($path. 'resize' . DS . '100x60' . DS . $pval))
									unlink($path. 'resize' . DS . '100x60' . DS . $pval);
							}
						}
					}

					$tmp_img	=	$data['images'.$str];
					foreach($_FILES['files'.$str]['type'] as $key=>$val){
						$filecheck = basename($_FILES['files'.$str]['name'][$key]);
						$ext = strtolower(substr($filecheck, strrpos($filecheck, '.') + 1));
						
						if (!(($ext == "jpg" || $ext == "gif" || $ext == "png") && ($val == "image/jpg" ||$val == "image/jpeg" || $val == "image/gif" || $val == "image/png") && ($_FILES["files".$str]["size"][$key] < 3000000) )){
							if($tmp_img[$key]['url'] == "")
								unset($tmp_img[$key]);
						}else{
							if ($_FILES["files".$str]["error"][$key] == UPLOAD_ERR_OK) {

								$date	=	getdate();
								$file_name	=	$date[0].'_'.$_FILES["files".$str]["name"][$key];
								$file_name	=	preg_replace('/[^a-zA-Z0-9\-_.]/','',$file_name);
								$tmp_img[$key]['url']	=	$file_name;

								$tmp_file	=	 $_FILES["files".$str]["tmp_name"][$key];
								move_uploaded_file($tmp_file, "$path/$file_name");
							}
						}
					}
					foreach ($tmp_img as $key => $row) {
						$position[$key]  = intval($row['position']);
					}
					array_multisort($position, SORT_ASC, $tmp_img);
					$info['slideshow'.$str2]	=	Mage::helper("mobapp")->jsonEncode($tmp_img);
				}
				//echo '<pre>';print_r($info);exit;

				$model = Mage::getModel('mobapp/store');
				$model->setData($info)
					->setId($this->getRequest()->getParam('id'));

				try {
					if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
						$model->setCreatedTime(now())
							->setUpdateTime(now());
					} else {
						$model->setUpdateTime(now());
					}	

					$model->save();

					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mobapp')->__('Mobapp was successfully saved'));
					Mage::getSingleton('adminhtml/session')->setFormData(false);

					if ($this->getRequest()->getParam('back')) {
						$this->_redirect('*/*/edit', array('id' => $model->getId()));
						return;
					}
					$this->_redirect('*/*/');
					return;
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setFormData($info);
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}
			}
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('Unable to find item to save'));
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
		}
        $this->_redirect('*/*/');
	}

	public function saveAction() {
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/save')){
			if ($data = $this->getRequest()->getPost()) {
				$info['name']		=	$data['name'];
				$info['status']		=	1;
				$tmp = array();
				if($data['color']){
					$arr2['code']  = $data['color'];
					$arr2['label'] = Mage::helper("mobapp")->__('Color Theme');
					$tmp[]	= $arr2;
				}
				$info['theme'] = Mage::helper("mobapp")->jsonEncode($tmp);
				$info['color'] = $arr2['label'];

				$send	=	$this->custommail($data);
				if($send == 'error'){
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('Error format image. Please upload again'));
					Mage::getSingleton('adminhtml/session')->setFormData($data);
					$this->_redirect('*/*/new');
					return;
				}

				$model = Mage::getModel('mobapp/store');
				$model->setData($info)
					->setId($this->getRequest()->getParam('id'));
				
				try {
					if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
						$model->setCreatedTime(now())
							->setUpdateTime(now());
					} else {
						$model->setUpdateTime(now());
					}

					$model->save();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mobapp')->__('Register success').Mage::getStoreConfig('trans_email/ident_general/email'));
					Mage::getSingleton('adminhtml/session')->setFormData(false);

					$this->sendmail($model->getId(),$send);
					//echo '<pre>';print_r($send);exit;
					$this->_redirect('*/*/');
					return;
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setFormData($data);
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}
			}
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__('Unable to find item to save'));
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
		}
        $this->_redirect('*/*/');
	}
	
	public function custommail($data){

		if($data['sl_icon'] == 0)
			$data['sl_icon'] =	"Default";
		else{
			$data['sl_icon'] =	"Custom";
			$icon = $this->uploadfile('img_icon');
			if($icon == 'error') return 'error';
			$data['sl_icon_link'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/icon/'.$icon;
		}

		if($data['sl_logo'] == 0)
			$data['sl_logo'] =	"Default";
		else{
			$data['sl_logo'] =	"Custom";
			$logo = $this->uploadfile('img_logo');
			if($logo == 'error' ) return 'error';
			$data['sl_logo_link'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/logo/'.$logo;
		}

		if($data['sl_splash'] == 0)
			$data['sl_splash'] =	"Default";
		else{
			$data['sl_splash'] =	"Custom";
			$splash_1 = $this->uploadfile('img_splash_1');
			if($splash_1 == 'error' ) return 'error';
			$splash_2 = $this->uploadfile('img_splash_2');
			if($splash_2 == 'error' ) return 'error';
			$splash_3 = $this->uploadfile('img_splash_3');
			if($splash_3 == 'error') return 'error';
			//$splash_4 = $this->uploadfile('img_splash_4');
			//if($splash_4 == 'error') return 'error';
			$splash_5 = $this->uploadfile('img_splash_5');
			if($splash_5 == 'error') return 'error';
			//$splash_6 = $this->uploadfile('img_splash_6');
			//if($splash_6 == 'error') return 'error';
			$splash_7 = $this->uploadfile('img_splash_7');
			if($splash_7 == 'error') return 'error';
			$splash_8 = $this->uploadfile('img_splash_8');
			if($splash_8 == 'error') return 'error';

			$data['sl_splash_link_1'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_1/'.$splash_1;
			$data['sl_splash_link_2'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_2/'.$splash_2;
			$data['sl_splash_link_3'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_3/'.$splash_3;
			//$data['sl_splash_link_4'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_4/'.$splash_4;
			$data['sl_splash_link_5'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_5/'.$splash_5;
			//$data['sl_splash_link_6'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_6/'.$splash_6;
			$data['sl_splash_link_7'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_7/'.$splash_7;
			$data['sl_splash_link_8'] =	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/upload/splash_8/'.$splash_8;
		}

		//echo '<pre>';print_r($data);exit;
		return $data;
	}
	
	protected function uploadfile($filename) {
		if($filename == "img_icon")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "icon" . DS ;
		elseif($filename == "img_logo")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "logo" . DS ;
		elseif($filename == "img_splash_1")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_1" . DS ;
		elseif($filename == "img_splash_2")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_2" . DS ;
		elseif($filename == "img_splash_3")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_3" . DS ;
		//elseif($filename == "img_splash_4")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_4" . DS ;
		elseif($filename == "img_splash_5")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_5" . DS ;
		//elseif($filename == "img_splash_6")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_6" . DS ;
		elseif($filename == "img_splash_7")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_7" . DS ;
		elseif($filename == "img_splash_8")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "splash_8" . DS ;
		elseif($filename == "tabb_home")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "tab_home" . DS ;
		elseif($filename == "tabb_shop")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "tab_shop" . DS ;
		elseif($filename == "tabb_search")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "tab_search" . DS ;
		elseif($filename == "tabb_cart")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "tab_cart" . DS ;
		elseif($filename == "tabb_account")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "tab_account" . DS ;
		elseif($filename == "upload_lang")	$path = Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS . "csv" . DS;
		else	return false;

		if(!is_dir(Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload" . DS))
			mkdir(Mage::getBaseDir('media') . DS . "em_mobapp" . DS . "upload", 0777);
		if(!is_dir($path))	mkdir($path, 0777);

		if(isset($_FILES[$filename]['name']) && $_FILES[$filename]['name'] != '') {
			try {
				$_FILES[$filename]['name']	=	preg_replace('/[^a-zA-Z0-9\-_.]/','',$_FILES[$filename]['name']);
				/* Starting upload */
				$uploader = new Varien_File_Uploader($filename);

				// Any extention would work
				if($filename == "upload_lang")
					$uploader->setAllowedExtensions(array('csv'));
				else
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
				
				$uploader->setAllowRenameFiles(false);

				// Set the file upload mode 
				// false -> get the file directly in the specified folder
				// true -> get the file in the product like folders 
				//	(file.jpg will go in something like /media/f/i/file.jpg)
				$uploader->setFilesDispersion(false);

				// We set media as the upload dir
				$uploader->save($path, $_FILES[$filename]['name']);

			} catch (Exception $e) {
				return 'error';
			}
			//this way the name is saved in DB
			return 	$_FILES[$filename]['name'];
		}
	}
	
	protected function sendmail($id,$data){
		$helper = Mage::helper("mobapp");
		$emailTemplate  = Mage::getModel('core/email_template')
								->loadDefault('custom_email_mobapp');

		$emailTemplateVariables = array();
		$emailTemplateVariables = $data;
		$emailTemplateVariables['id_app'] = $id;
		$emailTemplateVariables['tmp_name'] = $data['name'];

		$count = count($data['platform']);
		$tmp = "";
		foreach($data['platform'] as $key=>$value){
			if($key == $count-1) $tmp .= $value;else $tmp .= $value.',&nbsp;';
		}
		$emailTemplateVariables['platform_html'] = $tmp;
		if($data['release'] == 2)	$emailTemplateVariables['check_release'] = 1;

		//echo '<pre>';print_r($emailTemplateVariables);exit;
		$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

		$emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
		$emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
		$emailTemplate->setTemplateSubject(Mage::helper('adminhtml')->__('Register Mobapp Store'));

		$send_name = $helper->getMailRegister_EmailName();
		$send_address = $helper->getMailRegister_EmailAddress();
		$generalinfo = $helper->getGeneralInfo();
		if($generalinfo['contact_name'] != "")
			$send_name 		= $generalinfo['contact_name'];
		if($generalinfo['contact_email'] != "")
			$send_address 	= $generalinfo['contact_email'];

		return $emailTemplate->send($send_address, $send_name, $emailTemplateVariables);
	}

	public function deleteAction() {
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/delete')){
			if( $this->getRequest()->getParam('id') > 0 ) {
				try {
					$model = Mage::getModel('mobapp/mobapp');
					 
					$model->setId($this->getRequest()->getParam('id'))
						->delete();
						 
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
					$this->_redirect('*/*/');
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				}
			}
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/delete')){
			$mobappIds = $this->getRequest()->getParam('mobapp');
			if(!is_array($mobappIds)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
			} else {
				try {
					foreach ($mobappIds as $mobappId) {
						$mobapp = Mage::getModel('mobapp/mobapp')->load($mobappId);
						$mobapp->delete();
					}
					Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('adminhtml')->__(
							'Total of %d record(s) were successfully deleted', count($mobappIds)
						)
					);
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
		}
        $this->_redirect('*/*/index');
    }

    public function massStatusAction(){
		if(Mage::getSingleton('admin/session')->isAllowed('emthemes/mobapp/manage/save')){
			$mobappIds = $this->getRequest()->getParam('mobapp');
			if(!is_array($mobappIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			} else {
				try {
					foreach ($mobappIds as $mobappId) {
						$mobapp = Mage::getSingleton('mobapp/mobapp')
							->load($mobappId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
					}
					$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully updated', count($mobappIds))
					);
				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobapp')->__("You don't have permission to save item. Maybe this is a demo store."));
		}
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'mobapp.csv';
        $content    = $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_grid')
            ->getCsv();
		//echo '<pre>';print_r($content);exit;
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'mobapp.xml';
        $content    = $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_grid')
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
        die;
    }
}