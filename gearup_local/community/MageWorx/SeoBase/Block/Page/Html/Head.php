<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoBase_Block_Page_Html_Head extends MageWorx_SeoBase_Block_Page_Html_Head_Abstract
{
    /**
     * Crop params from baseUrl (as SID) in case of compilation canonical url.
     */
    const CROP_URL_PARAMS = true;

    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('seosuite');
        return parent::__construct();
    }

    public function getContentType()
    {
        $this->_data['content_type'] = $this->getMediaType() . '; charset=' . $this->getCharset();
        return $this->_data['content_type'];
    }

    public function getCssJsHtml()
    {
        $canonicalUrl = $this->getCanonicalUrl();
        $altLinks     = $this->getAlternateLinks();

        if (method_exists($this, 'addLinkRel')) {
            if ($canonicalUrl) {
                $this->addLinkRel('canonical', $canonicalUrl);
            }
        }
        else {
            $html = parent::getCssJsHtml();
            if ($canonicalUrl) {
                $html = '<link rel="canonical" href="' . $canonicalUrl . '" />' . "\n" . $html;
            }
        }

        $html = !empty($html) ? $html : parent::getCssJsHtml();

        if ($altLinks) {
            $html .= "\n" . $altLinks;
        }

        return $html;
    }

    public function getAlternateLinks()
    {
        $action             = Mage::helper('seosuite')->getCurrentFullActionName();
        $itemId             = '';
        $altLinksCollection = array();
        $altHtmlLinks       = array();


        switch ($action) {
            case 'catalog_category_view':
                /**
                 * @todo Only for "clear" category URL
                 */
                if(strpos(Mage::helper('core/url')->getCurrentUrl(), '?') === false && !Mage::helper('seosuite')->applyedLayeredNavigationFilters())

                    $itemId = Mage::getSingleton('catalog/layer')->getCurrentCategory()->getId();
                    if ($itemId) {
                        $alternateCodes = Mage::helper('seosuite/alternate')->getAlternateFinalCodes('category');
                        if (empty($alternateCodes)) {
                            return false;
                        }
                        $altLinksCollection = $this->_getCategoryAlternateLinks(array_keys($alternateCodes), $itemId);
                    }
                break;
            case 'catalog_product_view':
                $product = Mage::registry('current_product');

                if (!empty($product)) {
                    $itemId     = $product->getId();
                    $categoryId = $product->getCategoryId();
                    if ($itemId) {
                        $alternateCodes = Mage::helper('seosuite/alternate')->getAlternateFinalCodes('product');
                        if (empty($alternateCodes)) {
                            return false;
                        }
                        $altLinksCollection = $this->_getProductAlternateLinks(array_keys($alternateCodes), $itemId,
                            $categoryId);
                    }
                }

                break;
            case 'cms_index_index':
                $itemId             = Mage::getBlockSingleton('cms/page')->getPage()->getPageId();
                $alternateCodes     = Mage::helper('seosuite/alternate')->getAlternateFinalCodes('cms');

                if (empty($alternateCodes)) {
                    return false;
                }
                if ($itemId) {
                    $altLinksCollection = $this->_getCmsAlternateLinks(array_keys($alternateCodes), $itemId, true);
                }

                break;
            case 'cms_page_view':
                $itemId             = Mage::getBlockSingleton('cms/page')->getPage()->getPageId();
                $alternateCodes     = Mage::helper('seosuite/alternate')->getAlternateFinalCodes('cms');

                if (empty($alternateCodes)) {
                    return false;
                }
                if ($itemId) {
                    $altLinksCollection = $this->_getCmsAlternateLinks(array_keys($alternateCodes), $itemId);
                }
                break;
        }

        if (!empty($altLinksCollection)) {
            if ($action == 'cms_index_index') {
                $altHtmlLinks = Mage::helper('seosuite/alternate')->getAlternateCmsHtml($altLinksCollection,
                    $alternateCodes);
            }
            else {
                if(!empty($altLinksCollection[$itemId]['alternateUrls'])){
                    $altLinksCollection = $altLinksCollection[$itemId]['alternateUrls'];
                    $altHtmlLinks       = Mage::helper('seosuite/alternate')->getAlternateHtml($altLinksCollection,
                    $alternateCodes);
                }
            }

            return $altHtmlLinks;
        }

        return '';
    }

    protected function _getCategoryAlternateLinks($storeIds, $categoryId)
    {
        return $alternateUrlsCollection = Mage::getResourceModel('seosuite/alternate_catalog_category')->getAllCategoryUrls($storeIds,
            $categoryId);
    }

    protected function _getProductAlternateLinks($storeIds, $productId, $categoryId)
    {
        return $alternateUrlsCollection = Mage::getResourceModel('seosuite/alternate_catalog_product')->getAllProductUrls($storeIds,
            false, $productId, $categoryId);
    }

    protected function _getCmsAlternateLinks($storeIds, $itemId, $isCmsHomePage = false)
    {
        return $alternateUrlsCollection = Mage::getResourceModel('seosuite/alternate_cms_page')->getAllCmsUrls($storeIds,
            Mage::app()->getStore()->getStoreId(), $itemId, $isCmsHomePage);
    }

    public function getCanonicalUrl()
    {
        if (Mage::registry('amshopby_current_params')) {
            return;
        }

        if (!Mage::getStoreConfig('mageworx_seo/seosuite/enabled')) {
            return;
        }

        if (Mage::app()->getRequest()->getRequestedActionName() == 'noRoute') {
            return;
        }

        /** Ignore pages section * */
        $ignorePages = array_filter(preg_split('/\r?\n/', Mage::getStoreConfig('mageworx_seo/seosuite/ignore_pages')));
        $ignorePages = array_map('trim', $ignorePages);

        if (in_array($this->_helper->getCurrentFullActionName(), $ignorePages)) {
            return;
        }

        if ($this->_helper->isProductPage($this->_helper->getCurrentFullActionName())) {
            $canonicalUrl = $this->getCanonicalProductUrl();
        }
        elseif ($this->_helper->isCategoryPage($this->_helper->getCurrentFullActionName())) {
            $canonicalUrl = $this->getCanonicalCategoryUrl();
        }
        elseif($this->_helper->isHomePage($this->_helper->getCurrentFullActionName())) {
            $canonicalUrl = $this->getCanonicalHomePageUrl();
        }
        elseif ($this->_helper->getCurrentFullActionName() == 'tag_product_list') {
            $canonicalUrl = $this->getCanonicalTagUrl();
        }
        else {
            $canonicalUrl = $this->_helper->trailingSlash(Mage::helper('core/url')->getCurrentUrl());
        }

        // apply crossDomainUrl
        $crossDomainStore = false;
        $product          = Mage::registry('current_product');
        if (is_object($product) && $product->getCanonicalCrossDomain()) {
            $crossDomainStore = $product->getCanonicalCrossDomain();
        }
        elseif (Mage::getStoreConfig('mageworx_seo/seosuite/cross_domain')) {
            $crossDomainStore = Mage::getStoreConfig('mageworx_seo/seosuite/cross_domain');
        }

        if ($crossDomainStore) {
            $crossDomainUrlParts = explode('?', Mage::app()->getStore($crossDomainStore)->getBaseUrl());
            $crossomainUrl = trim($crossDomainUrlParts[0], '/');

            $urlParts = explode('?', Mage::getUrl());
            $url = trim($urlParts[0], '/');

            $canonicalUrl = str_replace($url, $crossomainUrl, $canonicalUrl);
        }

        $canonicalUrl = filter_var(filter_var($canonicalUrl, FILTER_SANITIZE_STRING), FILTER_SANITIZE_URL);

        return !empty($canonicalUrl) ? $canonicalUrl : false;
    }

    public function getCanonicalReviewUrl()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url        = $currentUrl;

        $toolbar = $this->getLayout()->getBlock('product_review_list.toolbar');
        if (is_object($toolbar) && ($toolbar instanceof Mage_Catalog_Block_Product_List_Toolbar)) {
            $availableLimit = $toolbar->getAvailableLimit();
        }
        else {
            $availableLimit = false;
        }

        if (is_array($availableLimit) && !empty($availableLimit['all'])) {
            $url = $this->_helper->trailingSlash($category->getUrl());
            $url = $this->addLimitAllToUrl($url, $toolbar);
        }
        else {
            $url = $this->deleteSortParametersFromUrl($url, $toolbar);
        }

        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $canonicalUrl = $url;
        }
        else {
            $canonicalUrl = $currentUrl;
        }

        return $canonicalUrl;
    }

    public function getCanonicalProductUrl()
    {
        $useCategories = Mage::getStoreConfigFlag('catalog/seo/product_use_categories');
        $product       = Mage::registry('current_product');

        ///Check product canonical on review page
        if ($this->_helper->getCurrentFullActionName() == 'review_product_list') {
            if (!$this->_helper->isProductCanonicalUrlOnReviewPage()) {
                return $this->getCanonicalReviewUrl();
            }
        }

        if ($product) {

            $canonicalUrl = $product->getCanonicalUrl();

            if ($canonicalUrl) {
                $secure = '';
                if (Mage::app()->getStore()->isFrontUrlSecure()) {
                    $secure = 's';
                }

                $urlRewrite = Mage::getModel('core/url_rewrite')->setStoreId(Mage::app()->getStore()->getId())->loadByIdPath($canonicalUrl);
                if (strpos($urlRewrite->getRequestPath(), "http" . $secure) !== true) {
                    $canonicalUrl = $urlRewrite->getRequestPath();
                }
                elseif (strpos($urlRewrite->getRequestPath(), "http") !== false) {
                    $canonicalUrl = $urlRewrite->getRequestPath();
                }
                else {
                    $canonicalUrl = $urlRewrite->getRequestPath();
                }
            }
            else {
                if (Mage::helper('seosuite')->isAssociatedCanonicalEnabled(Mage::app()->getStore()->getStoreId())) {
                    if ($this->_helper->isCompoundProductType($product->getTypeID()) === false) {

                        $compoundProduct = $this->_helper->getLastCompoundProductByChildProductId($product->getId());

                        if (is_object($compoundProduct)) {
                            $product = $compoundProduct;
                        }
                    }
                }

                $canonicalUrl = $this->_helper->getUrlRewriteCanonical($product);
                if (!$canonicalUrl) {
                    $canonicalUrl = $product->getProductUrl(false);
                    if (!$canonicalUrl) {  //|| $productCanonicalUrl == 0) {
                        $product->setDoNotUseCategoryId(!$useCategories);
                        $canonicalUrl = $product->getProductUrl(false);
                    }
                }
            }
        }

        if (strpos($canonicalUrl, 'http') === false) {
            if (self::CROP_URL_PARAMS) {
                list($urlWithoutParams) = explode('?', Mage::getUrl(''));
                $canonicalUrl = $this->_helper->trailingSlash($urlWithoutParams . $canonicalUrl);
            }
            else {
                $canonicalUrl = $this->_helper->trailingSlash(Mage::getUrl('') . $canonicalUrl);
            }
        }
        else {
            $canonicalUrl = $this->_helper->trailingSlash($canonicalUrl);
        }

        return $canonicalUrl;
    }

    public function getCanonicalCategoryUrl()
    {
        $category = Mage::registry('current_category');

        if (!is_object($category)) {
            return '';
        }

        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url        = $currentUrl;

        $toolbar = $this->getLayout()->getBlock('product_list_toolbar');

        $availableLimit = $this->_getAvailableLimit($toolbar);

        ///LN URLS
        if ($this->_helper->applyedLayeredNavigationFilters()) {
            ///Disable canonical on layered navigation pages
            if ($this->_helper->isIncludeLNFiltersToCanonicalUrlByConfig() == MageWorx_SeoBase_Helper_Data::CATEGORY_LN_CANONICAL_OFF) {
                return '';
            }

            ///FRIENDLY LN URLS
            if ($this->_helper->isLNFriendlyUrlsEnabled()) {
                ///FRIENDLY LN URLS WITH PAGE ALL
                if (is_array($availableLimit) && !empty($availableLimit['all'])) {
                    if ($this->_helper->isIncludeLNFiltersToCanonicalUrl()) {
                        $url = $this->deleteSortParametersFromUrl($url, $toolbar);
                        $url = $this->deleteLimitParameterFromUrl($url, $toolbar);
                        $url = $this->deletePagerParameterFromUrl($url, $toolbar);
                        $url = $this->addLimitAllToUrl($url, $toolbar);
                    }
                    else {
                        $url = $this->_helper->trailingSlash($category->getUrl());
                        $url = $this->addLimitAllToUrl($url, $toolbar);
                    }
                }
                ///FRIENDLY LN URLS WITHOUT PAGE ALL
                else {
                    if ($this->_helper->isIncludeLNFiltersToCanonicalUrl()) {
                        $url = $this->changePagerParameterToCurrentForCurrentUrl();
                        $url = $this->cropDefaultLimit($url, $toolbar);
                        $url = $this->deleteSortParametersFromUrl($url, $toolbar);
                    }
                    else {
                        //Maybe better without canonical url...?
                        $url = $this->_helper->trailingSlash($category->getUrl());
                    }
                }
            }
            ///DEFAULT LN URLS
            else {
                $subCategory = $this->getSubCategoryForCanonical($url);

                if (is_object($subCategory)) {
                    $url = $this->_convertSubCategoryUrl($url, $subCategory);
                    if ($subCategory->getDisplayMode() == 'PAGE') {
                        return $this->_helper->trailingSlash($url);
                    }
                }

                ///DEFAULT LN URLS WITH PAGE ALL
                if (is_array($availableLimit) && !empty($availableLimit['all'])) {
                    if ($this->_helper->isIncludeLNFiltersToCanonicalUrl()) {
                        $url = $this->deleteSortParametersFromUrl($url, $toolbar);
                        $url = $this->deleteLimitParameterFromUrl($url, $toolbar);
                        $url = $this->deletePagerParameterFromUrl($url, $toolbar);
                        $url = $this->addLimitAllToUrl($url, $toolbar);
                    }
                    else {
                        $url = $this->_helper->trailingSlash($category->getUrl());
                        $url = $this->addLimitAllToUrl($url, $toolbar);
                    }
                }
                ///DEFAULT LN URLS WITHOUT PAGE ALL
                else {
                    if ($this->_helper->isIncludeLNFiltersToCanonicalUrl()) {
                        $url = $this->deleteSortParametersFromUrl($url, $toolbar);
                    }
                    else {
                        //Maybe without canonical url better...?
                        $url = $this->_helper->trailingSlash($category->getUrl());
                    }
                }
            }
        }
        ///CATEGORY URLS WITHOUT LN
        else {
            ///Magento bug? For category with display mode = PAGE,
            /// If clear LN filters the pager will remain in the category URL
            if ($category->getDisplayMode() == 'PAGE') {
                return $this->_helper->trailingSlash($category->getUrl());
            }

            ///CATEGORY URLS WITH PAGE ALL
            if (is_array($availableLimit) && !empty($availableLimit['all'])) {
                $url = $this->_helper->trailingSlash($category->getUrl());
                $url = $this->addLimitAllToUrl($url, $toolbar);
            }
            ///CATEGORY URLS WITHOUT PAGE ALL
            else {
                $url = $this->changePagerParameterToCurrentForCurrentUrl();
                $url = $this->cropDefaultLimit($url, $toolbar);
                $url = $this->deleteSortParametersFromUrl($url, $toolbar);
            }
        }

        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $canonicalUrl = $this->_helper->trailingSlash($url);
        }
        else {
            $canonicalUrl = $this->_helper->trailingSlash($currentUrl);
        }

        return $canonicalUrl;
    }

    public function getCanonicalHomePageUrl()
    {
        if(Mage::helper('seosuite')->cropTrailingSlashForHomePageUrl()){
            return trim(Mage::getBaseUrl(), '/');
        }
        return Mage::getBaseUrl();
    }

    public function cropDefaultLimit($url, $toolbar)
    {
        if (is_callable(array($toolbar, 'getDefaultPerPageValue')) && is_callable(array($toolbar, 'getLimit'))) {
            if ($toolbar->getDefaultPerPageValue() == $toolbar->getLimit()) {
                $url = $this->deleteLimitParameterFromUrl($url, $toolbar);
            }
        }
        return $url;
    }

    public function getCanonicalTagUrl()
    {
        $toolbar = $this->getLayout()->getBlock('product_list_toolbar');

        if (is_object($toolbar) && ($toolbar instanceof Mage_Catalog_Block_Product_List_Toolbar)) {
            $availableLimit = $toolbar->getAvailableLimit();
        }
        else {
            $availableLimit = false;
        }

        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url        = $currentUrl;

        if (is_array($availableLimit) && !empty($availableLimit['all'])) {
            $url = $this->deleteSortParametersFromUrl($url, $toolbar);
            $url = $this->deleteLimitParameterFromUrl($url, $toolbar);
            $url = $this->deletePagerParameterFromUrl($url, $toolbar);
            $url = $this->addLimitAllToUrl($url, $toolbar);
        }
        else {
            $url = $this->deleteSortParametersFromUrl($url, $toolbar);
        }

        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $canonicalUrl = $url;
        }
        else {
            $canonicalUrl = $currentUrl;
        }
        return $canonicalUrl;
    }

    public function getSubCategoryForCanonical($url)
    {
        $parseUrl = parse_url($url);

        if (empty($parseUrl['query'])) {
            return $url;
        }

        parse_str(html_entity_decode($parseUrl['query']), $params);
        if (!empty($params['cat']) && is_numeric($params['cat'])) {
            $catId       = $params['cat'];
            $subCategory = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($catId);
        }
        return (!empty($subCategory)) ? $subCategory : false;
    }

    protected function _convertSubCategoryUrl($url, $category)
    {

        $parseUrl    = parse_url($url);
        $categoryUrl = $category->getUrl();

        if (!empty($categoryUrl)) {
            $url = $categoryUrl . '?' . $parseUrl['query'];
            $url = $this->deleteParametrsFromUrl($url, array('cat'));
        }

        return $url;
    }

    public function deleteSortParametersFromUrl($url, $toolbar)
    {
    	if(is_object($toolbar)){
    		$orderVarName     = $toolbar->getOrderVarName();
			$directionVarName = $toolbar->getDirectionVarName();
			$modeVarName      = $toolbar->getModeVarName();
    	}

        $orderVarName     = (!empty($orderVarName)) ? $orderVarName : 'order';
        $directionVarName = (!empty($directionVarName)) ? $directionVarName : 'dir';
        $modeVarName      = (!empty($modeVarName)) ? $modeVarName : 'mode';

        return $this->deleteParametrsFromUrl($url, array($orderVarName, $directionVarName, $modeVarName));
    }

    /**
     * Retrive current URL with a specified pager: with parameter 'p =' or as URL part: '* [page_number] * '.html? =...
     * Example 1:
     *      Old url from google search: example.com/computers?p=2
     *      Retrive url: example.com/computers-page2.html (If friendly pager ON, etc.)
     * Example 2 (with layered, sort and mode params):
     *      Old url from google search: example.com/electronics/lnav/price:0-1000.html?p=3&limit=15&mode=list
     *      Retrive url:                example.com/electronics/lnav/price:0-1000-page3.html?limit=15&mode=list
     * @return string
     */
    public function changePagerParameterToCurrentForCurrentUrl()
    {

        $pageNum = $this->getPageNumFromUrl();
        $pager   = $this->getLayout()->getBlock('product_list_toolbar_pager');

        //If friendly url disable
        //Canonical for ex.com/computers.html?p=1 is ex.com/computers.html?p=1,
        //Canonical for ex.com/computers.html     is ex.com/computers.html
        //If friendly url enable and use custom pager
        //Canonical for ex.com/computers.html     is ex.com/computers.html
        //Canonical for ex.com/computers.html?p=1 is ex.com/computers.html
        //Because for custom pager url we don't use '1'

        if (is_object($pager)) {
            if (!$pageNum) {
                return Mage::helper('core/url')->getCurrentUrl();
            }
            elseif ($pageNum == 1 && $this->_helper->isLNFriendlyUrlsEnabled() && $this->_helper->getPagerUrlFormat()) {
                return $this->deletePagerParameterFromUrl(Mage::helper('core/url')->getCurrentUrl(),
                        $this->getLayout()->getBlock('product_list_toolbar'));
            }
            else {
                return $pager->getPageUrl($pageNum);
            }
        }

        return Mage::helper('core/url')->getCurrentUrl();
    }

    public function deleteLimitParameterFromUrl($url, $toolbar)
    {
        $limitVarName = $toolbar->getLimitVarName();
        $limitVarName = $limitVarName ? $limitVarName : 'limit';

        return $this->deleteParametrsFromUrl($url, array($limitVarName));
    }

    public function deletePagerParameterFromUrl($url, $toolbar)
    {
        //delete friendly pager
        $pagerFormat = $this->_helper->getPagerUrlFormat();
        if ($pagerFormat) {
            $pattern         = '#' . str_replace('[page_number]', '[0-9]+', $pagerFormat) . '#';
            $urlWithoutPager = preg_replace($pattern, '', $url);
            $url             = (is_null($urlWithoutPager)) ? $url : $urlWithoutPager;
        }
        //also delete standart pager
        $pageVarName = $toolbar->getPageVarName();
        $url         = $this->deleteParametrsFromUrl($url, array($pageVarName));

        return $url;
    }

    public function deleteParametrsFromUrl($url, array $cropParams)
    {
        $parseUrl = parse_url($url);

        if (empty($parseUrl['query'])) {
            return $url;
        }

        parse_str(html_entity_decode($parseUrl['query']), $params);

        foreach ($cropParams as $cropName) {
            if (array_key_exists($cropName, $params)) {
                unset($params[$cropName]);
            }
        }

        $queryString = '';
        foreach ($params as $name => $value) {
            if ($queryString == '') {
                $queryString = '?' . $name . '=' . $value;
            }
            else {
                $queryString .= '&' . $name . '=' . $value;
            }
        }

        $url = $parseUrl['scheme'] . '://' . $parseUrl['host'] . $parseUrl['path'] . $queryString;
        return $url;
    }

    public function addLimitAllToUrl($url, $toolbar)
    {
        $limitVarName = $toolbar->getLimitVarName();
        $limitVarName = $limitVarName ? $limitVarName : 'limit';

        if (strpos($url, '?') !== false) {
            $url = $url . '&' . $limitVarName . '=all';
        }
        else {
            $url = $url . '?' . $limitVarName . '=all';
        }
        return $url;
    }

    public function getRobots()
    {
        // standart magento
        //$this->_data['robots'] = Mage::getStoreConfig('design/head/default_robots');
        //https_robots
        if (substr(Mage::helper('core/url')->getCurrentUrl(), 0, 8) == 'https://')
                $this->_data['robots'] = Mage::getStoreConfig('mageworx_seo/seosuite/https_robots');

        $noindexPatterns = explode(',', Mage::getStoreConfig('mageworx_seo/seosuite/noindex_pages'));
        $noindexPatterns = array_map('trim', $noindexPatterns);
        foreach ($noindexPatterns as $pattern) {
            //  $pattern = str_replace(array('\\','^','$','.','[',']','|','(',')','?','*','+','{','}'),array('\\\\','\^','\$','\.','\[','\]','\|','\(','\)','\?','\*','\+','\{','\}'),$pattern);
            if (preg_match('/' . $pattern . '/', $this->_helper->getCurrentFullActionName())) {
                $this->_data['robots'] = 'NOINDEX, FOLLOW';
                break;
            }
        }

        $noindexPatterns = array_filter(preg_split('/\r?\n/',
                Mage::getStoreConfig('mageworx_seo/seosuite/noindex_pages_user')));
        $noindexPatterns = array_map('trim', $noindexPatterns);
        $noindexPatterns = array_filter($noindexPatterns);
        foreach ($noindexPatterns as $pattern) {
            $pattern = str_replace('?', '\?', $pattern);
            $pattern = str_replace('*', '.*?', $pattern);
            //  $pattern = str_replace(array('\\','^','$','.','[',']','|','(',')','?','*','+','{','}'),array('\\\\','\^','\$','\.','\[','\]','\|','\(','\)','\?','\*','\+','\{','\}'),$pattern);

            if (preg_match('#' . $pattern . '#', $this->_helper->getCurrentFullActionName()) || preg_match('#' . $pattern . '#',
                    $this->getAction()->getRequest()->getRequestString()) || preg_match('#' . $pattern . '#',
                    $this->getAction()->getRequest()->getRequestUri()) || preg_match('#' . $pattern . '#',
                    Mage::helper('core/url')->getCurrentUrl())
            ) {
                $this->_data['robots'] = 'NOINDEX, FOLLOW';
                break;
            }
        }

        $noindexNofollowPatterns = array_filter(preg_split('/\r?\n/',
                Mage::getStoreConfig('mageworx_seo/seosuite/noindex_nofollow_pages_user')));
        $noindexNofollowPatterns = array_map('trim', $noindexNofollowPatterns);
        $noindexNofollowPatterns = array_filter($noindexNofollowPatterns);
        foreach ($noindexNofollowPatterns as $pattern) {
            $pattern = str_replace('?', '\?', $pattern);
            $pattern = str_replace('*', '.*?', $pattern);
            //  $pattern = str_replace(array('\\','^','$','.','[',']','|','(',')','?','*','+','{','}'),array('\\\\','\^','\$','\.','\[','\]','\|','\(','\)','\?','\*','\+','\{','\}'),$pattern);

            if (preg_match('%' . $pattern . '%', $this->_helper->getCurrentFullActionName()) || preg_match('%' . $pattern . '%',
                    $this->getAction()->getRequest()->getRequestString()) || preg_match('%' . $pattern . '%',
                    $this->getAction()->getRequest()->getRequestUri()) || preg_match('%' . $pattern . '%',
                    Mage::helper('core/url')->getCurrentUrl())
            ) {
                $this->_data['robots'] = 'NOINDEX, NOFOLLOW';
                break;
            }
        }

        if (empty($this->_data['robots'])) {
            $this->_data['robots'] = Mage::getStoreConfig('design/head/default_robots');
        }

        $this->_setIndividualRobots();

        if($this->_helper->isCategoryPage($this->_helper->getCurrentFullActionName())){

            $maxCountFiltersForNoindex = $this->_helper->getCountFiltersForNoindex();

            if($maxCountFiltersForNoindex !== false){
                $appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
                $countAppliedFilters = (is_array($appliedFilters)) ? count($appliedFilters) : false;

                if($countAppliedFilters && $countAppliedFilters >= $maxCountFiltersForNoindex){
                    $this->_data['robots'] = 'NOINDEX, FOLLOW';
                }
            }
        }

        return $this->_data['robots'];
    }

    protected function _setIndividualRobots()
    {
        if ($this->_helper->getCurrentFullActionName() == 'catalog_category_view' && is_object(Mage::registry('current_category'))) {
            $category = Mage::registry('current_category');

            if ($robots = $category->getMetaRobots()) {
                $this->_data['robots'] = $robots;
            }
        }
        elseif ($this->_helper->getCurrentFullActionName() == 'catalog_product_view' && is_object(Mage::registry('current_product'))) {
            $product = Mage::registry('current_product');
            if ($robots  = $product->getMetaRobots()) {
                $this->_data['robots'] = $robots;
            }
        }
    }

    protected function _getAvailableLimit($toolbar)
    {
        if (is_object($toolbar) && ($toolbar instanceof Mage_Catalog_Block_Product_List_Toolbar)) {
            $availableLimit = $toolbar->getAvailableLimit();

            if(!Mage::helper('seosuite')->isUseLimitAll() && !empty($availableLimit['all'])){
                unset($availableLimit['all']);
            }
        }
        else {
            $availableLimit = false;
        }

        return $availableLimit;
    }

}