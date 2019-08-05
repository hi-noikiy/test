<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Account;
/**
 * Class banner
 * @package Magestore\Affiliateplus\Block\Account
 */
class Banner extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @var
     */
    protected $_banner_collection;
    /**
     * @var array
     */
    protected $_ids = [];
    /**
     * @var array
     */
    protected $_types = [];
    /**
     * @var array
     */
    protected $_sizes = [];

    /**
     * @return $this
     */
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }

    /**
     * @return void
     */
    protected function _construct(){
        parent::_construct();
        $storeId = $this->_storeManager->getStore()->getId();
        $bannerCollection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')->getCollection()->setStoreViewId($storeId);

        $this->_eventManager->dispatch('affiliateplus_banner_prepare_collection',[
            'collection'	=> $bannerCollection,
        ]
        );

        $ids = [];
        $types = [];
        $sizes = [];
        foreach ($bannerCollection as $banner){
            if ($banner->getStatus() == 1)
                $ids[] = $banner->getId();
            $types[] = $banner->getTypeId();
            if ($banner->getTypeId() != 3)
                $sizes[] = [
                    'w' => $banner->getWidth(),
                    'h' => $banner->getHeight()
                ];
        }
        $this->_ids = $ids;
        $this->_types = array_unique($types);
        $this->_sizes = $sizes;
    }

    /**
     * @param $collection
     * @return $this
     */
    public function setBannerCollection($collection){
        $this->_banner_collection = $collection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBannerCollection(){
        if (!$this->_banner_collection){
            $storeId = $this->_storeManager->getStore()->getId();
            $bannerCollection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')->getCollection()
                ->setStoreViewId($storeId)
                ->addFieldToFilter('main_table.banner_id',['in' => $this->_ids]);
            if ($type = $this->getRequest()->getParam('type'))
                $bannerCollection->addFieldToFilter('main_table.type_id',$type);
            if ($width = $this->getRequest()->getParam('w'))
                $bannerCollection->addFieldToFilter('main_table.width',$width);
            if ($height = $this->getRequest()->getParam('h'))
                $bannerCollection->addFieldToFilter('main_table.height',$height);

            $clickSelect = clone $bannerCollection->getSelect();
            $viewsSelect = clone $clickSelect;
            $clickSelect->reset()
                ->from(['ct' => $bannerCollection->getTable('magestore_affiliateplus_action')],[
                    'banner_id' => 'banner_id',
                    'raw_click' => 'SUM(totals)',
                    'uni_click' => 'SUM(is_unique)'
                ])->group('banner_id')
                ->where('type = ?', 2);
            $viewsSelect->reset()
                ->from(['vt' => $bannerCollection->getTable('magestore_affiliateplus_action')],[
                    'banner_id' => 'banner_id',
                    'raw_view' => 'SUM(totals)',
                    'uni_view' => 'SUM(is_unique)'
                ])->group('banner_id')
                ->where('type = ?', 1);

            $bannerCollection->getSelect()
                ->joinLeft(['c' => new \Zend_Db_Expr("({$clickSelect->__toString()})")],
                    'main_table.banner_id = c.banner_id',
                    ['raw_click', 'uni_click']
                )->joinLeft(['v' => new \Zend_Db_Expr("({$viewsSelect->__toString()})")],
                    'main_table.banner_id = v.banner_id',
                    ['raw_view', 'uni_view']
                );
            $this->setBannerCollection($bannerCollection);
        }
        return $this->_banner_collection;
    }

    /**
     * Filter
     *
     * @return array
     */
    public function getFilters(){
        $request = $this->getRequest();
        $filters = [
            [
                'label'	=> __('All'),
                'current'	=> !($request->getParam('type') || $request->getParam('w') || $request->getParam('h')),
                'url'	=> $this->getFilterUrl(),
            ]
        ];

        /**
         * Filter by size
         */
        foreach ($this->_sizes as $size)
            $filters[$size['w'].'x'.$size['h']] = [
                'label'	=> __('%1x%2',$size['w'],$size['h']),
                'current'	=> ($request->getParam('w') == $size['w'] && $request->getParam('h') == $size['h']),
                'url'	=> $this->getFilterUrl([
                    'w'	=> $size['w'],
                    'h'	=> $size['h']
                ])
            ];

        /**
         * Filter by type
         */
        $typesLabel = $this->getTypesLabel();
        foreach ($this->_types as $type)
            $filters[] = [
                'label'	=> $typesLabel[$type],
                'current'	=> ($request->getParam('type') == $type),
                'url'	=> $this->getFilterUrl([
                    'type' => $type
                ])
            ];

        return $filters;
    }

    /**
     * get Type label
     *
     * @return array
     */
    public function getTypesLabel(){
        return [
            1 => __('Image'),
            2 => __('Flash'),
            3 => __('Text')
        ];
    }

    /**
     * Get Filter URL
     *
     * @param array $params
     * @return string
     */
    public function getFilterUrl($params = []){
        return $this->getUrl('affiliateplus/banner/listbanner',['_query' => $params]);
    }

    /**
     * Get share url for banner
     *
     * @param $banner
     * @return string
     */
    public function getBannerUrl($banner){
        return $this->_dataHelper->getBannerUrl($banner);
    }

    /**
     * Get banner source file url
     *
     * @param $banner
     * @return string
     */
    public function getBannerSrc($banner){
        return $this->getBaseUrlMedia().'affiliateplus/banner/'.$banner->getSourceFile();
    }

    /**
     * get Account
     *
     * @return mixed
     */
    public function getAccount(){
        return $this->_sessionModel->getAccount();
    }

    /**
     * get Store code
     *
     * @return bool|string
     */
    public function getStoreCode(){
        if ($this->_storeManager->getDefaultStoreView() && $this->_storeManager->getStore()->getId() != $this->_storeManager->getDefaultStoreView()->getId())
            return $this->_storeManager->getStore()->getCode();
        return false;
    }

    /**
     * get Affiliate Url
     *
     * @return mixed
     */
    public function getAffiliateUrl(){
        return $this->_dataHelper->addAccToUrl($this->_storeManager->getStore()->getBaseUrl());
    }

    /**
     * get Image Url
     *
     * @param array $params
     * @return string
     */
    public function getImageUrl($params = array())
    {
//        if (!$this->_dataHelper->getConfig('affiliateplus/action/use_magento_link')) {
//            $url = $this->getJsUrl() . '/magestore/affiliateplus/banner.php?';
//            $url .= http_build_query($params);
//        } else {
            $url = $this->getUrl('affiliateplus/banner/image', $params);
//        }
        return $url;
    }

    /**
     * get Url js
     *
     * @return mixed
     */
    public function getJsUrl()
    {
        return $this->getViewFileUrl('Magestore_Affiliateplus::js');
    }

    /**
     * @return string
     */
    public function getPersonalUrlParameter()
    {
        return $this->_dataHelper->getPersonalUrlParameter();
    }

    /**
     * @param $key
     * @param null $store_id
     * @return mixed
     */
    public function getStoreConfig($key, $store_id=null){
        return $this->_dataHelper->getConfig($key, $store_id);
    }

    /**
     * get Base Url
     *
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->filterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getReferConfig($code)
    {
        return $this->_configHelper->getReferConfig($code);
    }

}