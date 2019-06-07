<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Helper_Data extends Mage_Core_Helper_Abstract
{

	/** @var Justselling_Configurator_Model_Mysql4_Option_Collection [bschulte] */
	private $_optionsLocalCache = null;

	const MAGE_CACHE_NAME = 'productconfigurator';

	protected function prepareForCache($data) {
		if ($data || is_array($data)) {
			if ($data instanceof Varien_Data_Collection) {
				$data->load();
			}
			$serialized = serialize($data);
			return $serialized;
		}
		return "";
	}

	protected function prepareFromCache($data) {
		if ($data) {
			$unserialized = unserialize($data);
			return $unserialized;
		}
		return "";
	}

	protected function buildCacheKey($key) {
		$_product = Mage::registry("current_product");
		$id = "";
		if ($_product && $_product->getId()) {
			$id = $_product->getId();
		}

		// Add some general settings to cache key
		$key .=
			"_".Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getLocale()
			."_".Mage::app()->getStore()->getCurrentCurrencyCode()
			."_".Mage::helper("configurator")->getTaxFactor()
			."-".$id;

		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$key .= "_".$customerData->getId();
		}

		return $key;
	}

	public function writeToCache($value, $key, $tags, $serialize = true, $lifeTime=null) {
        if (Mage::app()->useCache(self::MAGE_CACHE_NAME) && !Mage::app()->getRequest()->getParam('configure')) {
			if (!in_array("PRODCONF", $tags)) {
				$tags[] = "PRODCONF";
			}

			$key = $this->buildCacheKey($key);
            //Js_Log::log("write to cache key=".$key, $this);

			// initialize collection before serialize
			if ($value instanceof Mage_Core_Model_Mysql4_Collection_Abstract) {
				$value = $this->createArrayOfCollection($value);
			}elseif(is_array($value)){
				foreach($value as $val){
					if ($value instanceof Mage_Core_Model_Mysql4_Collection_Abstract) {
						$value = $this->createArrayOfCollection($value);
					}
				}
			}

			$cache = Mage::app()->getCache();
			if ($serialize) {
				$value = $this->prepareForCache($value);
			}
			return $cache->save(
				$value,
				$key,
				$tags,
				$lifeTime
			);
		}
		return false;
	}

    public function createFolder($folder) {
        $folders = explode(DS, $folder);
        $tmpFolder = '';
        foreach ($folders as $subfolder) {
            if ($subfolder != '') {
                $tmpFolder .= DIRECTORY_SEPARATOR . $subfolder;
                if (!file_exists($tmpFolder)) {
                    mkdir($tmpFolder);
                }
            }
        }
        return $tmpFolder;
    }

	public function getLocalConfiguration($key) {
		$fileName = Mage::getBaseDir().'/app/etc/local.xml';
		$xmlString = file_get_contents($fileName);
		$localXml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
		$justselling = $localXml->global[0]->justselling[0];
		if ($justselling) {
			$value = (string) $justselling->$key;
			if ($value) {
				return $value;
			}
		}
		return false;
	}

	private function createArrayOfCollection($value){
		$valueArray = array();
		foreach($value as $val){
			$valueArray[] = $val;
		}
		return $valueArray;
	}

	public function readFromCache($key, $serialize = true) {
		if (Mage::app()->useCache(self::MAGE_CACHE_NAME)) {
			$key = $this->buildCacheKey($key);
            //Js_Log::log("read from cache key=".$key, $this);

			$cache = Mage::app()->getCache();
			if (!$cache->load($key)) {
                //Js_Log::log("cache miss", $this);
				return false;
			}
            //Js_Log::log("cache hit", $this);
			$value = $cache->load($key);
			if ($serialize) {
				$value = $this->prepareFromCache($cache->load($key));
			}
			return $value;
		}
		return false;
	}

	public function getConfiguratorVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Justselling_Configurator->version;
	}
	public function getConfiguratorEdition() {
		$editions = array("U" => "Ultimate", "P" => "Professional", "B" => "Basic");
		$edition = Mage::getSingleton('core/session')->getEdition();
		if (isset($editions[$edition])) {
			return $editions[$edition];
		}
		return "unknown";
	}

	public function isMageEnterprise(){
		return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
	}

	public function getMagentoVersion()
	{
		$ver_info = Mage::getVersionInfo();
		$mag_version	= "{$ver_info['major']}.{$ver_info['minor']}.{$ver_info['revision']}.{$ver_info['patch']}";

		return $mag_version;
	}

	public function getDeepLink($product, $values) {
		if ($product && $product->getproductUrl())
			return $product->getproductUrl()."?".$this->getDeepLinkParameters($values);

		return "";
	}

	public function getDeepLinkParameters($values) {
		$values = unserialize($values);
		$options = $values['options'];
		$deeplink = "";

		if ($options) {
			foreach ( $options as $optionId => $option ) {
				if (is_array ($option)) {
					foreach ($option as $config) {
						if (is_array ($option)) {
							if (isset ($config ['template'])) {
								foreach ($config ['template'] as $templateOptionId => $templateOptionValue) {
									if ($templateOptionId && $templateOptionValue) {
										if ($deeplink) {
											$deeplink .= "&";
										}
										$deeplink = $deeplink . "o_" . $optionId . "_" . $templateOptionId . "=" . $templateOptionValue;
									}
								}
							}
						}
					}
				}
			}
		}

		return $deeplink;
	}

	public function getListUrl() {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."configurator/list";
	}

	public function isResponsiveThemeActive() {
		if (Mage::getSingleton('core/design_package')->getPackageName() == "justselling-themeone") {
			return true;
		}
		return false;
	}

	public function getUploadImage($filename) {
		$parts = explode(".",$filename);
		$extension = $parts[sizeof($parts)-1];
		if (in_array($extension, array("gif","jpeg","jpg","png","tiff","tif"))) {
			if ($extension == "jpg") $extension="jpeg";
			if ($extension == "tif") $extension="tiff";
            return Mage::getDesign()->getSkinUrl("images/justselling/image/".$extension.".png");
		}
		if (in_array($extension, array("doc","docx","pdf","psd","zip","vnd"))) {
			if ($extension == "docx") $extension="msword";
			if ($extension == "doc") $extension="msword";
			if ($extension == "psd") $extension="x-photoshop";
			if ($extension == "vnd") $extension="msword";
			return Mage::getDesign()->getSkinUrl('images/justselling/application/' .$extension .'.png');
		}
		if (in_array($extension, array("xml"))) {
			return Mage::getDesign()->getSkinUrl('images/justselling/text/' .$extension .'.png');
		}
		return Mage::getDesign()->getSkinUrl('images/justselling/file.png');
	}

	public function getMimetypArrayByOptionId($optionId, $format = "json"){
		$_option = Mage::getModel("configurator/option")->load($optionId);

		$_filetypes = Mage::getStoreConfig('fileuploader/general/filetype');
		if ($_option->getId()) {
			$_filetypes = $_option->getUploadFiletypes();
		}

		if ($_filetypes) {
			if($_filetypes) {
				$_filetypes = str_replace(' ','',$_filetypes);
			}

			$_filetypesArray = explode(";", $_filetypes);
			$mimeTypeArray = array();
			if(is_array($_filetypesArray)) {
				foreach($_filetypesArray as $fn) {
					$mimeType = self::getMimeType(str_replace('*','',$fn));
					if($mimeType){
						$mimeTypeTempArray = explode(";", $mimeType);
						foreach($mimeTypeTempArray as $mt) {
							$mimeTypeArray[] =  $mt;
						}
					}
				}
			} else {
				$mimeType = self::getMimeType(str_replace('*','',$_filetypes));
				if($mimeType) {
					$mimeTypeTempArray = explode(";", $mimeType);
					foreach($mimeTypeTempArray as $mt) {
						$mimeTypeArray[] =  $mt;
					}
				}
			}

			if ($format === 'json') {
				$result =  json_encode($mimeTypeArray);
			} else{
				$result =  implode(",", $mimeTypeArray);
			}
			return $result;
		}

		if ($format === 'json') {
			return '{}';
		}
		return '';
	}

	function getMimeType($filename){
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		switch ($extension)
		{
			case "zip": $mime="application/zip"; break;
			case "rar": $mime="application/rar"; break;
			case "ez":  $mime="application/andrew-inset"; break;
			case "hqx": $mime="application/mac-binhex40"; break;
			case "cpt": $mime="application/mac-compactpro"; break;
			case "doc": $mime="application/msword"; break;
			case "bin": $mime="application/octet-stream"; break;
			case "dms": $mime="application/octet-stream"; break;
			case "lha": $mime="application/octet-stream"; break;
			case "lzh": $mime="application/octet-stream"; break;
			case "exe": $mime="application/octet-stream"; break;
			case "class": $mime="application/octet-stream"; break;
			case "so":  $mime="application/octet-stream"; break;
			case "dll": $mime="application/octet-stream"; break;
			case "oda": $mime="application/oda"; break;
			case "pdf": $mime="application/pdf"; break;
			case "eps": $mime="application/postscript"; break;
			case "ps":  $mime="application/postscript"; break;
			case "smi": $mime="application/smil"; break;
			case "smil": $mime="application/smil"; break;
			case "xls": $mime="application/vnd.ms-excel"; break;
			case "ppt": $mime="application/vnd.ms-powerpoint"; break;
			case "wbxml": $mime="application/vnd.wap.wbxml"; break;
			case "wmlc": $mime="application/vnd.wap.wmlc"; break;
			case "wmlsc": $mime="application/vnd.wap.wmlscriptc"; break;
			case "bcpio": $mime="application/x-bcpio"; break;
			case "vcd": $mime="application/x-cdlink"; break;
			case "pgn": $mime="application/x-chess-pgn"; break;
			case "cpio": $mime="application/x-cpio"; break;
			case "csh": $mime="application/x-csh"; break;
			case "dcr": $mime="application/x-director"; break;
			case "dir": $mime="application/x-director"; break;
			case "dxr": $mime="application/x-director"; break;
			case "dvi": $mime="application/x-dvi"; break;
			case "spl": $mime="application/x-futuresplash"; break;
			case "gtar": $mime="application/x-gtar"; break;
			case "hdf": $mime="application/x-hdf"; break;
			case "js":  $mime="application/x-javascript"; break;
			case "skp": $mime="application/x-koan"; break;
			case "skd": $mime="application/x-koan"; break;
			case "skt": $mime="application/x-koan"; break;
			case "skm": $mime="application/x-koan"; break;
			case "latex": $mime="application/x-latex"; break;
			case "nc":  $mime="application/x-netcdf"; break;
			case "cdf": $mime="application/x-netcdf"; break;
			case "sh":  $mime="application/x-sh"; break;
			case "shar": $mime="application/x-shar"; break;
			case "swf": $mime="application/x-shockwave-flash"; break;
			case "sit": $mime="application/x-stuffit"; break;
			case "sv4cpio": $mime="application/x-sv4cpio"; break;
			case "sv4crc": $mime="application/x-sv4crc"; break;
			case "tar": $mime="application/x-tar"; break;
			case "tcl": $mime="application/x-tcl"; break;
			case "tex": $mime="application/x-tex"; break;
			case "texinfo": $mime="application/x-texinfo"; break;
			case "texi": $mime="application/x-texinfo"; break;
			case "t":   $mime="application/x-troff"; break;
			case "tr":  $mime="application/x-troff"; break;
			case "roff": $mime="application/x-troff"; break;
			case "man": $mime="application/x-troff-man"; break;
			case "me":  $mime="application/x-troff-me"; break;
			case "ms":  $mime="application/x-troff-ms"; break;
			case "ustar": $mime="application/x-ustar"; break;
			case "src": $mime="application/x-wais-source"; break;
			case "xhtml": $mime="application/xhtml+xml"; break;
			case "xht": $mime="application/xhtml+xml"; break;
			case "zip": $mime="application/zip"; break;
			case "au":  $mime="audio/basic"; break;
			case "snd": $mime="audio/basic"; break;
			case "mid": $mime="audio/midi"; break;
			case "midi": $mime="audio/midi"; break;
			case "kar": $mime="audio/midi"; break;
			case "mpga": $mime="audio/mpeg"; break;
			case "mp2": $mime="audio/mpeg"; break;
			case "mp3": $mime="audio/mpeg"; break;
			case "aif": $mime="audio/x-aiff"; break;
			case "aiff": $mime="audio/x-aiff"; break;
			case "aifc": $mime="audio/x-aiff"; break;
			case "m3u": $mime="audio/x-mpegurl"; break;
			case "ram": $mime="audio/x-pn-realaudio"; break;
			case "rm":  $mime="audio/x-pn-realaudio"; break;
			case "rpm": $mime="audio/x-pn-realaudio-plugin"; break;
			case "ra":  $mime="audio/x-realaudio"; break;
			case "wav": $mime="audio/x-wav"; break;
			case "pdb": $mime="chemical/x-pdb"; break;
			case "xyz": $mime="chemical/x-xyz"; break;
			case "bmp": $mime="image/bmp"; break;
			case "gif": $mime="image/gif"; break;
			case "ief": $mime="image/ief"; break;
			case "jpeg": $mime="image/jpeg"; break;
			case "jpg": $mime="image/jpeg"; break;
			case "jpe": $mime="image/jpeg"; break;
			case "png": $mime="image/png"; break;
			case "tiff": $mime="image/tiff"; break;
			case "tif": $mime="image/tiff"; break;
			case "djvu": $mime="image/vnd.djvu"; break;
			case "djv": $mime="image/vnd.djvu"; break;
			case "wbmp": $mime="image/vnd.wap.wbmp"; break;
			case "ras": $mime="image/x-cmu-raster"; break;
			case "pnm": $mime="image/x-portable-anymap"; break;
			case "pbm": $mime="image/x-portable-bitmap"; break;
			case "pgm": $mime="image/x-portable-graymap"; break;
			case "ppm": $mime="image/x-portable-pixmap"; break;
			case "rgb": $mime="image/x-rgb"; break;
			case "xbm": $mime="image/x-xbitmap"; break;
			case "xpm": $mime="image/x-xpixmap"; break;
			case "xwd": $mime="image/x-xwindowdump"; break;
			case "igs": $mime="model/iges"; break;
			case "iges": $mime="model/iges"; break;
			case "msh": $mime="model/mesh"; break;
			case "mesh": $mime="model/mesh"; break;
			case "silo": $mime="model/mesh"; break;
			case "wrl": $mime="model/vrml"; break;
			case "vrml": $mime="model/vrml"; break;
			case "css": $mime="text/css"; break;
			case "html": $mime="text/html"; break;
			case "htm": $mime="text/html"; break;
			case "asc": $mime="text/plain"; break;
			case "txt": $mime="text/plain"; break;
			case "rtx": $mime="text/richtext"; break;
			case "rtf": $mime="text/rtf"; break;
			case "sgml": $mime="text/sgml"; break;
			case "sgm": $mime="text/sgml"; break;
			case "tsv": $mime="text/tab-separated-values"; break;
			case "wml": $mime="text/vnd.wap.wml"; break;
			case "wmls": $mime="text/vnd.wap.wmlscript"; break;
			case "etx": $mime="text/x-setext"; break;
			case "xml": $mime="text/xml"; break;
			case "xsl": $mime="text/xml"; break;
			case "mpeg": $mime="video/mpeg"; break;
			case "mpg": $mime="video/mpeg"; break;
			case "mpe": $mime="video/mpeg"; break;
			case "qt":  $mime="video/quicktime"; break;
			case "mov": $mime="video/quicktime"; break;
			case "mxu": $mime="video/vnd.mpegurl"; break;
			case "avi": $mime="video/x-msvideo"; break;
			case "movie": $mime="video/x-sgi-movie"; break;
			case "asf": $mime="video/x-ms-asf"; break;
			case "asx": $mime="video/x-ms-asf"; break;
			case "wm":  $mime="video/x-ms-wm"; break;
			case "wmv": $mime="video/x-ms-wmv"; break;
			case "wvx": $mime="video/x-ms-wvx"; break;
			case "ice": $mime="x-conference/x-cooltalk"; break;
			case "csv": $mime="text/csv;text/comma-separated-values;application/csv"; break;
			case "psd": $mime="image/vnd.adobe.photoshop"; break;
			case "ai": $mime="application/illustrator"; break;
			default: $mime=""; break;
		}

		return $mime;
	}

	public function getFileClassByMimetype($mimetype) {
		if ($mimetype) {
			$parts =  explode("/", $mimetype);
			if (is_array($parts)) {
				return $parts[0];
			}
		}
		return "unknown";
	}

	public function getFileTypeByMimetype($mimetype) {
		if ($mimetype) {
			$parts =  explode("/", $mimetype);
			if (is_array($parts)) {
				return $parts[1];
			}
		}
		return "unknown";
	}

	public function maskadeWysiwyg($html) {

		$html = str_replace("\"", "'", $html);
		return $html;
	}

	public function getPrice() {

	}

	/**
	 * @param $option
	 * @param $price
	 * @param null $product_id
	 * @return float|int
	 */
	public function getDiscountPrice($option, $price, $product_id = NULL) {
		if ($option->getApplyDiscount()) {
			if (is_null($product_id) && Mage::registry('current_product')) {
				$product_id = Mage::registry('current_product')->getId();
			}

			if(Mage::getSingleton( 'customer/session' )->isLoggedIn()) {
				$group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
			} else {
				$group_id = null;
			}

			$website_id = Mage::app()->getStore()->getWebsiteId();
			$store_id = Mage::app()->getStore()->getStoreId();
			$store_timestamp = Mage::app()->getLocale()->storeTimeStamp($store_id);

			$rules = Mage::getResourceModel('catalogrule/rule')->getRulesFromProduct($store_timestamp, $website_id, $group_id, $product_id);
			foreach ($rules as $rule) {
				if (isset($rule['simple_action'])) { // Magento 1.4
					$operator = $rule['simple_action'];
					$amount = $rule['discount_amount'];
				}
				if (isset($rule['action_operator'])) { // Magento 1.5+
					$operator = $rule['action_operator'];
					$amount = $rule['action_amount'];
				}

				if (isset($operator) && $operator == 'by_percent') {
					$price -= $price * $amount/100;;
				}
				if (isset($operator) && $operator == 'to_percent') {
					$price = $price * $amount/100;
				}
				if (isset($operator) && $operator == 'to_fixed') {
					$price = 0;
				}
			}
		}

		return $price;
	}

	public function getOptionSku($params, $skuDelimiter)
	{
		$sku = '';
		foreach ($params as $js_template_id => $optionValue) {
			if( is_array($optionValue) ) {
				$optionValue = serialize(array($js_template_id => array('template' => $optionValue)));
				$templateOptions = $this->getTemplateOption($optionValue);
			} else {
				$templateOptions = $this->getTemplateOption($optionValue);
			}

			if( is_array($templateOptions) && count($templateOptions) > 0) {
				$skus = array();
				foreach($templateOptions as $templateOption) {
					if( !empty($templateOption['value']['sku']) ) {
						$skus[] = $templateOption['value']['sku'];
					}
				}
				$sku = implode($skuDelimiter, $skus);
			}
		}
		return $sku;
	}

	public function getSelectedTemplateOptions($params) {
		$templateOptions = array();
		if( isset($params['options']) ) {
			foreach($params['options'] as $option) {
				if (is_array($option)) {
					foreach($option as $configId => $config) {
						if (is_array($option)) {
							if( isset($config['template']) ) {
								foreach($config['template'] as $templateOptionId => $templateOptionValue) {
									$templateOptions[$configId][$templateOptionId] = $templateOptionValue;
								}
							}
						}
					}
				}
			}
		}
		return $templateOptions;
	}

	public function getTemplateOption($optionValue) {
		$arr =  unserialize($optionValue);
		if (!is_array($arr))
			return false;

		$options = array();
		foreach ($arr as $optionValues) {

			foreach($optionValues as $valueKey => $optionValue) {
				if ($valueKey == "postprice") {
					$options[] = array(
						'option' => array("title" => "postprice", "is_visible" => 0),
						'value' => array(
							'title'=> "__postprice",
							'price'=> $optionValue,
							'sku' => ""
						)
					);
				}
				if( $valueKey == "template" ) {
					foreach ($optionValue as $optionId => $valueId ) {
						$optionModel = Mage::getModel('configurator/option')->load($optionId);
						if( !empty($valueId) ) {

							/* Get option price */
							switch ($optionModel->getType()) {
								case "area":
								case "text":
								case "static":
								case "combi":
								case "matrixvalue":
								case "expression":
								case "http":
								case "textimage":
								case "checkbox":
								case "date":
									$price =  $optionModel->getCalculatedPrice($valueId,$optionValue);
									break;
								case "selectcombi":
								case "listimagecombi":
									$price = $optionModel->getCalculatedPrice($valueId,$optionValue);
									break;
								case "listimage":
								case "select":
								case "radiobuttons":
									$valueModel = Mage::getModel('configurator/value')->load($valueId);
									$price = $valueModel->getPrice();
									break;
							}

							if ($this instanceof Mage_Catalog_Model_Product_Option) {
								$product_id = $this->getOption()->getProductId();
								$price = Mage::helper('configurator')->getDiscountPrice($optionModel, $price, $product_id);
							} else {
								$price = Mage::helper('configurator')->getDiscountPrice($optionModel, $price);
							}

							/* build option array */
							switch ($optionModel->getType()) {
								case "area":
								case "text":
								case "static":
								case "combi":
								case "matrixvalue":
								case "expression":
								case "http":
								case "textimage":
								case "checkbox":
								case "date":
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=>$valueId,
											'price'=> $price,
											'sku' => $optionModel->getSku()
										)
									);
									break;
								case "selectcombi":
								case "listimagecombi":
									$valueModel = Mage::getModel('configurator/value')->load($valueId);
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=> $valueModel->getTitle(),
											'price'=> $price,
											'sku'=> $valueModel->getSku()
										)
									);
									break;
								case "listimage":
								case "select":
								case "radiobuttons":
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=> $valueModel->getTitle(),
											'price'=> $price,
											'sku' => $valueModel->getSku()
										)

									);
									break;
							}
						}
					}
				}
			}
		}

		return $options;
	}

	public function getOptionValueByIdOrTemplateId($id, $templateId = null){
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');

		// get all options
		$optionTable = Mage::getSingleton("core/resource")->getTableName('configurator/option');
		$select = $connection->select()
			->from(
				array('co' => $optionTable),
				array('id',"template_id","parent_id","title","type","sort_order","is_require","is_visible","apply_discount","max_characters","min_value","max_value",
					"text_validate","sku","value", "placeholder","option_group_id",
					"upload_type","upload_maxsize", "upload_filetypes",
					"price","operator","alt_title","operator_value_price","decimal_place","product_id","expression","url","font","font_size","font_angle",
					"text_alignment","font_color","font_pos_x","font_width_x","font_width_y","font_pos_y","option_image","default_value","option_group_id","product_attribute",
					"matrix_dimension_x","matrix_operator_x","matrix_dimension_y","matrix_operator_y","matrix_csv_delimiter",
					"listimage_hover","listimage_style","listimage_items_per_line","matrix_filename","frontend_type","selectcombi_expression","css_class","sort_order_combiimage"
				)
			);
		$originalSelect = clone $select;

		if($id){
			$select->where('co.id = ?',$id)
				->order(array("co.sort_order ASC","co.id ASC"));
		}elseif($templateId){
			$select->where('template_id = ?',$templateId)
				->order(array("co.sort_order ASC","co.id ASC"));
		}

		$items = $connection->fetchAll($select);

		// prepaire options values and pricelist
		$allOptionIds = array();
		$optionArrayPos = array();
		$arrayPos = 0;
		foreach ($items as $i => $item) {
			$allOptionIds[] = $item['id'];
			$optionArrayPos[$item['id']] = $arrayPos;

			$items[$i]['values'] = array();
			$items[$i]['pricelist'] = array();

			$arrayPos = $arrayPos + 1;

		}

		// get all option values
		if(is_array($allOptionIds) && count($allOptionIds) > 0){
			$ids = join(',',$allOptionIds);

			$optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
			$optionValuesSelect = $connection->select()
				->from(
					array('cov' => $optionValueTable),
					array("id as id",
						"title as title",
						"value as value",
						"sku as sku",
						"option_id as option_id",
						"price as price",
						"sort_order as sort_order"                )
				);
			$optionValuesSelect->where('cov.option_id IN (' .$ids .')');
			$optionValuesSelect->order(array("cov.sort_order ASC","cov.value ASC"));

			$optionValues = $connection->fetchAll($optionValuesSelect);

		}else{
			$optionValues = array();
		}


		// set option values to option
		foreach ($optionValues as $optionValue) {
			$optionId = $optionValue['option_id'];
			$arrayPos = $optionArrayPos[$optionId];
			$items[$arrayPos]['values'][] = $optionValue;
		}


		if(is_array($allOptionIds) && count($allOptionIds) > 0){
			// get all pricelist values
			$pricelistTable = Mage::getSingleton("core/resource")->getTableName('configurator/pricelist');
			$pricelistSelect = $connection->select()
				->from(
					array('cp' => $pricelistTable),
					array(
						"id",
						"option_id",
						"operator",
						"value",
						"price"
					)
				);
			$pricelistSelect->where('cp.option_id IN (' .$ids .')');
			$pricelistSelect->order(array('CAST(`value` AS SIGNED) ASC'));

			$pricelistValues = $connection->fetchAll($pricelistSelect);
		}else{
			$pricelistValues = array();
		}

		// set pricelist entries to option
		foreach ($pricelistValues as $pricelist) {
			$optionId = $pricelist['option_id'];
			$arrayPos = $optionArrayPos[$optionId];
			$items[$arrayPos]['pricelist'][] = $pricelist;
		}


		if(count($items) > 0){
			$templateId = $items[0]['template_id'];
			$groups = Mage::getModel("configurator/optiongroup")->getCollection();
			$groups->addFilter('template_id',$templateId);
		}else{
			$groups = array();
		}

		if($id && $templateId){
			$originalSelect->where('template_id = ?',$templateId)
				->order(array("co.sort_order ASC","co.id ASC"));
			$itemsForSearch = $connection->fetchAll($originalSelect);
		}else{
			$itemsForSearch = $items;
		}

		foreach($items as $key => $item) {
			$parentId = $item['parent_id'];
			$parentTitle = Mage::helper('configurator')->__('None');
			if($parentId){
				foreach ($itemsForSearch as $parentItem) {
					if($parentItem['id'] == $parentId){
						$parentTitle = $parentItem['title'];
						break;
					}
				}
			}
			$items[$key]['parent_title'] = $parentTitle;

			$defaultValue = $item['default_value'];
			$defaultTitle = Mage::helper('configurator')->__('no default');
			if($defaultValue){
				foreach ($item['values'] as $optionValue) {
					if($optionValue['id'] == $defaultValue){
						$defaultTitle = $optionValue['title'];
						break;
					}
				}
			}
			$items[$key]['default_title'] = $defaultTitle;

			$optionGroupId = $item['option_group_id'];
			$optionGroupTitle = "";
			if($optionGroupId){
				foreach ($groups as $group) {
					if($group->getId() == $optionGroupId){
						$optionGroupTitle =  $group->getTitle();
						break;
					}
				}
			}
			$items[$key]['option_group'] = $optionGroupTitle;

			$frontendType = $item['frontend_type'];
			if($frontendType){
				$type = $item['type'];
				$items[$key]['type'] = $type .'-' .$frontendType;
			}
		}

		$items = array_values($items);
		return $items;
	}

	public function getOptionValueById($id){
		$items = $this->getOptionValueByIdOrTemplateId($id);

		$items = array_values($items);
		if(!empty($items)){
			$result = $items[0];
			return $result;
		}
		return false;
	}

	/**
	 * Get the options of the given item printable.
	 * @param $item Mage_Sales_Model_Order_Item
	 * @param bool $withHtml
	 */
	public function getOptionsPrintable($item, $withHtml=false) {
		$configuratorCustom = Mage::getModel('configurator/product_option_type_custom')->getTemplateOption(
			$item->getData('product_options'));
		$format = $withHtml ? 'value' : 'print_value';
		$printValue = $configuratorCustom[0]['value']['price'][$format];
		return $printValue;
	}

	/**
	 * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
	 * @return array|bool
	 */
	public function getOptionValues($item) {
		if (array_key_exists($item->getId(), $this->_optionValuesCache)) {
			return $this->_optionValuesCache[$item->getId()];
		}
		$options = null;
		if ($item instanceof Mage_Sales_Model_Quote_Item) {
			$options = $item->getOptions();
		} elseif ($item instanceof Mage_Sales_Model_Order_Item) {
			$options = $item->getProductOptions();
		} else {
			return false;
		}
		$optionCodes = array();
		foreach ($options as $option){
			if (isset($option['code'])) {
				$code = $option['code'];

				if ($code == 'option_ids') {
					$optionIds = explode(",", $option['value']);
					if (isset($optionIds)) {
						foreach ($optionIds as $optionId) {
							$optionCodes[] = "option_" . $optionId;
						}
					}
				}
				if (in_array($code, $optionCodes)) {
					if (isset($option['value'])) {
						$value = unserialize($option['value']);
						if (is_array($value)) {
							foreach ($value as $jsTemplateOption){
								if (isset($jsTemplateOption['template'])) {
									$optionValues = $jsTemplateOption['template'];
									$this->_optionValuesCache[$item->getId()] = $optionValues;
									return $this->_optionValuesCache[$item->getId()];
								}
							}
						}
					}
				}
			}
		}
		return false;
	}


	/**
	 * Returns the option values of the given item, identified by the given frontend type.
	 * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
	 * @param $frontendType string
	 * @return array may be empty!
	 */
	public function getOptionValuesByFrontendType($item, $frontendType) {
		$templateOptionItems = $this->getTemplateOptions($item);
		if (!$templateOptionItems) return false;
		$result = array();
		foreach ($templateOptionItems as $templateOptions) {
			foreach ($templateOptions as $optionId => $optionValue) {
				$option = $this->getCachedOptionById($optionId);
				/** @var $option Justselling_Configurator_Model_Option */
				if ($option && $option->getFrontendType() == $frontendType) {
					array_push($result, $optionValue);
				}
			}
		}
		return $result;
	}

	/**
	 * Returns the option values of the given item, identified by the given alt title.
	 * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
	 * @param $altTitle string
	 * @return array may be empty!
	 */
	public function getOptionValuesByAltTitle($item, $altTitle) {
		$templateOptionItems = $this->getTemplateOptions($item);
		if (!$templateOptionItems) return false;
		$result = array();
		foreach ($templateOptionItems as $templateOptions) {
			foreach ($templateOptions as $optionId => $optionValue) {
				$option = $this->getCachedOptionById($optionId);
				if ($option && $option->getAltTitle() == $altTitle) {
					array_push($result, $optionValue);
				}
			}
		}
		return $result;
	}

	/**
	 * Retrieve configurator template options from quote item or order item.
	 * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
	 * @return array|bool
	 * @author Bodo Schulte
	 */
	public function getTemplateOptions($item) {
		$templateOptions = array();
		if ($item instanceof Mage_Sales_Model_Quote_Item) {
			$options = $item->getOptions();
			$optionCodes = array();
			foreach ($options as $option){
				if (isset($option['code'])) {
					$code = $option['code'];
					if ($code == 'option_ids') {
						$optionIds = explode(",", $option['value']);
						if (isset($optionIds)) {
							foreach ($optionIds as $optionId) {
								$optionCodes[] = "option_" . $optionId;
							}
						}
					}
					if (in_array($code, $optionCodes)) {
						if (isset($option['value'])) {
							$value = unserialize($option['value']);
							if (is_array($value)) {
								foreach ($value as $jsTemplateOption){ // now we have justSelling TemplateOptions
									if (isset($jsTemplateOption['template'])) {
										$optionValues = $jsTemplateOption['template'];
										if (is_array($optionValues)) {
											$templateOptions[$option['option_id']] = $optionValues;
										}
									}
								}
							}
						}
					}
				}
			}
		} elseif ($item instanceof Mage_Sales_Model_Order_Item) {
			$options = $item->getProductOptions();
			if (isset($options['options'])) {
				foreach ($options['options'] as $option) {
					if ($option['option_type'] == 'configurator') {
						$value = unserialize($option['option_value']);
						if (is_array($value)) {
							foreach ($value as $jsTemplateOption){ // now we have justSelling TemplateOptions
								if (isset($jsTemplateOption['template'])) {
									$optionValues = $jsTemplateOption['template'];
									if (is_array($optionValues)) {
										$templateOptions[$option['option_id']] = $optionValues;
									}
								}
							}
						}
					}
				}
			}
		} else {
			return false;
		}
		return empty($templateOptions) ? false : $templateOptions;
	}

	/**
	 * Retrieve a single configurator option out of the given Quote or Sales Item, identified by the option-key (alt-title)
	 * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
	 * @param $optionKey String
	 * @return string|bool
	 */
	public function getTemplateOptionValue($item, $optionKey) {
		$allOptions = $this->getTemplateOptions($item);
		if ($allOptions) {
			foreach($allOptions as $options) {
				$optionIds = array_keys($options);
				$optionCollection = Mage::getModel("configurator/option")->getCollection();
				$optionCollection->addFieldToFilter('id', array('in' => $optionIds));
				foreach ($optionCollection as $option) {
					if ($option && $option->getId() && $option->getAltTitle() == $optionKey) {
						return $options[$option->getId()];
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns the (locally cached) option identified by given id.
	 * @param $id
	 * @return Justselling_Configurator_Model_Option
	 */
	private function getCachedOptionById($id) {
		if (is_null($this->_optionsLocalCache)) {
			/** @var $options Justselling_Configurator_Model_Mysql4_Option_Collection */
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->load();
			$this->_optionsLocalCache = $options;
		}
		$item = $this->_optionsLocalCache->getItemById($id);
		return $item;
	}

	public function getHelpIqLink($block, $class, $helpiq_id, $title) {
		if (Mage::getStoreConfig('productconfigurator/general/onlinehelp')) {
			$html = "<a ";
			$html .= "class=\"".$class."\" ";
			$title = Mage::helper('configurator')->__($title);
			$html .= "helpiq-id=\"$helpiq_id\" ";
			$html .= "helpiq-title=\"".$title."\" ";
			$html .= "href=\"javascript:;\" ";
			$html .= " >";

			$html .= "<img ";
			$imagefile = $block->getSkinUrl("images/justselling/info.png");
			$html .= "src=\"".$imagefile."\" ";
			$html .= "alt=\"".Mage::helper('configurator')->__('Help')."\" ";
			$html .= "title=\"".Mage::helper('configurator')->__('Help')."\" ";
			$html .= "/>";

			$html .= "</a>";

			return $html;
		}

		return "";
	}

	public function getOptionsForTemplateIdAsArray($templateId){
		$optionArray = array();
		if($templateId){
			$options = Mage::getModel("configurator/option")->getCollection();
			$options->addFieldToFilter("template_id",$templateId);
			$options->addOrder('sort_order', 'ASC');

			foreach($options as $option){
				$optionArray[] = array('id'=> $option->getId(), 'title' => $option->getTitle());
			}
		}
		return $optionArray;
	}

	public function hasTemplateOption($item) {
		$templateOptions = $this->getTemplateOptions($item);
		if(!$templateOptions){
			return 0;
		}else{
			return 1;
		}
	}


	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return int
	 */
	public function getCountOfLinkedConfiguratorTemplates($product) {
		$productOptions = $product->getOptions();
		$count = 0;
		/* @var $productOption Mage_Catalog_Model_Product_Option */
		foreach ($productOptions as $productOption) {
			$type = $productOption->getType();
			if ($type == 'configurator') {
				$count++;
			}
		}
		return $count;
	}

	public function isConfiguratorProduct($product) {
		if ($this->getCountOfLinkedConfiguratorTemplates($product)) {
			return true;
		}
		return false;
	}

	public function hasConfiguratorProduct($order) {
		$hasConfiguratorProduct = false;
		/** @var Mage_Sales_Model_Order $order */
		if ($order) {
			foreach ($order->getAllItems() as $orderItem) {
				$result = $this->isConfiguratorProduct($order->getProduct());
				if ($result == true) {
					$hasConfiguratorProduct = true;
				}
			}
			return $hasConfiguratorProduct;
		}
		return false;
	}

    /**
     * Returns the tax rate of the default store view.
     * @param $product
     * @return float|int
     */
    protected function getRateOfDefaultStore($product) {
        if ($product === null) $product = Mage::registry('current_product');
        if (!$product || !$product->getId()) return 0;
        $defaultStore = Mage::app()->getDefaultStoreView();
        $defaultCountryId = Mage::getStoreConfig('tax/defaults/country', $defaultStore);
        $defaultPostcode = Mage::getStoreConfig('tax/defaults/postcode', $defaultStore);
        $defaultRegion = Mage::getStoreConfig('tax/defaults/region', $defaultStore);
        $customer = Mage::getSingleton('tax/calculation')->getCustomer();
        $customerTaxClass = $customer ? $customer->getTaxClassId() : Mage::getModel('customer/group')
            ->getTaxClassId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        $request = new Varien_Object();
        $request->setCountryId($defaultCountryId)
            ->setRegionId($defaultRegion)
            ->setPostcode($defaultPostcode)
            ->setStore($defaultStore)
            ->setCustomerClassId($customerTaxClass);
        $taxClassId = $product->getTaxClassId();
        $percentage = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
        return floatval($percentage);
    }


	public function getCurrentRate($product){
        if ($product === null) {
            $product = Mage::registry('current_product');
        }
        if (!$product || !$product->getId()) return 0;
        $store = Mage::app()->getStore();

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $customerTaxClassId = $quote->getCustomer() ? $quote->getCustomer()->getTaxClassId() : null;
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
            $quote->getShippingAddress(), $quote->getBillingAddress(), $customerTaxClassId, $store);
        $taxClassId = $product->getTaxClassId();
        $percentage = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
        return floatval($percentage);
	}

	public function productViewPriceInclTax(){
		$productViewPriceInclTax = 0;
		/* 1=exclTax 2=inclu 3=exclAndIncl */
		$productTaxType = Mage::getStoreConfig('tax/display/type');

		if($productTaxType == 2 || $productTaxType == 3){
			$productViewPriceInclTax = 1;
		}
		return $productViewPriceInclTax;

	}

	public function getTaxFactor($product = null){
        $taxFactor = 1;
        $currentTaxRate = round($this->getCurrentRate($product), 2);
        $taxRateOfDefaultStore = round($this->getRateOfDefaultStore($product), 2);

        /* TODO Get configuration from CART if we are in cart and not on the product page */
        $isCatalogPriceInclTax = Mage::getStoreConfig('tax/calculation/price_includes_tax');
        $isProductViewPriceInclTax = $this->productViewPriceInclTax();

        if (!$isCatalogPriceInclTax == '1' && $isProductViewPriceInclTax){
            $taxFactor =  1  + ($currentTaxRate / 100);

        } else if ($isCatalogPriceInclTax == '1' && !$isProductViewPriceInclTax){
            $taxFactor =  1  - ($currentTaxRate / 100);

        } else if ($isCatalogPriceInclTax
            && $isProductViewPriceInclTax
            && $currentTaxRate != $taxRateOfDefaultStore) {
            $taxFactor = 100 / (100 + $taxRateOfDefaultStore); // would be excl. tax
            $taxFactor = $taxFactor * (1 + ($currentTaxRate / 100));
        }
        if (Js_SystemMode::isScopeDevelopment()) {
            Js_Log::log(sprintf('currentTaxRate=%s, rateOfDefaultStore=%s, viewInclTax=%s => calc.factor=%s',
                $currentTaxRate, $taxRateOfDefaultStore,
                $isProductViewPriceInclTax, $taxFactor), $this);
        }
        return $taxFactor;
	}

	public function getPriceInclExclTax($price, $product = null){
		$taxFactor = Mage::helper("configurator")->getTaxFactor($product);
		$price = round($price * $taxFactor, 2);
		return $price;
	}

	public  function checkOptionValuesInStock($values){

		if (Mage::getStoreConfig('productconfigurator/stock/active') && Mage::registry("stock_status")) {
			$stockStatus = Mage::registry("stock_status");
			if(isset($stockStatus['values']) && is_array($stockStatus['values']) && sizeof($stockStatus['values']) > 0){
				$tempvalues = array();
				foreach($values as $value){
					if(array_key_exists($value->getId(), $stockStatus['values'])){
						if($stockStatus["values"][$value->getId()]){
							$tempvalues[] = $value;
						}
					}else{
						$tempvalues[] = $value;
					}
				}
				$values = $tempvalues;
			}
		}
		return $values;
	}

	public function ieLinebreakFix($value){
		if(preg_match('/(?i)msie/',$_SERVER['HTTP_USER_AGENT'])){
			$value = str_replace("\n\n","\r\n",$value);
		}
		return $value;
	}

	public function linebreaksToHtmlBr($value){
		$value = str_replace("\r\n","<br/>",$value);
		$value = str_replace("\n","<br/>",$value);
		return $value;
	}

    public function getConfiguratorMediaFolder() {
        return Mage::getBaseDir('media') .DS .'configurator'. DS;
    }

}