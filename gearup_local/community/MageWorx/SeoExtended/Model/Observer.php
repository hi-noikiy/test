<?php

/**
 * MageWorx
 * MageWorx SeoExtended Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoExtended
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SeoExtended_Model_Observer
{
    static protected $_singleton = array();

    public function cropMetaKeywordsTag($observer)
    {
        if ($observer->getBlock() instanceof Mage_Page_Block_Html_Head) {

            if (!$this->_observerCanRun(__METHOD__)) {
                return false;
            }

            if (Mage::helper('seoextended/config')->getStatusCropMetaKeywordsTag() == 'no') {
                return false;
            }

            if (Mage::helper('seoextended/config')->getStatusCropMetaKeywordsTag() == 'yes') {
                $actionName  = Mage::helper('seoextended')->getCurrentFullActionName();
                $ignorePages = Mage::helper('seoextended/config')->getIgnorePagesForMetaKeywords();

                if (!empty($ignorePages) && in_array($actionName, $ignorePages)) {
                    return false;
                }
            }

            $html = $observer->getTransport()->getHtml();
            if ($html) {
                $q       = '"';
                $matches = array();
                $ret     = preg_match_all("/<\s*meta\s{1,}name\s{0,}=\s{0,}['|$q]\s{0,1}keywords\s{0,}['|$q]\s{1,}content\s{0,}=\s{0,}['|$q](.{0,}?)['|$q]\s{0,}\/\s{0,}>\s{0,}\n{0,}/i",
                    $html, $matches, PREG_SET_ORDER);

                if ($ret && !empty($matches[0][0])) {
                    $modifyHtml = str_replace($matches[0][0], '', $html);
                    if (Mage::helper('seoextended/config')->getStatusCropMetaKeywordsTag() == 'yes') {
                        $html = $modifyHtml;
                    }
                    elseif (Mage::helper('seoextended/config')->getStatusCropMetaKeywordsTag() == 'for_empty') {

                        if (isset($matches[0][1]) && $matches[0][1] == "") {
                            $html = $modifyHtml;
                        }
                    }
                }
                $observer->getTransport()->setHtml($html);
            }
        }
    }

    /**
     * Modify meta data.
     * Event: core_block_abstract_to_html_before
     *
     * @param type $observer
     */
    public function modifyMeta($observer)
    {
        $block = $observer->getBlock();

        if ($block->getNameInLayout() != 'head') {
            return false;
        }

        if (!$this->_observerCanRun(__METHOD__)) {
			return false;
		}

        if ('catalog_product_view' == Mage::helper('seoextended')->getCurrentFullActionName()) {
            $categoryProductFlag = true;
            $title               = $this->_cutPrefixSuffix($block->getTitle());
            $metaDescription     = $block->getDescription();
            $metaKeywords        = $block->getKeywords();
        }
        elseif ('review_product_list' == Mage::helper('seoextended')->getCurrentFullActionName()) {
            $categoryProductFlag = true;
            $title               = $this->_updateTitleForProductReviewPage($this->_cutPrefixSuffix($block->getTitle()));
            $metaDescription     = $this->_updateMetaDescriptionForProductReviewPage($block->getDescription());
        }
        elseif ('catalog_category_view' == Mage::helper('seoextended')->getCurrentFullActionName()) {
            $categoryProductFlag = true;
            $title               = $this->_updateTitleForCategoryPage($this->_cutPrefixSuffix($block->getTitle()));
            $metaDescription     = $this->_updateMetaDescriptionForCategoryPage($block->getDescription());
            $metaKeywords        = $this->_updateMetaKeywordsForCategoryPage($block->getKeywords());
        }
        elseif ('cms' == Mage::app()->getRequest()->getModuleName()) {
            $title           = $this->_getCmsMetaTitle($this->_cutPrefixSuffix($block->getTitle()));
            $metaDescription = $this->_updateMetaDescriptionForCmsPage($block->getDescription());
            $metaKeywords    = $this->_updateMetaKeywordsForCmsPage($block->getKeywords());
        }
        elseif ('rss' == Mage::app()->getRequest()->getModuleName()) {
            $title           = $this->_getTitleForRssPage();
            $metaDescription = $this->_getMetaDescriptionForRssPage();
        }

        if (!empty($title)) {
            $title = trim(htmlspecialchars(html_entity_decode($title, ENT_QUOTES, 'UTF-8')));
            if ($title) {
                if (!empty($categoryProductFlag) && Mage::helper('seoextended/config')->isCutPrefixSuffixFromProductAndCategoryPages()) {
                    $block->setData('title', $title);
                }
                else {
                    $block->setTitle($title);
                }
            }
        }

        if (!empty($metaDescription)) {
            $stripTags       = new Zend_Filter_StripTags();
            $metaDescription = htmlspecialchars(html_entity_decode(preg_replace(array('/\r?\n/', '/[ ]{2,}/'),
                        array(' ', ' '), $stripTags->filter($metaDescription)), ENT_QUOTES, 'UTF-8'));
            if ($metaDescription) {
                $block->setDescription($metaDescription);
            }
        }

        if (!empty($metaKeywords)) {
            $metaKeywords = trim(htmlspecialchars(html_entity_decode($metaKeywords, ENT_QUOTES, 'UTF-8')));
            if ($metaKeywords) {
                $block->setKeywords($metaKeywords);
            }
        }
    }

    protected function _updateTitleForProductReviewPage($title)
    {
        $title = Mage::helper('seoextended')->__('Reviews for') . ' ' . $title;
        return $title;
    }

    protected function _updateTitleForCategoryPage($title)
    {
        $title    = trim($title);
        $category = Mage::registry('current_category');

        $filtersPartForMeta = $this->_getLayerFiltersMetaPart('title');

        if ($filtersPartForMeta) {
            $title = rtrim($title) . ', ' . $filtersPartForMeta;
        }

        $this->_addPageNumToMeta('title', $title);

        return $title;
    }

    protected function _getCmsMetaTitle($title)
    {
        if (Mage::getSingleton('cms/page')->getMetaTitle()) {
            $title = Mage::getSingleton('cms/page')->getMetaTitle();
        }
        elseif (Mage::getSingleton('cms/page')->getData('published_revision_id')) {
            $collection = Mage::getResourceModel('enterprise_cms/page_revision_collection');
            $collection->getSelect()->where('revision_id=?',
                Mage::getSingleton('cms/page')->getData('published_revision_id'));
            $pageData   = $collection->getFirstItem();

            if (!$pageData->getMetaTitle()) {
                $title = $pageData->getMetaTitle();
            }
        }
        return $title;
    }

    protected function _getTitleForRssPage()
    {
        return Mage::helper('seoextended')->__('RSS Feed') . ' | ' . Mage::app()->getWebsite()->getName();
    }

    protected function _updateMetaDescriptionForProductReviewPage($description)
    {
        $description = Mage::helper('seoextended')->__('Reviews for') . ' ' . $description;
        return $description;
    }

    protected function _updateMetaDescriptionForCategoryPage($description)
    {
        $description = trim($description);
        $category    = Mage::registry('current_category');

        $filtersPartForMeta = $this->_getLayerFiltersMetaPart('description');

        if ($filtersPartForMeta) {
            $description = rtrim($description) . ', ' . $filtersPartForMeta;
        }

        $this->_addPageNumToMeta('description', $description);

        return $description;
    }

    protected function _getMetaDescriptionForRssPage()
    {
        return Mage::helper('seoextended')->__('RSS Feed') . ' | ' . Mage::app()->getWebsite()->getName();
    }

    protected function _updateMetaDescriptionForCmsPage($description)
    {
        if ($metaDescription = Mage::getSingleton('cms/page')->getMetaDescription()) {
            return $metaDescription;
        }
        return $description;
    }

    protected function _getLayerFiltersMetaPart($metaType)
    {
        $metaPart = '';
        if (
            ($metaType == 'title' && Mage::helper('seoextended/config')->isExtendedMetaTitleForLNEnabled()) || ($metaType ==
            'description' && Mage::helper('seoextended/config')->isExtendedMetaDescriptionForLNEnabled())
        ) {
            $currentFiltersData = Mage::helper('seoextended')->getLayeredNavigationFiltersData();
            if (is_array($currentFiltersData) && count($currentFiltersData) > 0) {
                foreach ($currentFiltersData as $filter) {
                    $metaPart .= $filter['name'] . " " . strip_tags($filter['label'] . ', ');
                }
            }
        }
        return trim(trim($metaPart), ',');
    }

    protected function _addPageNumToMeta($metaType, &$metaValue)
    {
        $metaValue      = trim($metaValue);
        $statusPagerNum = Mage::helper('seoextended/config')->getStatusPagerNumForMeta($metaType);
        if ($statusPagerNum == 'end') {
            $pageNum = $this->_getPageNumFromUrl();
            if ($pageNum) {
                $metaValue .= ' | ' . Mage::helper('seoextended')->__('Page') . " " . $pageNum;
            }
        }
        elseif ($statusPagerNum == 'begin') {
            $pageNum = $this->_getPageNumFromUrl();
            if ($pageNum) {
                $metaValue = Mage::helper('seoextended')->__('Page') . " " . $pageNum . ' | ' . $metaValue;
            }
        }

        return $metaValue;
    }

    /**
     * Parse url and retrive page number.
     * At first find by param '[?&]p=', then by part of url (E.g. ex.com/apparel-page2.html).
     * @return type
     */
    public function _getPageNumFromUrl()
    {
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        if (!empty($params['p'])) {
            if (settype($params['p'], 'int') == $params['p']) {
                $num = $params['p'];
            }
        }

        if (empty($num)) {
            $pageFormat = Mage::helper('seoextended/config')->getPagerUrlFormat();
            if ($pageFormat != '-[page_number]') {
                $pattern = '(' . str_replace('[page_number]', '[0-9]+', $pageFormat) . ')';
                if (preg_match($pattern,
                        Mage::app()->getFrontController()->getAction()->getRequest()->getRequestString(), $matches)) {
                    $match = array_pop($matches);
                    if ($match) {
                        $lengthBeforeNum = strpos($pageFormat, '[page_number]');
                        $lengthAfterNum  = strlen($pageFormat) - (strpos($pageFormat, '[page_number]') + 13);
                        $num             = substr($match, $lengthBeforeNum);
                        $num             = substr($num, 0, strlen($num) - $lengthAfterNum);
                    }
                }
            }
        }
        return !empty($num) ? $num : false;
    }

    protected function _cutPrefixSuffix($title)
    {
        $prefix = Mage::helper('seoextended/config')->getMagentoTitlePrefix();
        $suffix = Mage::helper('seoextended/config')->getMagentoTitleSuffix();
        if ($prefix && strpos($title, $prefix) !== false) {
            $title = trim(str_replace($prefix, '', $title));
        }
        if ($suffix && strpos($title, $suffix) !== false) {
            $title = trim(str_replace($suffix, '', $title));
        }
        return $title;
    }

    public function _updateMetaKeywordsForCategoryPage($keywords)
    {
        $keywords = trim($keywords);
        $category = Mage::registry('current_category');

        $filtersPartForMeta = $this->_getLayerFiltersMetaPart('keywords');

        if ($filtersPartForMeta) {
            $keywords = rtrim($keywords) . ', ' . $filtersPartForMeta;
        }

        $this->_addPageNumToMeta('keywords', $keywords);

        return $keywords;
    }

    public function _updateMetaKeywordsForCmsPage($keywords)
    {
        if ($metaKeywords = Mage::getSingleton('cms/page')->getKeywords()) {
            return $metaKeywords;
        }
        return $keywords;
    }

    protected function _observerCanRun($method)
    {
        if (!isset(self::$_singleton[$method])) {
            self::$_singleton[$method] = true;

            return true;
        }

        return false;
    }

}
