<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_fileuploader
 * @copyright   Copyright (C)2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

class Justselling_Configurator_UploadifyController extends Mage_Core_Controller_Front_Action
{

    protected function getUniqueFilePrefix() {
        $prefix = "";
        for ($i=0; $i<10; $i++) {
            $prefix .= (string)rand(0,9);
        }
        return $prefix;
    }

	public function uploadifyAction(){
		// Load propper session
		$session_name = "frontend";
		if (!isset($_POST[$session_name])) {
			exit;
		}
		$session_id =  $_POST[$session_name];

		$tmpFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp';
		$thumbsFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp'.DS."thumbs";
		$targetFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp'.DS.$session_id.DS;
        $order_id = null;
        if (isset($_POST['order_id'])) {
            $order_id = $_POST['order_id'];
            if ($order_id) { // This is a customer upload in customer area
                $targetFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS;
            }
        }

		if (!file_exists(str_replace('//','/',$tmpFolder))){
			mkdir(str_replace('//','/',$tmpFolder), 0755, true);
		}
		if (!file_exists(str_replace('//','/',$thumbsFolder))){
			mkdir(str_replace('//','/',$thumbsFolder), 0755, true);
		}
		if (!file_exists(str_replace('//','/',$targetFolder))){
			mkdir(str_replace('//','/',$targetFolder), 0755, true);
		}

		$verifyToken = md5('unique_salt' . $_POST['timestamp']);

		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			Mage::Log("Token is valid");
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/';
			if ($order_id) {
				$targetFile .= $order_id."_";
			}
            $targetFile .= $this->getUniqueFilePrefix()."_";
			$targetFile .= $_FILES['Filedata']['name'];

			// Validate the file type
			$fileTypes = explode(";",Mage::getStoreConfig('fileuploader/general/filetype'));
			foreach ($fileTypes as $key => $value)
				$fileTypes[$key] = trim($fileTypes[$key]);
			$fileParts = pathinfo($_FILES['Filedata']['name']);

			if (true || in_array($fileParts['extension'],$fileTypes)) {
				Mage::Log("filetype is allowed");
				move_uploaded_file($tempFile,$targetFile);

				$optionId = $_POST['optionId'];
				$jsTemplateId = $_POST['jsTemplateId'];

				if (!file_exists(str_replace('//','/',$targetFolder))){
					mkdir(str_replace('//','/',$targetPath.DS.'tmp'.DS."thumbs".DS.$session_id), 0755, true);
				}
				$file = str_replace(Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp'.DS, "", $targetFile);
				Mage::Log("file is".$file);
				if(isset($_POST['uploadId']) && $_POST['uploadId']){
					$upload = Mage::getModel('configurator/upload')->load($_POST['uploadId']);
					$upload->setFile($file);
					$upload->setStatus('1');
					$upload->save();
				}else{
					$upload = Mage::getModel('configurator/upload');
					$upload->setSessionId($session_id);
					$upload->setFile($file);
					$upload->setStatus('1');
					$upload->setOptionId($optionId);
					$upload->setJsTemplateId($jsTemplateId);
					$upload->setCreatedAt(new Zend_Date());
					$upload->save();
				}

				// Create Thumbnail
				if (in_array($fileParts['extension'],array('png','gif','jpg','jpeg'))) {
					$thumb = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp'.DS."thumbs".DS.$file;
					$image = new Varien_Image($targetFile);
					$image->keepAspectRatio(true);
					$image->resize(80,80);
					$image->save($thumb);
					$image_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp'.DS."thumbs".DS.$file;
				} else {
					// Mage::Log("it is not image");
					$image_url = Mage::helper('configurator')->getUploadImage($file);
				}

				$html = '<div class="uploadifyimgwrapper"><img id="uplodifyimg' .$upload->getId() .'" src="'.$image_url.'"/>';
				$html .= '<a class="uploadifytag" id="uploadifytag' .$upload->getId()  .'" href="#" ></a></div>';
				$this->getResponse()->setBody($html);
			} else {
				$this->getResponse()->setBody('Invalid file type');
			}
		}
	}

	public function uploadifyadminAction(){
		// Define a destination
		$tmpFolder = Mage::getBaseDir('media') .DS.'tmp';
		$uploadFolder = Mage::getBaseDir('media') .DS.'tmp'.DS."upload";
		$targetFolder = Mage::getBaseDir('media') .DS.'tmp'.DS.'upload'.DS.'admin';

		$session_id = Mage::getSingleton('core/session')->getSessionId();

		if (!file_exists(str_replace('//','/',$tmpFolder))){
			mkdir(str_replace('//','/',$tmpFolder), 0755, true);
		}
		if (!file_exists(str_replace('//','/',$uploadFolder))){
			mkdir(str_replace('//','/',$uploadFolder), 0755, true);
		}
		if (!file_exists(str_replace('//', '/', $targetFolder))) {
			mkdir(str_replace('//', '/', $targetFolder), 0755, true);
			mkdir(str_replace('//', '/', $targetFolder . DS . "thumbs"), 0755, true); // Make subfoder for thumbnails
		}

		if (!empty($_FILES)) {
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder;

			$filename = $_FILES['Filedata']['name'];
			if(isset($_POST['filename'])){
				$filename = $_POST['filename'] .'.' .$fileParts['extension'];
			}

			$targetFile = rtrim($targetPath, '/') . '/' . $filename;

			$uploaded =  move_uploaded_file($tempFile,$targetFile);
			Mage::log('$uploaded ' .$uploaded);
			if($uploaded){
				if (!file_exists(str_replace('//','/',$targetFolder))){
					mkdir(str_replace('//','/',$targetPath.DS."thumbs".DS.$session_id), 0755, true);
				}

				$file = str_replace(Mage::getBaseDir('media') .DS .'tmp' .DS .'upload' .DS .'admin'.DS, "", $targetFile);
				Mage::log('file ' .$file);
				$image_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .'tmp' .DS .'upload'.DS .'admin' . DS . $file;
				Mage::log('image_url ' .$image_url);

				$html = '<div class="uploadifyimgwrapper"><img class="uplodifyimg' . '" attr-file="' .$file .'" src="' . $image_url . '"/>';
				$html .= '<a class="uploadifytag" id="uploadifytag' . '" href="#" ></a></div>';
			}
		}

		if(!isset($html)){
			$html = '<div class="error">'.$this->__("File could not be saved to temp folder. Maybe file size is to big or has no permission for writing.").'</div>';
		}

		$this->getResponse()->setBody($html);
	}

	public function uploadifyimporterAction(){
		$targetFolder = Mage::getBaseDir('var') .'/imports';

		if (!file_exists(str_replace('//', '/', $targetFolder))) {
			mkdir(str_replace('//', '/', $targetFolder), 0755, true);
		}

		if (!empty($_FILES)) {
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder;

			$filename = $_FILES['Filedata']['name'];

			if(isset($_POST['filename'])){
				$filename = $_POST['filename'] .'.' .$fileParts['extension'];
			}

			if (!$filename) {
				Js_Log::log("Filename to upload is not given", $this, Zend_Log::ERR);
			} else {
				$targetFile = rtrim($targetPath, '/') . '/' . $filename;
				$uploaded =  move_uploaded_file($tempFile,$targetFile);
			}
			$this->getResponse()->setBody(urlencode($filename));
		}
	}

	public function uploadifycustomeraccountAction(){
		$session_id = Mage::getSingleton('core/session')->getSessionId();

		$orderId = $_POST['orderId'];
		if (!isset($orderId)) {
			return $this->getResponse()->setBody("wrong or missing orderId");
		}

		$tmpFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.'tmp';

		if ($orderId) { // This is a customer upload in customer area
			$targetFolder = Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS.$orderId;
		}

		if (!file_exists(str_replace('//','/',$tmpFolder))){
			mkdir(str_replace('//','/',$tmpFolder), 0755, true);
		}
		if (!file_exists(str_replace('//','/',$targetFolder))){
			mkdir(str_replace('//','/',$targetFolder), 0755, true);
		}

		$verifyToken = md5('unique_salt' . $_POST['timestamp']);

		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			Mage::Log("Token is valid");
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/';
            $targetFile .= $this->getUniqueFilePrefix()."_";
			$targetFile .= $_FILES['Filedata']['name'];

			// Validate the file type
			$fileTypes = explode(";",Mage::getStoreConfig('fileuploader/general/filetype'));
			foreach ($fileTypes as $key => $value)
				$fileTypes[$key] = trim($fileTypes[$key]);
			$fileParts = pathinfo($_FILES['Filedata']['name']);

			if (true || in_array($fileParts['extension'],$fileTypes)) {
				Mage::Log("filetype is allowed");
				move_uploaded_file($tempFile,$targetFile);

				$optionId = $_POST['optionId'];
				$jsTemplateId = $_POST['jsTemplateId'];

				$file = str_replace(Mage::getBaseDir('media').DS.Mage::getStoreConfig('fileuploader/general/mediapath').DS, "", $targetFile);
				Mage::Log("file is".$file);
				if(isset($_POST['uploadId']) && $_POST['uploadId']){
					$upload = Mage::getModel('configurator/upload')->load($_POST['uploadId']);
					$upload->setFile($file);
					$upload->setStatus('1');
					$upload->save();
				}else{
					$upload = Mage::getModel('configurator/upload');
					$upload->setSessionId($session_id);
					$upload->setFile($file);
					$upload->setStatus('1');
					$upload->setOptionId($optionId);
					$upload->setJsTemplateId($jsTemplateId);
					$upload->setCreatedAt(new Zend_Date());
					$upload->save();
				}

				return $this->getResponse()->setBody('ok');

			}
		}
		return $this->getResponse()->setBody('Invalid file type');
	}
}
