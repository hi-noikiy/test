<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2014 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */

class Justselling_Configurator_Model_Export_Processor {

	/* relative export path */
	const EXPORT_PATH = 'var/exports';
	const IMPORT_PATH = 'var/imports';

	/*  */
	const TEMPLATE_EXPORT_PATH_NAME = 'configurator_template';

	/* file name of the json export */
	const JSON_TEMPLATE_FILE = "export.json";

	/* Simple mapping from templateId to product sku's */
	private static $_LOGFILE_PRODUCT_MAPPING = 'product-mapping.txt';

	/** @var bool flag */
	private $_isImportedCompleted = false;

	/**
	 * Imports a template (identified by the given file name) to the database.
	 * @param string $zipFileName (without file extension '.zip')
	 * @return int|bool the new templateID on success, false otherwise
	 */
	public function importTemplate($zipFileName) {
		if (empty($zipFileName)) {
			Js_Log::log("Unable to import Template: no import (zip) file name given.", $this, Zend_Log::ERR);
			return false;
		}
		register_shutdown_function('Justselling_Configurator_Model_Export_Processor::onShutdown', $this);

		if ($this->stringEndsWith($zipFileName, '.zip')) {
            $strlen = strlen($zipFileName) - 4;
			$zipFileName = substr($zipFileName, 0, $strlen);
		}
		Js_Log::log("Start importing $zipFileName...", $this, Zend_Log::INFO);
		try {
			$this->zipExtract($zipFileName);
			$templateObjects = $this->loadImportTemplateObjects();
			if (!$templateObjects) {
				Js_Log::log("Import of {$zipFileName} failed. JSON deserialisation failed with 0 objects.", $this, Zend_Log::ERR);
				$this->dropFolder($this->getImportPath());
				return false;
			}
			//$this->copyZipImages($templateObjects);

			/* Template Import */
			$templateId = $templateObjects->getTemplate()->getId();
			$utils = new Justselling_Configurator_Model_Utils_TemplateCopy($templateObjects);
			$templateIdCopy = $utils->copy($templateId);

			/* Mapping to the products */
			if ($productSkus = $this->getProductMappingOf($templateId /* OLD one! */)) {
				$productOptionIds = array();
				foreach ($productSkus as $sku) {
					/** @var $product Mage_Catalog_Model_Product */
					$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
					if ($product->getId()) {
						$options = $product->getProductOptionsCollection();
						/** @var $option Justselling_Configurator_Model_Product_Option */
						foreach ($options as $option) {
							if ($option->getType() == 'configurator') {
								$productOptionIds[] = $option->getId();
							}
						}

					}
				}
				if (!empty($productOptionIds)) {
					$template = Mage::getModel('configurator/template')->load($templateIdCopy);
					foreach ($productOptionIds as $productOptionId) {
						$template->linkTemplateWithProductOption($productOptionId, $templateIdCopy);
					}
					Js_Log::log(sprintf("Linked %s product options to template with ID %s", count($productOptionIds), $templateIdCopy),
						$this, Zend_Log::INFO);
				}
			}

			Js_Log::log("Finished importing $zipFileName successfull, new template ID={$templateIdCopy}", $this, Zend_Log::INFO);
			$this->dropFolder($this->getImportPath());
			$this->_isImportedCompleted = true;

			return $templateIdCopy;
		} catch (Exception $e) {
			Js_Log::logException($e, $this, "Unexpected problem on importing template '{$zipFileName}'");
			$this->dropFolder($this->getImportPath());
			return false;
		}
	}

	/**
	 * Copies the zip images to the configurator media path.
	 * @param $templateObjects
	 */
	private function copyZipImages($templateObjects) {
		$path = $this->getImportPath($templateObjects);
		if (false !== ($dir = opendir($path))) {
			while (false !== ($file = readdir($dir))) {
				if ($file != '.' && $file != '..') {
					$srcFile = $path.DS.$file;
					if (is_dir($srcFile)) {
						$target = Mage::getBaseDir('media').DS.'configurator';
						self::copyr($srcFile, $target);
					}
				}
			}
		}
	}

	/**
	 * Extracts the zip file to the
	 * @param $zipFileName
	 * @throws Mage_Exception
	 */
	private function zipExtract($zipFileName) {
		$path = $this->getBasePath(null, self::IMPORT_PATH);
		$file = $path.DS.$zipFileName.'.zip';
		if (is_file($file) && !is_dir($file)) {
			$zip = new ZipArchive();
			if ($zip->open($file) === true) {
				if (!$zip->extractTo($path)) {
					throw new Mage_Exception("Unable to extract zip file: ".$file);
				}
				$zip->close();
			} else {
				throw new Mage_Exception("Unable to open zip file for extraction: ".$file);
			}
		} else {
			throw new Mage_Exception("Zip not found: $file");
		}
	}

	/**
	 * Exports all templates.
	 *
	 * @param bool $logProducts true if mapping templateId=>products should be logged to a file.
	 * @return int the number of exported items
	 * @throws Exception on any internal exception
	 */
	public function exportAllTemplates($logProducts=false) {
		if ($logProducts) {
			$file = $this->getProductMappingLogFile();
			if (file_exists($file)) {
				if (!unlink($file)) Js_Log::log("Failed to remove file {$file}", $this, Zend_Log::ERR);
			}
		}
		$templates = Mage::getModel('configurator/template')->getCollection();
		if (!$templates->getSize()) {
			Js_Log::log("No template candidate found for export.", $this, Zend_Log::INFO);
			return 0;
		}
		Js_Log::log(sprintf("Start exporting %s templates...\n", $templates->getSize()), $this, Zend_Log::INFO);
		/** @var $template Justselling_Configurator_Model_Template */
		foreach ($templates as $template) {
			try {
				$this->exportTemplate($template->getId(), $logProducts);
				Js_Log::log(sprintf("Exporting template '%s' done.", $template->getTitle()), $this, Zend_Log::INFO);
			} catch (Exception $e) {
				Js_Log::log(sprintf("Export failed with reason: ".$e->getMessage()), $this, Zend_Log::ERR);
				throw $e;
			}
		}
		Js_Log::log("Export finished. Find related zip-files in folder 'var/exports'.", $this, Zend_Log::INFO);
		return $templates->getSize();
	}

	/**
	 * Exports the template identified by the given ID, to a file.
	 * @param int $templateId the id of the template to export
	 * @param bool $logProducts should be true if the mapped products should be written to a file 'product-mapping.txt'
	 * @throws Exception in case of any Exception
	 */
	public function exportTemplate($templateId, $logProducts=false) {
		$exportObjects = array();
		$exportedOptionIds = array();
		$exportedOptionValueIds = array();

		/* @var $templateModel Justselling_Configurator_Model_Template */
		$templateModel = Mage::getModel('configurator/template')->load($templateId);
		if ($templateModel->getId()) {
			array_push($exportObjects, $templateModel);
		}
		{
			/**
			 * Write the option groups
			 */
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->addFieldToFilter("template_id", $templateId);
			$groups = array();

			/** @var $option Justselling_Configurator_Model_Option */
			foreach ($options as $option) {
				$optionGroupId = $option->getOptionGroupId();
				if ($option->getOptionGroupId () && !in_array ( $optionGroupId, $groups )) {
					$groups[] = $optionGroupId;
					$optionGroup = Mage::getModel ( "configurator/optiongroup" )->load ( $optionGroupId );
					if ($optionGroup->getId()) {
						array_push($exportObjects, $optionGroup);
					} else {
						Js_Log::log("OptionGroup {$optionGroupId} for Option {$option->getId()} not found. Skipped.", $this, Zend_Log::ERR);
					}
				}
			}
		}
		{
			/**
			 * Add the options
			 */
			/* @var $options Justselling_Configurator_Model_Mysql4_Option_Collection */
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->addFieldToFilter("template_id", $templateId);
			$optionItems = $options->getItems();
			{
				/** @var $optionItem Justselling_Configurator_Model_Option */
				foreach ($optionItems as $option) {
					/*
					 * Add the option
					 */
					array_push($exportObjects, $option);
					array_push($exportedOptionIds, $option->getId());

					/**
					 * Add option matrix's of the option
					 */
					$matrixvalues = Mage::getModel("configurator/optionmatrix")->getCollection();
					$matrixvalues->addFieldToFilter("option_id", $option->getId());
					$items = $matrixvalues->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the options fonts
					 */
					$fonts = Mage::getModel("configurator/optionfont")->getCollection();
					$fonts->addFieldToFilter("option_id",$option->getId());
					$items = $fonts->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the options font color
					 */
					$fontcolors = Mage::getModel("configurator/optionfontcolor")->getCollection();
					$fontcolors->addFieldToFilter("option_id", $option->getId());
					$items = $fontcolors->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the options font configs
					 */
					$fontconfs = Mage::getModel("configurator/optionfontconfiguration")->getCollection();
					$fontconfs->addFieldToFilter("option_id", $option->getId());
					$items = $fontconfs->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the options font position
					 */
					$fontposs = Mage::getModel("configurator/optionfontposition")->getCollection();
					$fontposs->addFieldToFilter("option_id", $option->getId());
					$items = $fontposs->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the options pricelist
					 */
					$priceList = Mage::getModel("configurator/pricelist")->getCollection();
					$priceList->addFieldToFilter("option_id", $option->getId());
					$items = $priceList->getItems();
					$exportObjects = array_merge($exportObjects, $items);

					/**
					 * Add the option values
					 */
					$optionValues = Mage::getModel("configurator/value")->getCollection();
					$optionValues->addFieldToFilter("option_id", $option->getId());
					$items = $optionValues->getItems();
					foreach ($items as $item) {
						array_push($exportObjects, $item);
						array_push($exportedOptionValueIds, $item->getId());

						$valuetags = Mage::getModel("configurator/valuetag")->getCollection();
						$valuetags->addFieldToFilter("option_value_id", $item->getId());
						$exportObjects = array_merge($exportObjects, $valuetags->getItems());
					}

				} // end iterating options

				/**
				 * Add the options pricelist values
				 */
				$priceListValues = Mage::getModel("configurator/pricelistvalue")->getCollection();
				$priceListValues->addFieldToFilter('option_value_id', array('in' => $exportedOptionValueIds));
				$items = $priceListValues->getItems();
				$exportObjects = array_merge($exportObjects, $items);


				/**
				 * Add the option-blacklist items
				 */
				$optionBlacklistEntries = Mage::getModel("configurator/optionblacklist")->getCollection();
				$optionBlacklistEntries->addFieldToFilter('option_id', array('in' => $exportedOptionIds));
				$items = $optionBlacklistEntries->getItems();
				$exportObjects = array_merge($exportObjects, $items);


				/**
				 * Add the blacklist items for the OptionValues
				 */
				$blackList = Mage::getModel("configurator/blacklist")->getCollection();
				$blackList->addFieldToFilter('option_value_id', array('in' => $exportedOptionValueIds));
				$items = $blackList->getItems();
				$exportObjects = array_merge($exportObjects, $items);

				/**
				 * Add the value tag blacklist for the Options
				 */
				$optionValueTagBlackListEntries = Mage::getModel("configurator/valuetagblacklist")->getCollection();
				$optionValueTagBlackListEntries->addFieldToFilter('option_id', array('in' => $exportedOptionIds));
				$items = $optionValueTagBlackListEntries->getItems();
				$exportObjects = array_merge($exportObjects, $items);

				/**
				 * Add the childoption status for the Options
				 */
				$statuscollection = Mage::getModel("configurator/childoptionstatus")->getCollection();
				$statuscollection->addFieldToFilter('option_id', array('in' => $exportedOptionIds));
				$items = $statuscollection->getItems();
				$filteredItems = array();
				foreach ($items as $item) {
					/* As there may be references to options which are not part of this template, we filter them out here */
					$childOptionId = $item->getChildOptionId();
					if (in_array($childOptionId, $exportedOptionIds)) {
						$filteredItems[] = $item;
					}
				}
				$exportObjects = array_merge($exportObjects, $filteredItems);

				/**
				 * Add the postprice rules for the template
				 */
				$postpricerules = Mage::getModel("configurator/postpricerule")->getCollection();
				$postpricerules->addFieldToFilter("template_id", $templateId);
				$items = $postpricerules->getItems();
				$exportObjects = array_merge($exportObjects, $items);

				/**
				 * Add the rules for the template
				 */
				$rules = Mage::getModel("configurator/rules")->getCollection();
				$rules->addFieldToFilter("template_id", $templateId);
				$items = $rules->getItems();
				$exportObjects = array_merge($exportObjects, $items);

			}
		}
		if (!empty($exportObjects)) {
			$templateObjects = new Justselling_Configurator_Model_Export_TemplateObjects($exportObjects);
			$exportFile = $this->export($templateObjects);

			/* Write out a mapping of sku => templateId */
			if ($logProducts) {
				/* linkedProducts: array(productId => productoptionId) */
				$linkedProducts = $templateModel->getLinkedProducts($templateModel->getId());
				if (!empty($linkedProducts)) {
					$this->logProductMapping($templateModel->getId(), $linkedProducts);
				}
			}

			return $exportFile;
		}
	}

	/**
	 * Exports the given TemplateObjects.
	 *
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $templateObjects
	 * @throws Exception
	 * @return string
	 */
	private function export(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects) {
		if (empty($templateObjects)) {
			Js_Log::log("Nothing found to export!", $this, Zend_Log::ERR);
			return;
		}
		Js_Log::log("Start exporting ".$templateObjects->getCount()." objects...", $this);
		$encoded = $this->toJson($templateObjects);
		//if ($jsonResult) throw new Mage_Exception("json encoding failed with msg: ".json_last_error_msg());
		$file = $this->getExportJsonFileAbs($templateObjects);
		$handle = fopen($file, 'w');
		try {
			if (!$handle) throw new Mage_Exception("Unable to open for writing: {$file}");
			$result = fwrite($handle, $encoded);
			if ($result === false) throw new Mage_Exception("Writing of bytes failed!");
			fclose($handle);
			Js_Log::log("Export of template data processed, file={$file}", $this, Zend_Log::INFO);
			$this->exportImages($templateObjects);
			$this->zip($templateObjects);
			Js_Log::log("Export successfully processed, path={$this->getExportName($templateObjects)}", $this, Zend_Log::INFO);
			$this->dropFolder($this->getExportPath());
			return $this->getExportName($templateObjects);
		} catch (Exception $e) {
			Js_Log::logException($e, $this, 'Unexpected problem on export images/zip');
			if ($handle) fclose($handle);
			throw $e;
		}

	}

	/**
	 * Drops the given folder recursively. It is not ensured that the given folder is deleted afterwards.
	 * @param string $path
	 */
	public function dropFolder($path) {
		$moduleDir = Mage::getBaseDir().DS.'var';
		if (strpos($path, $moduleDir) !== 0) { // ensures only deleting something below /var
			return;
		}
		if (file_exists($path)) {
			foreach(scandir($path) as $file) {
				if ('.' === $file || '..' === $file) continue;
				if (is_dir($path.DS.$file)) $this->dropFolder($path.DS.$file);
				else unlink($path.DS.$file);
			}
			rmdir($path);
		}
	}

	/**
	 * Creates a zip file from the given TemplateObjects instance.
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $templateObjects
	 * @return string path to zip file
	 */
	private function zip(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects) {
		$zipPath = Mage::getBaseDir().DS.self::EXPORT_PATH.DS.$this->getExportName($templateObjects).'.zip';
		Js_Log::log("Start zip template to ".$zipPath, $this, Zend_Log::INFO);
		$zip = new ZipArchive();
		$openResult = $zip->open($zipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
		if ($openResult === true) {
			$path = $this->getExportPath();
			$zipLocalPath = self::TEMPLATE_EXPORT_PATH_NAME;
			try {
				$this->zipAdd($zip, $path, $zipLocalPath);
				Js_Log::log("Finished zipping to ".$zipPath, $this, Zend_Log::INFO);
			} catch (Exception $e) {
				Js_Log::logException($e, $this);
			}
			if (!$zip->close()) {
				Js_Log::log("Unable to close zip for finalizing it, ".$zipPath, $this, Zend_Log::ERR);
			}
		} else {
			Js_Log::log("Unable to open zip with overwrite option, ".$zipPath, $this, Zend_Log::ERR);
		}
		return $zipPath;
	}

	/**
	 * @param $zip ZipArchive
	 * @param $path string
	 * @param $srcPath
	 */
	private function zipAdd($zip, $path, $srcPath) {
		if (false !== ($dir = opendir($path))) {
			while (false !== ($file = readdir($dir))) {
				if ($file != '.' && $file != '..') {
					if (is_dir($path.DS.$file)) {
						$this->zipAdd($zip, $path.DS.$file, $srcPath.DS.$file);
					} else {
						$zip->addFile($path.DS.$file, $srcPath.DS.$file);
						//if($file!=='important.txt') unlink($path.DIRECTORY_SEPARATOR.$file);
					}
				}
			}
		}
	}

	/**
	 * Writes all template related images to the export path.
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $templateObjects
	 */
	private function exportImages(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects) {
		$finalImages = array();
		$images = $templateObjects->getCollectImageFileNames();
		foreach ($images as $image) {
			$imgNameParts = explode('.', basename($image));
			if (count($imgNameParts) >= 2) {
				$files = Justselling_Configurator_Model_Utils_FileHelper::getFilesOfFolder(Mage::getBaseDir('media').
					DS.dirname($image),
					$imgNameParts[0].'.*\.(jpg|jpeg|gif|png)', 'i');
				$finalImages = array_merge($finalImages, $files);
			} else {
				$imagePathAbs = Mage::getBaseDir('media').DS.$image;
				$finalImages[] = $imagePathAbs;
			}
		}
		foreach ($finalImages as $imagePathAbs) {
			if (file_exists($imagePathAbs)) {
				$image = str_replace(Mage::getBaseDir('media'),"",$imagePathAbs);
				$target = $this->getExportPath($templateObjects).DS.$image;
				$targetPath = dirname($target);
				if (!file_exists($targetPath) && (!mkdir($targetPath, 0777, true))) {
					Js_Log::log('Unable to create directory '.$targetPath, $this, Zend_Log::ERR);
				}
				if (!copy($imagePathAbs, $target)) {
					Js_Log::log("Could not copy image to $target", $this, Zend_Log::ERR);
				}
			}
		}
	}

	/**
	 * Serializes the given array of objects to a JSON string.
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $templateObjects
	 * @return string
	 */
	private function toJson($templateObjects) {
		$encoded = "{\n\"export\": [\n ";
		$idx = 0; $count = $templateObjects->getCount();
		foreach ($templateObjects->getItems() as $object) {
			if ($object instanceof Mage_Core_Model_Abstract) {
				$object->addData(array("_resource_name" => $object->getResourceName()));
				$encoded .= $object->toJson();
			} else {
				Js_Log::log("Unable to serialize object to json as no instance of Mage_Core_Model_Abstract: ".get_class($object), $this, Zend_Log::ERR);
			}
			$idx++;
			if ($idx < $count) $encoded .= ",\n";
		}
		$encoded .= "\n]\n}";
		return $encoded;
	}

	/**
	 * Decodes the given json and returns the template credentials as a simple array.
	 * @param $json
	 * @return array|bool array of imported objects, false on any problem
	 */
	private function fromJson($json) {
		$decoded = json_decode($json);
		if (is_object($decoded) && $decoded->export) {
			$importedObjects = array();
			$items = $decoded->export;
			/** @var $item array */
			foreach ($items as $item) {
				$item = (array) $item;
				$resourceName = array_key_exists('_resource_name', $item) ? $item['_resource_name'] : false;
				if (!$resourceName) {
					Js_Log::log("No resourceName found in exported item!", $this, Zend_Log::ERR);
					continue;
				}
				unset($item['_resource_name']);
				$model = Mage::getModel($resourceName);
				$model->setData($item);
				$importedObjects[] = $model;
			}
			return $importedObjects;
		}
		return false;
	}

	/**
	 * Returns the absolute base path for the export, later containing images and json configuration. This path will
	 * be zipped for export.
	 */
	public function getExportPath(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects=null) {
		// Export base path should be static, to allow renaming of zip file (export folder name different to zip file name)
		$base = $this->getBasePath();
		$base .= DS.self::TEMPLATE_EXPORT_PATH_NAME;
		if (!file_exists($base)) {
			if (!mkdir($base, 0777, true)) {
				throw new Mage_Exception("Unable to create export path on disk: {$base}");
			}
		}
		return $base;
	}

	/**
	 * Returns the absolute base path for the import.
	 */
	public function getImportPath(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects=null) {
		$base = $this->getBasePath(null, self::IMPORT_PATH);
		$base .= DS.self::TEMPLATE_EXPORT_PATH_NAME;
		return $base;
	}

	/**
	 * Returns the absolute base path (depending in im/export)
	 *
	 * @param $templateObjects Justselling_Configurator_Model_Export_TemplateObjects may be null/optional
	 * @param $var string optional
	 * @return string
	 * @throws Mage_Exception in case the path could not be created
	 */
	public function getBasePath(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects=null, $var=self::EXPORT_PATH) {
		$exportPath = Mage::getBaseDir().DS.$var;
		if ($templateObjects != null) {
			$path = $this->getExportName($templateObjects);
			$exportPath.DS.$path;
		}
		if (!file_exists($exportPath)) {
			if (!mkdir($exportPath, 0777, true)) {
				throw new Mage_Exception("Unable to create export path on disk: {$exportPath}");
			}
		}
		return $exportPath;
	}

	/**
	 * Returns the absolute file path to the template export.
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $templateObjects
	 * @return string
	 * @throws Mage_Exception on any internal exception
	 */
	private function getExportJsonFileAbs(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects) {
		$path = $this->getExportPath();
		$templateId = $templateObjects->getTemplate()->getId();
		$filePath = $path.DS.sprintf(self::JSON_TEMPLATE_FILE);
		return $filePath;
	}

	/**
	 * Returns the export name (which may be used for file and folder name).
	 *
	 * @param $templateObjects array
	 * @return string
	 * @throws Mage_Exception in case no template to export could be found
	 */
	private function getExportName(Justselling_Configurator_Model_Export_TemplateObjects $templateObjects) {
		$template = null;
		foreach ($templateObjects->getItems() as $object) {
			if ($object instanceof Justselling_Configurator_Model_Template) {
				$template = $object;
				break;
			}
		}
		if (is_null($template)) throw new Mage_Exception("No instance of Justselling_Configurator_Model_Template found!");
		$filter = new Zend_Filter_Alnum();
		$title = preg_replace('/[^(\x20-\x7F)]*/','', $template->getTitle());
		$title = empty($title) ? 'template' : $filter->filter($title);
		return $title.'_'.$template->getId();
	}


	/**
	 * Loads a TemplateObjects instance from the given file, on base of the import directory.
	 *
	 * @param $templateName the template name
	 * @return Justselling_Configurator_Model_Export_TemplateObjects|bool returns false on any problem.
	 */
	private function loadImportTemplateObjects() {
		$file = $this->getImportPath().DS.self::JSON_TEMPLATE_FILE;
		if (is_file($file) && !is_dir($file)) {
			if ($json = file_get_contents($file)) {
				$encodedObjects = $this->fromJson($json);
				if ($encodedObjects) {
					return new Justselling_Configurator_Model_Export_TemplateObjects($encodedObjects);
				}
			} else {
				Js_Log::log("Unable to load from file {$file}, please check!", $this, Zend_Log::ERR);
			}
		} else {
			Js_Log::log("File not existing: {$file}", $this, Zend_Log::ERR);
		}
		return false;
	}


	/**
	 * Recursive copy of folders.
	 * @param $source
	 * @param $dest
	 */
	static public function copyr($source, $dest) {
		$ds = DIRECTORY_SEPARATOR;
		// recursive function to copy
		// all subdirectories and contents:
		if(is_dir($source)) {
			$dir_handle=opendir($source);
			//$sourcefolder = basename($source);
			if (!file_exists($dest)) mkdir($dest);
			while($file=readdir($dir_handle)){
				if($file!="." && $file!=".."){
					if(is_dir($source.$ds.$file)){
						self::copyr($source.$ds.$file, $dest.$ds.$file);
					} else {
						copy($source.$ds.$file, $dest.$ds.$file);
					}
				}
			}
			closedir($dir_handle);
		} else {
			// can also handle simple copy commands
			copy($source, $dest);
		}
	}


	/**
	 * @param $whole
	 * @param $end
	 * @return bool
	 */
	private function stringEndsWith($whole, $end) {
		if (strlen($end) == 0 || strlen($end) > strlen($whole)) return false;
		return (strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
	}

	/**
	 * @return string
	 */
	private function getProductMappingLogFile($isImport=false) {
		$importOrExport = $isImport ? self::IMPORT_PATH : self::EXPORT_PATH;
		$file = Mage::getBaseDir().DS.$importOrExport.DS.self::$_LOGFILE_PRODUCT_MAPPING;
		return $file;
	}

	/**
	 * Logs a product mapping for the given templateId.
	 * @param int $templateId
	 * @param array $productOptionIds array(productId => productOptionId)
	 */
	private function logProductMapping($templateId, array $productOptionIds) {
		if (!empty($productOptionIds)) {
			$file = $this->getProductMappingLogFile();
			$handle = fopen($file, 'a');
			if ($handle) {
				$skus = array();
				foreach ($productOptionIds as $productId => $productOptionId) {
					$product = Mage::getModel('catalog/product')->load($productId);
					if ($product->getId()) {
						$skus[] = $product->getSku();
					}
				}
				fwrite($handle, $templateId.'='.implode(',', $skus)."\n");
				fclose($handle);
			} else {
				Js_Log::log("Not able to write category mapping!", $this, Zend_Log::ERR);
			}
		}
	}

	/**
	 * Returns all product sku mappings (to template IDs) as an array (templateId=>array(skus))
	 * @return array|bool false if no mapping could be found
	 */
	private function getProductMappings() {
		$mappings = array();
		$mappingFile = $this->getProductMappingLogFile(true);
		if (file_exists($mappingFile)) {
			$lines = file($mappingFile);
			foreach ($lines as $line) {
				$items = explode('=', trim($line));
				if (count($items) == 2) {
					$skus = explode(',', $items[1]);
					$mappings[$items[0]] = $skus;
				}
			}
		}
		return empty($mappings) ? false : $mappings;
	}

	/**
	 * Returns an array of product skus for the given template ID.
	 * @param $templateId
	 * @return bool|array false if no mapping could be found for the given template ID.
	 */
	private function getProductMappingOf($templateId) {
		$mappings = $this->getProductMappings();
		if (!empty($mappings) && array_key_exists($templateId, $mappings)) {
			return $mappings[$templateId];
		}
		return false;
	}

	/** @return bool  */
	public function isImportCompleted() {
		return $this->_isImportedCompleted;
	}

	/**
	 * @param $processor Justselling_Configurator_Model_Export_Processor
	 */
	public static function onShutdown($processor) {
		$error = error_get_last();
		if ($processor && !$processor->isImportCompleted()) {
			Js_Log::log('Shutdown of ExportProcessor: '.print_r($error, true), 'configurator',
				Zend_Log::ERR);
			$processor->dropFolder($processor->getImportPath());
		}
	}

	/**
	 * Checks the currently set image path reference and copies it to the related (new structure) target location. In
	 * case the location has been adjusted it is set in the image reference (data).<br/>
	 * This method may be called in case of import, or copy of a configurator model.
	 *
	 * @param $imgField
	 * @param $object Mage_Core_Model_Abstract
	 * @return bool
	 */
	public static function adjustAndCopyImageLocation($object, $imgField) {
		if (empty($object)) {
			Js_Log::log("object is empty!", 'configurator', Zend_Log::ERR);
			return false;
		}
		if (empty($imgField) || !$object->hasData($imgField)) {
			Js_Log::log("object does not contain data for $imgField or imgField is empty!", 'configurator', Zend_Log::ERR);
			return false;
		}
		if (!method_exists($object, 'calculateImagePath')) {
			Js_Log::log("given object does not implement method 'calculateImagePath'!", 'configurator', Zend_Log::ERR);
			return false;
		}
		$hasBeenAdjusted = false;
		$imgRef = $object->getData($imgField);
		if (!empty($imgRef)) {
			$processor = new Justselling_Configurator_Model_Export_Processor();
			$locations = array(
				$processor->getImportPath(), // contains 'configurator_template'
				Mage::getBaseDir('media')
			);
			foreach ($locations as $location) {
				if ($finalLocation = self::findImageLocation($location, $imgRef)) {
					$currentPath = dirname($finalLocation);
					$calculatedPath = $object->calculateImagePath(true, $imgField);
					if ($calculatedPath && strcasecmp($currentPath, $calculatedPath) !== 0) {
						if (!file_exists($calculatedPath) && !mkdir($calculatedPath, 0777, true)) {
							throw new Exception("Unable to created '$calculatedPath', please check permissions.");
						}
						if (copy($finalLocation, $calculatedPath.DS.basename($imgRef))) {
							$object->setData($imgField, $object->calculateImagePath(false, $imgField).DS.basename($imgRef));
							$hasBeenAdjusted = true;
						} else {
							Js_Log::log("Unable to copy image to $calculatedPath!", 'configurator', Zend_Log::ERR);
						}
					}
					break;
				} else {
					$object->setData($imgField, '');
					$hasBeenAdjusted = true;
				}
			}
		}
		return $hasBeenAdjusted;
	}

	private static function findImageLocation($absPath, $imgRef) {
		$trials = array(''.DS, DS.'configurator'.DS);
		foreach ($trials as $imgRefPrefix) {
			$location = $absPath.$imgRefPrefix.$imgRef;
			if (file_exists($location)) {
				return $location;
			}
		}
		return false;
	}
}
?>