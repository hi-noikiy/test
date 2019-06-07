<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Helper_ImageMigration extends Mage_Core_Helper_Abstract {

	public function migrateAllImages($templateId = false){

		$allFilesToDelete = array();

		$this->checkConfiguratorFolderExists();

		$this->isMediaFolderReadable();
		$this->isMediaFolderWritable();

		$this->migrateGroupImages($allFilesToDelete, $templateId);
		$this->migrateTemplateImages($allFilesToDelete, $templateId);
		$this->migrateOptionImages($allFilesToDelete, $templateId);
		$this->migrateOptionValueImages($allFilesToDelete, $templateId);

		$this->removeAllImagesFromOldDir($allFilesToDelete);
	}

	private function checkConfiguratorFolderExists(){
		$targetFolder = Mage::getBaseDir('media') .DS .'configurator';
		if (!file_exists(str_replace('//', '/', $targetFolder))) {
			mkdir(str_replace('//', '/', $targetFolder), 0755, true);
		}
	}

	private function isMediaFolderReadable(){
		$filename = Mage::getBaseDir('media') . DS ."configurator";
		if (!is_readable($filename)) {
			Mage::log("The media/configurator folder isn't readable. Please check it.");
			throw new Exception("The media/configurator folder isn't readable. Please check it.");
		}
	}

	private function isMediaFolderWritable(){
		$filename = Mage::getBaseDir('media') . DS ."configurator";
		if (!is_writable($filename)) {
			Mage::log("The media/configurator folder isn't writable. Please check it.");
			throw new Exception("The media/configurator folder isn't writable. Please check it.");
		}
	}

	private function migrateGroupImages(&$allFilesToDelete, $templateId){
		$groupCollection = Mage::getModel("configurator/optiongroup")->getCollection();
		$groupCollection->addFieldToFilter('group_image', array('notnull' => true,));
		if($templateId){
			$groupCollection->addFieldToFilter('template_id', $templateId);
		}

		foreach($groupCollection as $group){
			$templateId = $group->getTemplateId();
			$groupImage = $group->getGroupImage();
			$newPath = 'configurator/' .$templateId;
			if(strpos($groupImage, $newPath) === FALSE){
				$basename = basename($groupImage);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath($newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$group->setGroupImage($fileSrcNew);
							$group->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for group with id " .$group->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for group with id " .$group->getId());
				}
			}
		}
	}

	private function migrateTemplateImages(&$allFilesToDelete, $templateId){
		$templateCollection = Mage::getModel("configurator/template")->getCollection();
		$templateCollection->addFieldToFilter('base_image', array('notnull' => true,));
		if($templateId){
			$templateCollection->addFieldToFilter('id', $templateId);
		}

		foreach($templateCollection as $template){
			$templateId = $template->getId();
			$image = $template->getBaseImage();
			$newPath = 'configurator/' .$templateId;
			if(strpos($image, $newPath) === FALSE){
				$basename = basename($image);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath($newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$template->setBaseImage($fileSrcNew);
							$template->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for template with id " .$template->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for template with id " .$template->getId());
				}
			}
		}

		$templateCollection = Mage::getModel("configurator/template")->getCollection();
		$templateCollection->addFieldToFilter('template_image', array('notnull' => true,));
		if($templateId){
			$templateCollection->addFieldToFilter('id', $templateId);
		}

		foreach($templateCollection as $template){
			$templateId = $template->getId();
			$image = $template->getTemplateImage();
			$newPath = 'configurator/' .$templateId;
			if(strpos($image, $newPath) === FALSE){
				$basename = basename($image);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath($newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$template->setTemplateImage($fileSrcNew);
							$template->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for template with id " .$template->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for template with id " .$template->getId());
				}
			}
		}
	}

	private function migrateOptionImages(&$allFilesToDelete, $templateId){
		$optionCollection = Mage::getModel("configurator/option")->getCollection();
		$optionCollection->addFieldToFilter('option_image', array('notnull' => true,));
		if($templateId){
			$optionCollection->addFieldToFilter('template_id', $templateId);
		}

		foreach($optionCollection as $option){
			$templateId = $option->getTemplateId();
			$optionId = $option->getId();
			$image = $option->getOptionImage();
			$newPath = 'configurator/' .$templateId .DS .$optionId;
			if(strpos($image, $newPath) === FALSE){
				$basename = basename($image);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath($newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$option->setOptionImage($fileSrcNew);
							$option->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for option with id " .$option->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for option with id " .$option->getId());
				}
			}
		}
	}

	private function migrateOptionValueImages(&$allFilesToDelete, $templateId){
		if($templateId){
			$optionCollection = Mage::getModel("configurator/option")->getCollection();
			if($templateId){
				$optionCollection->addFieldToFilter('template_id', $templateId);
			}
			$optionArray = array();
			foreach($optionCollection as $option){
				$optionArray[] = $option->getId();
			}
		}
		$optionValueCollection = Mage::getModel("configurator/value")->getCollection();
		$optionValueCollection->addFieldToFilter('thumbnail', array('notnull' => true,));
		if($templateId){
            $optionValueCollection->addFieldToFilter('option_id', array('in' => $optionArray));
		}

		foreach($optionValueCollection as $optionValue){
			$optionValueId = $optionValue->getId();
			$optionId = $optionValue->getOptionId();
            if(!$templateId){
			    $templateId = Mage::getModel("configurator/option")->load($optionId)->getTemplateId();
            }
			$image = $optionValue->getThumbnail();
			$newPath = $templateId .DS .$optionId .DS .$optionValueId;
			if(strpos($image, $newPath) === FALSE){
				$basename = basename($image);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .'configurator' .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath('configurator' .DS .$newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$optionValue->setThumbnail($fileSrcNew);
							$optionValue->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for optionvalue with id " .$optionValue->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for optionvalue with id " .$optionValue->getId());
				}
			}
		}

		$optionValueCollection = Mage::getModel("configurator/value")->getCollection();
		$optionValueCollection->addFieldToFilter('image', array('notnull' => true,));
		if($templateId){
			$optionValueCollection->addFieldToFilter('option_id', array('in' => $optionArray));
		}

		foreach($optionValueCollection as $optionValue){
			$optionValueId = $optionValue->getId();
			$optionId = $optionValue->getOptionId();
            if(!$templateId){
			    $templateId = Mage::getModel("configurator/option")->load($optionId)->getTemplateId();
            }
			$image = $optionValue->getImage();
			$newPath = $templateId .DS .$optionId .DS .$optionValueId;
			if(strpos($image, $newPath) === FALSE){
				$basename = basename($image);

				$fileDirOld = Mage::getBaseDir('media') .DS .'configurator' .DS .$basename;
				$fileDirNew = Mage::getBaseDir('media') .DS .'configurator' .DS .$newPath .DS .$basename;
				$fileSrcNew = $newPath .DS .$basename;

				try{
					if(file_exists(str_replace('//', '/', $fileDirOld))){
						Mage::helper('configurator/upload')->createAllDirectoriesFromPath('configurator' .DS .$newPath);
						if(copy($fileDirOld, $fileDirNew)){
							$optionValue->setImage($fileSrcNew);
							$optionValue->save();
							$allFilesToDelete[$fileDirOld] = $fileDirOld;
						}else{
							Mage::log("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
							throw new Exception("Can't copy file for image migration. Maybe the media/configurator folder has no writing permissions.");
						}
					}else{
						Mage::log("image not found for optionvalue with id " .$optionValue->getId());
					}
				}catch (Exception $e){
					Mage::log("can't migrate image for optionvalue with id " .$optionValue->getId());
				}
			}
		}
	}

	private function removeAllImagesFromOldDir($allFilesToDelete){
		foreach($allFilesToDelete as $file){
			unlink($file);
		}
	}
}