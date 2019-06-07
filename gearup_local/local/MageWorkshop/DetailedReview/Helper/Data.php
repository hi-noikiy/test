<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PROS = 'detailedreview/proscons/pros';
    const XML_PATH_CONS = 'detailedreview/proscons/cons';

    const REFERER_QUERY_PARAM_NAME = 'referer';

    protected $baseMediaDir = null;
    protected $baseDir = null;
    protected $baseMediaUrl = null;
    protected $baseSkinUrl = null;
    protected $baseSkinDir = null;
    protected $optionalCategories = array();
    protected $_currentProtocolSecurity;
    
    /**
     * @param string $paramName
     * @param string $paramValue
     * @return string
     */
    public function getCurrentUrlWithNewParam($paramName, $paramValue)
    {
        $params = '?feedback=1&';
        $request = Mage::app()->getRequest();
        $paramSources = $request->getParamSources();
        $request->setParamSources(array('_GET'));
        foreach ($request->getParams() as $getParam => $getValue) {
            if ($getParam == 'feedback' || ($getParam == $paramName && $getValue == 'true') || $getParam == 'show_popup' || is_object($getValue)) {
                continue;
            }
            $params .= $getParam . '=' . (($getParam == $paramName) ? $paramValue : $getValue) . '&';
        }
        if (is_null($request->getParam($paramName))) {
            $params .= $paramName . '=' . $paramValue . '&';
        }
        $request->setParamSources($paramSources);
        return substr($params, 0, -1);
    }
    
    /**
     * @param string $paramName
     * @param string $value
     * @return bool
     */
    public function isInGetParams($paramName, $value = '')
    {
        $request = Mage::app()->getRequest();
        $paramSources = $request->getParamSources();
        $request->setParamSources(array('_GET'));
        $isInGetParams = false;
        if ($value) {
            $isInGetParams = (!is_null($request->getParam($paramName))&& $request->getParam($paramName) == $value ) ? true : false;
        } else {
            if (!is_array($paramName)) {
                $paramName = array($paramName);
            }
            foreach ($paramName as $param) {
                if (!is_null($request->getParam($param))) {
                    $isInGetParams = true;
                    break;
                }
            }
        }
        $request->setParamSources($paramSources);
        return $isInGetParams;
    }
    
    /**
     * @param $imageUrl
     * @param null $width
     * @param null $height
     * @param int $quality
     * @return string
     */
    public function getResizedImage($imageUrl, $width = null, $height = null, $quality = 100)
    {
        if (is_null($this->baseMediaDir)) {
            $this->baseDir = Mage::getBaseDir();
            $this->baseMediaDir = Mage::getBaseDir('media');
            $this->baseMediaUrl = Mage::getBaseUrl('media');
            
            $this->baseSkinDir = Mage::getBaseDir('skin');
            $this->baseSkinUrl = Mage::getBaseUrl('skin');
        }
        
        $path = (strpos($imageUrl, '/skin/') !== false)
            ?  str_replace($this->baseSkinUrl, '', $imageUrl)
            : str_replace($this->baseMediaUrl, '', $imageUrl);
        
        if (!$path) {
            return '';
        }
        
        $imageResized = urlencode('catalog/resized/' . $width . 'x' . $height . DS . $path);
        
        $baseDir = $this->baseDir;
        $baseResizedDir = $this->baseMediaDir . DS . 'catalog' . DS . 'resized' . DS . $width . 'x' . $height . DS ;
        if (!file_exists($baseResizedDir . $path)) {
            if (!file_exists($baseDir) || !is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }
            if (!file_exists($baseResizedDir)  || !is_dir($baseResizedDir))
                mkdir($baseResizedDir, 0777, true);
            
            $imgDir = (strpos($imageUrl, '/skin/') !== false) ? $this->baseSkinDir : $this->baseMediaDir;
            $filePath = $imgDir . DS . $path;
            
            if (!file_exists($filePath) || (file_exists($filePath) && !is_file($filePath))) {
                return '';
            }
            $imageObj = new Varien_Image($filePath);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepTransparency(true);
            $imageObj->keepFrame(false);
            $imageObj->quality($quality);
            $imageObj->resize($width, $height);
            Mage::dispatchEvent('detailedreview_resize_image', array(
                'base_resized_dir'  => $baseResizedDir,
                'image' => $imageObj
            ));
            $imageObj->save($baseResizedDir . $path);
        }
        
        return $this->baseMediaUrl . $imageResized;
    }
    
    /**
     * @param string $action
     * @return mixed
     */
    public function fixFormActionForIE($action)
    {
        return preg_replace('/(.*)\//', '$1', $action);
    }
    
    /**
     * @param null|int $store
     * @return bool
     */
    public function canSendNewReviewEmail($store = null)
    {
        return Mage::getStoreConfigFlag(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_ENABLED, $store);
    }
    
    /**
     * @param null|int $store
     * @return bool
     */
    public function canSendNewReviewEmailToCustomer($store = null)
    {
        return Mage::getStoreConfigFlag(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_ENABLED_FOR_CUSTOMER, $store);
    }
    
    /**
     * @param string $field
     * @param null|Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function checkFieldAvailable($field, $type = 'form', $product = null)
    {
        if (Mage::getStoreConfig('detailedreview/show_review_' . $type . '_settings/allow_' . $field)) {
            if ($field == 'review_graph') {
                return true;
            }
            if ($category = $this->getCategoryWithConfig($product)) {
                if (!$reviewFieldsAvailable = $category->getData('review_fields_available')) {
                    return true;
                }
                if (is_string($reviewFieldsAvailable)) {
                    $reviewFieldsAvailable =  explode(',', $reviewFieldsAvailable);
                }
                foreach ($reviewFieldsAvailable as $availableField) {
                    if ( $availableField == $field ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * @param null|Mage_Catalog_Model_Product $product
     * @param null|string $param
     * @return bool|Mage_Catalog_Model_Category
     */
    public function getCategoryWithConfig($product = null, $param = null)
    {
        if (is_null($product) && !$product = Mage::registry('current_product')) {
            return false;
        }
        $productId = $product->getId();
        if ( is_null($param) ) {
            if (!isset($this->optionalCategories[$productId])) {
                /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
                $categories = $product->getCategoryCollection();
                $this->addReviewConfigToSelect($categories)
                    ->tryToFindOptionalCategory($categories, $productId, $param);
                if (!isset($this->optionalCategories[$productId])) {
                    /** @var Mage_Catalog_Model_Category $category */
                    $category = $categories->getFirstItem();
                    if ($category->getLevel() <= 1) {
                        $this->optionalCategories[$productId] = $category;
                    } else {
                        $this->getParentCategories($category, $productId, $param);
                        if (!isset($this->optionalCategories[$productId])) {
                            $this->optionalCategories[$productId] = $categories->getLastItem();
                        }
                    }
                }
            }
            return $this->optionalCategories[$productId];
        } else {
            if (!isset($this->optionalCategories[$param][$productId])) {
                /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
                $categories = $product->getCategoryCollection();
                $this->addReviewConfigToSelect($categories)
                    ->tryToFindOptionalCategory($categories, $productId, $param);
                if (!isset($this->optionalCategories[$param][$productId])) {
                    /** @var Mage_Catalog_Model_Category $category */
                    $category = $categories->getFirstItem();
                    $this->getParentCategories($category, $productId, $param);
                    if (!isset($this->optionalCategories[$param][$productId])) {
                        $this->optionalCategories[$param][$productId] = $this->_getDefaults($param);
                    }
                }
            }
            return $this->optionalCategories[$param][$productId];
        }
    }
    
    /**
     * @param Mage_Catalog_Model_Category $category
     * @param int $productId
     * @param string $param
     * @return $this
     */
    protected function getParentCategories($category, $productId, $param)
    {
        $parentsCategories = explode('/', preg_replace('/\d+\/(.*)\/.*/','$1', $category->getPath()));
        /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $parentsCategories));
        $this->addReviewConfigToSelect($categories)
            ->tryToFindOptionalCategory($categories, $productId,$param);
        return $this;
    }
    
    /**
     * @param Mage_Catalog_Model_Resource_Category_Collection $categories
     * @param int $productId
     * @param string $param
     * @return mixed
     */
    protected function tryToFindOptionalCategory($categories, $productId, $param)
    {
        /** @var Mage_Catalog_Model_Category $category */
        foreach ($categories as $category) {
            if (is_null($param)) {
                if (!is_null($category->getData('review_fields_available'))) {
                    $this->optionalCategories[$productId] = $category;
                }
            } else {
                if ($category->getData($param) === '0') {
                    $this->optionalCategories[$param][$productId] = $category;
                }
            }
        }
        return $this;
    }
    
    /**
     * @param Mage_Catalog_Model_Resource_Category_Collection $categories
     * @return $this
     */
    protected function addReviewConfigToSelect($categories)
    {
        $categories
            ->addAttributeToSelect('review_fields_available')
            ->addAttributeToSelect('use_parent_proscons_settings')
            ->addAttributeToSelect('pros')
            ->addAttributeToSelect('cons')
            ->setOrder('level','DESC');
        return $this;
    }
    
    /**
     * @param string $param
     * @return Varien_Object
     */
    protected function _getDefaults($param)
    {
        $settings = new Varien_Object();
        switch ($param) {
            case 'use_parent_proscons_settings':
                $settings->setData(array(
                    'pros' => Mage::getStoreConfig(self::XML_PATH_PROS),
                    'cons' => Mage::getStoreConfig(self::XML_PATH_CONS)
                ));
                break;
            default:
                break;
        };
        return $settings;
    }
    
    /**
     * @return bool
     */
    public function isUserAbleToWriteReview()
    {
        $helper = Mage::helper('customer');
        if ($helper->isLoggedIn()) {
            return (bool) !$helper->getCustomer()->getIsBannedWriteReview();
        } else {
            /** @var MageWorkshop_DetailedReview_Model_AuthorIps $authorIpModel */
            $authorIpModel = Mage::getModel('detailedreview/authorIps')->load(Mage::helper('core/http')->getRemoteAddr(), 'remote_addr');
            if ($authorIpModel->getId()) {
                if ( Mage::app()->getLocale()->date($authorIpModel->getExpirationTime()) > Mage::app()->getLocale()->date() ) {
                    return false;
                }
            }
            return true;
        }
    }
    
    /**
     * @return string
     */
    public function getDetailReviewJsUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'detailedreview';
    }
    
    /**
     * @return string
     */
    public function getDetailReviewCssUrl()
    {
        return Mage::getDesign()->getSkinUrl('css/detailedreview');
    }
    
    /**
     * @param string $type
     * @return array
     */
    public function getProsConsValues($type)
    {
        $values = array();
        $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()
            ->setType($type);
        /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $item */
        foreach ($collection as $item) {
            $values[] = array('label' => $item->getName(), 'value' => $item->getEntityId());
        }
        return $values;
    }
    
    /**
     * @return bool
     */
    public function checkEnabledRatings()
    {
        $collection = Mage::getModel('rating/rating')->getCollection()
            ->setStoreFilter(Mage::app()->getStore()->getId());
        return (bool) $collection->getSize();
    }
    
    /**
     * @param string $list
     * @param string $type
     * @return string
     */
    public function getProsConsText($list, $type)
    {
        $prosConsIds = explode(',', $list);
        $prosCons = Mage::getModel('detailedreview/review_proscons')->getCollection();
        $prosCons->setType($type)
            ->addFieldToFilter('entity_id', array('in' => $prosConsIds))
            ->load();
        $names = $prosCons->getColumnValues('name');
        return(implode(', ', $names));
    }
    
    /**
     * @return string - empty string if the file was not found
     */
    public function checkPackageFile()
    {
        // Find the package
        $packageFile = '';
        $downloaderFiles = glob(getcwd() . DS . 'var' . DS . 'package' . DS . '*.xml');
        foreach ($downloaderFiles as $v) {
            $name = explode(DS, $v);
            $checkName = substr($name[count($name) - 1], 0, -4);
            if (strpos($checkName,'DetailedReview') !== false) {
                $packageFile = 'var' . DS . 'package' . DS . $name[count($name) - 1];
            }
        }
        return $packageFile;
    }
    
    /**
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/MageWorkshop_DetailedReview/version');
    }
    
    /**
     * @return array
     */
    public function uploadImages()
    {
        $files = array(
            'success' => true,
            'images'  => array(),
            'errors'  => array()
        );
        
        $allowedImageTypes = array('jpg', 'jpeg', 'gif', 'png');
        if ($images = Mage::app()->getRequest()->getParam('image')) {
            $files['images'] = $images;
        }
        
        if (array_key_exists('image', $_FILES) && count($_FILES['image']) > 1 && !empty($_FILES['image']['name'][0])) {
            foreach ($_FILES['image']['name'] as $index => $value) {
                $image = array(
                    'name'      => $_FILES['image']['name'][$index],
                    'type'      => $_FILES['image']['type'][$index],
                    'tmp_name'  => $_FILES['image']['tmp_name'][$index],
                    'error'     => $_FILES['image']['error'][$index],
                    'size'      => $_FILES['image']['size'][$index]
                );
                
                if (empty($image['name'])){
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('Image was not uploaded!');
                    continue;
                }
                
                $filename = uniqid() . stripslashes($image['name']);
                
                $imageValidatorModel = Mage::getModel('core/file_validator_image');
    
                /*
                 * Condition was added for checking if Mage_Core_Model_File_Validator_Image is exists.
                 * Class Mage_Core_Model_File_Validator_Image doesn't exist in Magento version less than 1.9.2.3
                 * MageWorkshop_Core_Model_File_Validator_Image is full copy of Mage_Core_Model_File_Validator_Image
                 */
                if ($imageValidatorModel === false) {
                    /** @var MageWorkshop_Core_Model_File_Validator_Image $imageValidatorModel */
                    $imageValidatorModel = Mage::getModel('drcore/file_validator_image');
                }
    
                $imageValidatorModel->setAllowedImageTypes($allowedImageTypes);
                
                try {
                    $imageValidatorModel->validate($image['tmp_name']);
                } catch(Exception $e) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('Error while uploading file "%s". Disallowed file type. Only "jpg", "jpeg", "gif", "png" are allowed.', $image['name']);
                    continue;
                }
                
                $size = filesize($image['tmp_name']);
                $maxSize = Mage::getStoreConfig('detailedreview/image_options/max_image_size');
                $maxUploadSize = Mage::helper('detailedreview')->getMaxUploadSize();
                if (($size > $maxSize * 1024 * 1024)||($size > $maxUploadSize * 1024 * 1024)) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('You have exceeded the size limit!. Max %s file size %sMb', $image['name'] , $maxSize);
                    continue;
                }
                
                $dimension = getimagesize($image['tmp_name']);
                $minWidth = Mage::getStoreConfig('detailedreview/image_options/min_image_width');
                $minHeight = Mage::getStoreConfig('detailedreview/image_options/min_image_height');
                if ($dimension[0] < $minWidth || $dimension[1] < $minHeight) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('One of your image dimensions is less then %dpx', $minWidth);
                    continue;
                }
                
                
                $folder = 'media' . DS . 'detailedreview';
                $uploader = new Varien_File_Uploader($image);
                $uploader->setAllowedExtensions($allowedImageTypes)
                    ->setAllowRenameFiles(false)
                    ->setFilesDispersion(1);
                $newFileName = $uploader->getCorrectFileName($filename);
                Mage::dispatchEvent('detailedreview_upload_images', array(
                    'image'  => $image,
                    'uploader' => $uploader
                ));
                try {
                    $uploader->save($folder, $newFileName);
                } catch (Exception $e) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('Some problems appeared while saving image.');
                    continue;
                }
                $newFileName = $uploader->getUploadedFileName();
                $files['images'][] = 'detailedreview' . $newFileName;
            }
        }
        return $files;
    }
    
    /**
     * @return int
     */
    public function getMaxUploadSize()
    {
        return (int) min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
    }
    
    /**
     * @param string $key
     * @return int
     */
    public function checkAvailableFilter($key)
    {
        $reviewCollection = clone Mage::getSingleton('detailedreview/review')->getReviewsCollection();
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $totalsCollection */
        switch ($key) {
            case 'verified_buyers':
                $reviewCollection->addVerifiedBuyersFilter();
                break;
            case 'video':
                $reviewCollection->addVideoFilter();
                break;
            case 'images':
                $reviewCollection->addImagesFilter();
                break;
            case 'admin_response':
                $reviewCollection->addManyResponseFilter();
                break;
            case 'highest_contributors':
                $reviewCollection->addHighestContributorFilter();
                break;
            default:
                break;
        }
        return $reviewCollection->getSize();
    }
    
    /**
     * @param string $url
     * @param array $param
     * @return string
     */
    public function addRequestParam($url, $param)
    {
        $startDelimiter = (false === strpos($url,'?'))? '?' : '&';
        
        $arrQueryParams = array();
        foreach ($param as $key=>$value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }
            
            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif (is_null($value)) {
                $arrQueryParams[] = $key;
            } else {
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        $url .= $startDelimiter . implode('&', $arrQueryParams);
        
        return $url;
    }
    
    /**
     * @param null|string $url
     * @return string
     */
    public function getVideoIFrame($url = null)
    {
        if (is_null($url)) {
            return '';
        }
        $width = Mage::getStoreConfig('detailedreview/video_options/width_video_preview');
        $height = Mage::getStoreConfig('detailedreview/video_options/height_video_preview');
        
        if (strpos($url, 'youtube') !== false || strpos($url, 'youtu') !== false) {
            if (strpos($url, 'watch?v=') !== false) {
                if (strpos($url, '&') !== false) {
                    $videoEnd = strpos($url, '&') - 1;
                } else {
                    $videoEnd = strlen($url);
                }
                $videoStart = strpos($url, 'watch?v=') + 8;
                $video = substr($url, $videoStart, (( $videoEnd - $videoStart ) + 1));
            } else {
                $tmpArr = explode("/", $url);
                $video = '';
                foreach ($tmpArr as $key => $value) {
                    if (!isset($tmpArr[$key + 1])) {
                        $video = $tmpArr[$key];
                    }
                }
                if ($video && strpos($video, 'watch?feature=') !== false) {
                    $linkParts = explode("v=",$video);
                    if (isset($linkParts[1])) {
                        $video = $linkParts[1];
                    }
                }
            }
            return '<iframe width="'. $width .'" height="'. $height .'" src="//www.youtube.com/embed/'. $video .'?wmode=transparent" frameborder="0" allowfullscreen></iframe>';
        }
        
        if (strpos($url, 'vimeo') !== false) {
            $tmpArr = explode("/", $url);
            $video = '';
            foreach ($tmpArr as $key => $value) {
                if(!isset($tmpArr[$key + 1])) {
                    $video = $tmpArr[$key];
                }
            }
            return '<iframe src="//player.vimeo.com/video/'. $video .'?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=7c9c70" width="'. $width .'" height="'. $height .'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
        }
        return '';
    }
    
    /**
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
     * @param bool $middle
     * @return mixed|string
     */
    public function smartyModifierTruncate($string, $length = 120, $etc = '...', $breakWords = false, $middle = false)
    {
        if (strlen($string) > $length) {
            $length -= strlen($etc);
            if (!$breakWords && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }
            if (!$middle) {
                return substr($string, 0, $length).$etc;
            } else {
                return substr($string, 0, $length/2) . $etc . substr($string, -$length / 2);
            }
        } else {
            return $string;
        }
    }
    
    /**
     * @return string
     */
    public function getCurrentTheme()
    {
        return (string) Mage::getStoreConfig('detailedreview/settings/theme');
    }
    
    /**
     * @param Mage_Core_Block_Template $block
     * @return Mage_Core_Block_Template
     */
    public function applyTheme($block)
    {
        $theme = $this->getCurrentTheme();
        if ($theme == 'standard') {
            return $block;
        }
        $currentTemplate = $block->getTemplate();
        $newTemplate = str_replace('detailedreview', 'detailedreview/' . $theme, $currentTemplate);
        $block->setTemplate($newTemplate);
        return $block;
    }
    
    public function checkHttps()
    {
        if (empty($this->_currentProtocolSecurity)) {
            $store = Mage::app()->getStore();
            if ($store->isAdmin()) {
                $secure = $store->isAdminUrlSecure();
            } else {
                $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
            }
            $this->_currentProtocolSecurity = $secure;
        }
        return $this->_currentProtocolSecurity;
    }
    
    public function checkProductVisibility($productId)
    {
        return $visible = Mage::getModel('catalog/product')->load($productId)->isVisibleInSiteVisibility();
    }
    
    public function checkLicenseKey() {
        $store = Mage::app()->getStore();
        if ($store->isAdmin()) {
            $secure = $store->isAdminUrlSecure();
        } else {
            $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
        }
        if (Mage::app()->getRequest()->getParam('store')) {
            $store = Mage::getModel('core/store')->load(Mage::app()->getRequest()->getParam('store'), 'code');
        }
        $serverHost = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure),PHP_URL_HOST);
        if (!$serverHost) {
            $serverHost = Mage::app()->getRequest()->getHttpHost();
        }
        $serverHost = str_replace('www.', '', $serverHost);

        if (ip2long($serverHost) !== false) {
            return true;
        }
        if (preg_match('/(^(www\.)?(dev\.|test\.).*|.*(local|test)$)/i', $serverHost) || $serverHost == 'localhost') {
            return true;
        } else {
            if (!$this->checkObserverIdentity($serverHost)) {
                return false;
            }
            if (md5($serverHost . 'someeasykeyword') != trim(Mage::getStoreConfig('detailedreview/license/key', $store->getId()))) {
                return $this->checkLicenseForDifferentStoreUrl($store);
            } else {
                return true;
            }
        }
    }
    
    public function getAutoApproveFlag()
    {
        $autoApproveFlag = false;
        $customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $autoApproveGroups = Mage::getStoreConfig('detailedreview/settings/auto_approve');
        if ( $autoApproveGroups || $autoApproveGroups === "0") {
            $autoApproveGroups = explode(',', $autoApproveGroups);
            $autoApproveFlag = in_array($customerGroup, $autoApproveGroups);
        }
        return $autoApproveFlag;
    }
    
    /**
     * @return array
     */
    public function getAllProductsIdForVerifiedBuyer()
    {
        $result = array();
        if (Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer')) {
            $result = $this->getCustomerProductIds();
        }
        return $result;
    }
    
    /**
     * @return array
     */
    
    /** return $customerInfo */
    public function getCustomerInfo()
    {
        /** @var MageWorkshop_DetailedReview_Model_CustomerIdentifier $customerInfo */
        $customerInfo = Mage::getSingleton('detailedreview/customerIdentifier');
        $customerIdentifierData = Mage::getModel('core/cookie')->get('customerIdentifier');
        
        if ($customerIdentifierData) {
            $customerInfo->setData(json_decode($customerIdentifierData, true));
            return $customerInfo;
        }
        
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
        if ($customer->getId()) {
            $customerInfo->setCustomerEmail($customer->getEmail());
            $customerInfo->setCustomerId($customer->getId());
        }
        
        $customerEmail = Mage::app()->getRequest()->getParam('customer_email');
        
        if (!$customer->getId() && $customerEmail) {
            $customerInfo->setCustomerEmail($customerEmail);
            $customerInfo->setCustomerId(null);
        }
        
        return $customerInfo;
    }
    
    /**
     * @param Varien_Object $customerData
     * @param int $productId
     * @return array
     */
    public function getReviewsPerProductByCustomer($customerData, $productId)
    {
        $reviewCollection = Mage::getModel('review/review')->getCollection();
        $reviewCollection->addFieldToFilter(
            array('customer_id', 'customer_email'),
            array($customerData->getCustomerId(), $customerData->getCustomerEmail())
        );
        $reviewCollection->addFieldToFilter('entity_pk_value', $productId);
        $reviewIds = $reviewCollection->getAllIds();
        return $reviewIds;
    }
    
    /**
     * @param Varien_Object $customerData
     * @return array
     */
    public function getOrderedProductsByCustomer($customerData)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter(
            array('customer_id', 'customer_email'),
            array($customerData->getCustomerId(), $customerData->getCustomerEmail())
        );
        $productCollection = $this->getProductsByOrders($orderCollection->getAllIds());
        $productIds = $productCollection->getAllIds();
        return $productIds;
    }
    
    /**
     * @param array $orderIds
     * @param bool $showOutOfStockProduct
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsByOrders($orderIds, $showOutOfStockProduct = true)
    {
        $productIds = array();
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('entity_id', array('in' => $orderIds))
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()));
        
        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        array_unique($productIds);
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        
        /** @var Mage_Catalog_Model_Product_Visibility $productVisibleModel */
        $productVisibleModel = Mage::getModel('catalog/product_visibility');
        
        $productCollection->addAttributeToFilter('visibility', array('in' => $productVisibleModel->getVisibleInSiteIds()))
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        
        if (!$showOutOfStockProduct) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
        }
        
        $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
        return $productCollection;
    }
    
    protected function checkLicenseForDifferentStoreUrl($store) {
        $secure = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true),PHP_URL_HOST);
        $unsecure = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, false),PHP_URL_HOST);
        $secureUrl = str_replace('www.', '', $secure);
        $unsecureUrl = str_replace('www.', '', $unsecure);

        return md5($unsecureUrl . $secureUrl . 'someeasykeyword') == trim(Mage::getStoreConfig('detailedreview/license/key', $store->getId()));
    }
    
    protected function checkObserverIdentity($serverHost) {
        return Mage::getSingleton('detailedreview/observer')->checkObserverKey() == md5('checkobserver' . $serverHost . date('z'));
    }
    
    /**
     * @param array $params
     * @return array
     */
    public function prepareShareReviewData($params)
    {
        $data = array(
            'sender'          => array(
                'name'  => Mage::getStoreConfig('trans_email/ident_sales/name'),
                'email' => Mage::getStoreConfig('trans_email/ident_sales/email')
            ),
            'recipient_email' => $params['drw-email-share-mail-to'],
            'copy_to_path'    => Mage::getStoreConfig(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER)
                ? MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER
                : 'trans_email/ident_support/email',
            'copy_method'     => 'bcc',
            'template_id'     => Mage::getStoreConfig(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_XML_PATH_SHARE_EMAIL_TEMPLATE),
            'template_params' => array(
                'customer_name'      => $params['drw-email-share-customer-name'],
                'customer_email'     => $params['drw-email-share-customer-email'],
                'message'            => $params['drw-email-share-mail-body'],
                'review_link'        => $params['drw-email-share-link'],
                'review_title'       => $params['drw-email-share-subject'],
                'share_review_image' => $params['drw-email-share-image'],
                'img_alt'            => $this->__('Share Image')
            )
        );
        
        return $data;
    }
    
    /**
     * @param $product_id
     */
    public function reindexProductAttr($product_id)
    {
        $indexer = Mage::getSingleton('index/indexer')
            ->getProcessByCode(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_PRODUCT_ATTR_INDEXER_CODE);
        if ($indexer) {
            if ($indexer->getMode() === Mage_Index_Model_Process::MODE_REAL_TIME) {
                $reindex = Mage::getResourceModel('detailedreview/product_indexer');
                try {
                    $reindex->reindexMostReviewed($product_id);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
                
                try {
                    $reindex->reindexHighlyRated($product_id);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            } else {
                $indexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
    }
    
    public function prepareFormData($data)
    {
        
        if ($this->checkFieldAvailable('good_and_bad_detail', 'form')) {
            $data['good_detail'] = trim($data['good_detail']);
            $data['no_good_detail'] = trim($data['no_good_detail']);
        }
        if ($this->checkFieldAvailable('user_pros_and_cons', 'form')) {
            $types = array(
                'user_pros' => 'pros',
                'user_cons' => 'cons'
            );
            foreach ($types as $type => $value) {
                if (isset($data[$type])) {
                    $storeId = array(Mage::app()->getStore()->getId());
                    $userProsCons = explode(',', $data[$type]);
                    foreach ($userProsCons as $item) {
                        $item = trim(htmlspecialchars($item));
                        if ($item != '') {
                            if ($value === 'pros') {
                                $entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::PROS;
                            } else {
                                $entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::CONS;
                            }
                            $prosConsCollection = Mage::getModel('detailedreview/review_proscons')->getCollection()
                                ->setType($entityType)
                                ->addFieldToFilter('name',array('eq' => $item));
                            /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $prosConsItem */
                            $prosConsItem = $prosConsCollection->getFirstItem();
                            if ($prosConsItem->getId()){
                                if (!isset($data[$value]) || !is_array($data[$value])) {
                                    $data[$value] = array();
                                }
                                $data[$value][] = $prosConsItem->getId();
                            } else {
                                $prosConsItem->setEntityType($entityType)
                                    ->setStoreIds($storeId)
                                    ->setName($item)
                                    ->setStatus(MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_DISABLED)
                                    ->setWroteBy(MageWorkshop_DetailedReview_Model_Source_Common_Wroteby::CUSTOMER);
                                $prosConsItem->save();
                                if (!isset($data[$value]) || !is_array($data[$value])) {
                                    $data[$value] = array();
                                }
                                $data[$value][] = $prosConsItem->getEntityId();
                            }
                        }
                    }
                }
            }
        }
        
        if ($this->checkFieldAvailable('about_you', 'form')) {
            $data['location'] = trim($data['location']);
        }
        
        return $data;
    }
    
    /**
     * @param $date
     * @param $format
     * @return null|string
     */
    public function convertToGMT($date, $format = 'Y-m-d H:i:s')
    {
        if ($this->checkIsAValidDate($date)) {
            $date = new Zend_Date($date, Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
            $timestampWithOffset = $date->get() - Mage::getSingleton('core/date')->getGmtOffset();
        }
        
        return isset($timestampWithOffset) ? date($format, $timestampWithOffset) : null;
    }
    
    /**
     * @param $dateString
     * @return bool
     */
    public function checkIsAValidDate($dateString)
    {
        return (bool)strtotime($dateString);
    }
    
    /**
     * @return array
     */
    public function getCustomerProductIds()
    {
        $customerProductsIds = array();
        $customerData = $this->getCustomerInfo();
        if(strtolower($customerData->getType()) == strtolower(MageWorkshop_DetailedReview_Model_CustomerIdentifier::IDENTIFIER_TYPE_ID)) {
            $customerData->setCustomerId($customerData->getValue());
        } elseif (strtolower($customerData->getType()) == strtolower(MageWorkshop_DetailedReview_Model_CustomerIdentifier::IDENTIFIER_TYPE_EMAIL)) {
            $customerData->setCustomerEmail($customerData->getValue());
        }
        if ($customerData->getCustomerId() || $customerData->getCustomerEmail()) {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $select = $readConnection->select();
            $select
                ->from(
                    array('o' => $resource->getTableName('sales/order')),
                    array('si.product_id'))
                ->joinInner(
                    array('si' => $resource->getTableName('sales/order_item')),
                    'o.entity_id = si.order_id',
                    array())
                ->where('o.state = ?', Mage_Sales_Model_Order::STATE_COMPLETE)
                ->where("o.customer_id = {$customerData->getCustomerId()} OR o.customer_email = '{$customerData->getCustomerEmail()}'")
                ->group('si.product_id');
            
            $customerProductsIds = $readConnection->fetchCol($select);
        }
        
        return $customerProductsIds;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function isVerifiedBuyer($product)
    {
        $productIds[] = $product->getId();
        if ($product->isGrouped()) {
            $childrenIds = reset($product->getTypeInstance()->getChildrenIds($product->getId()));
            $productIds = array_merge($productIds, $childrenIds);
        }
        
        $purchasedProductIds = $this->getCustomerProductIds();
        
        return (boolean) array_intersect($purchasedProductIds, $productIds);
    }
    
    /**
     * @param $productId
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    public function getProduct($productId)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('entity_id', $productId);
        $collection->getSelect()->limit(1);
        /** @var Mage_Catalog_Model_Product $product */
        $product = $collection->getFirstItem();
        if (!$product->getId()) {
            throw new Exception($this->__('Invalid product ID'));
        }
        
        return $product;
    }

    public function getCustomLoginPostUrl()
    {
        $params = array();
        if ($this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)) {
            $params = array(
                self::REFERER_QUERY_PARAM_NAME => $this->_getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)
            );
        }

        return $this->_getUrl('customer/account/loginPost1', $params);
    }
}