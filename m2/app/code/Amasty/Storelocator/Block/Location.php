<?php

namespace Amasty\Storelocator\Block;

use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Magento\Framework\View\Element\Template;
use Amasty\Storelocator\Block\View\Reviews;

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */
class Location extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Amasty_Storelocator::center.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * IO File
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    public $dataHelper;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Storelocator\Model\ConfigProvider
     */
    public $configProvider;

    /**
     * @var \Amasty\Storelocator\Model\Location
     */
    private $locationModel;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    private $locationCollection;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Gallery\Collection
     */
    private $galleryCollection;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;

    /**
     * @var array
     */
    private $attributeIds;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        \Amasty\Storelocator\Model\Location $locationModel,
        \Amasty\Storelocator\Model\ImageProcessor $imageProcessor,
        \Amasty\Storelocator\Model\ResourceModel\Gallery\Collection $galleryCollection,
        \Magento\Catalog\Model\Product $productModel,
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->filesystem = $context->getFilesystem();
        $this->jsonEncoder = $jsonEncoder;
        $this->ioFile = $ioFile;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->locationModel = $locationModel;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->productModel = $productModel;
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->getNameInLayout() != 'amasty.locator.center'
            && $this->getNameInLayout() != 'amasty.locator.left';
    }

    /**
     * @return string
     */
    public function getLeftBlockHtml()
    {
        $html = $this->setTemplate('Amasty_Storelocator::left.phtml')->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getMainBlockStyles()
    {
        $styles = '';
        if (!$this->isWrap()) {
            $styles = 'clear:both;';
        }

        return $styles;
    }

    /**
     * Get setting for showing store list in widget
     *
     * @return string
     */
    public function getShowLocations()
    {
        if (!$this->hasData('show_locations')) {
            return true; // not widget
        }

        return $this->getData('show_locations');
    }

    /**
     * Get setting for showing search block in widget
     *
     * @return string
     */
    public function getShowSearch()
    {
        if (!$this->hasData('show_search')) {
            return true; // not widget
        }

        return $this->getData('show_search');
    }

    /**
     * Get map wrap style
     *
     * @return bool
     */
    public function isWrap()
    {
        return (bool)$this->getData('wrap_block');
    }

    /**
     * Return map Element unic ID
     *
     * @return string
     */
    public function getMapId()
    {
        if (!$this->hasData('map_id')) {
            $this->setData('map_id', uniqid('amlocator-map-canvas'));
        }

        return $this->getData('map_id');
    }

    /**
     * Return search Element unic ID
     *
     * @return string
     */
    public function getSearchId()
    {
        if (!$this->hasData('search_id')) {
            $this->setData('search_id', uniqid('amlocator-search'));
        }

        return $this->getData('search_id');
    }

    /**
     * Return search radius Element unic ID
     *
     * @return string
     */
    public function getSearchRadiusId()
    {
        if (!$this->hasData('radius_id')) {
            $this->setData('radius_id', uniqid('amlocator-radius'));
        }

        return $this->getData('radius_id');
    }

    /**
     * Return Filter by attribute Element unic ID
     *
     * @return string
     */
    public function getAttributeFilterId()
    {
        if (!$this->hasData('filter_attribute_id')) {
            $this->setData('filter_attribute_id', uniqid('amasty-filter-attribute-id'));
        }

        return $this->getData('filter_attribute_id');
    }

    /**
     * Return Nearby button Element unic ID
     *
     * @return string
     */
    public function getNearbyButtonId()
    {
        if (!$this->hasData('nearby_button_id')) {
            $this->setData('nearby_button_id', uniqid('locateNearBy'));
        }

        return $this->getData('nearby_button_id');
    }

    /**
     * Return am_lat input unic ID
     *
     * @return string
     */
    public function getAmLatId()
    {
        if (!$this->hasData('am_lat')) {
            $this->setData('am_lat', uniqid('am_lat'));
        }

        return $this->getData('am_lat');
    }

    /**
     * Return am_lng input unic ID
     *
     * @return string
     */
    public function getAmLngId()
    {
        if (!$this->hasData('am_lng')) {
            $this->setData('am_lng', uniqid('am_lng'));
        }

        return $this->getData('am_lng');
    }

    /**
     * Return rating html
     *
     * @param $location
     *
     * @return string
     */
    public function getRatingHtml($location)
    {
        return $this->getLayout()->createBlock(Reviews::class)
            ->setData('location', $location)
            ->setTemplate('Amasty_Storelocator::rating.phtml')
            ->toHtml();
    }

    /**
     * Set rating
     *
     * @param $location
     */
    public function setRatingHtml($location)
    {
        $location->setRating($this->getRatingHtml($location));
    }

    /**
     * Return search button Element unic ID
     *
     * @return string
     */
    public function getSearchButtonId()
    {
        if (!$this->hasData('search_button_id')) {
            $this->setData('search_button_id', uniqid('sortByFilter'));
        }

        return $this->getData('search_button_id');
    }

    /**
     * Return stores list Element unic ID
     *
     * @return string
     */
    public function getStoresListId()
    {
        if (!$this->hasData('amlocator_store_list')) {
            $this->setData('amlocator_store_list', uniqid('amlocator_store_list'));
        }

        return $this->getData('amlocator_store_list');
    }

    public function getLocationCollection()
    {
        $pageNumber = (int)$this->getRequest()->getParam('p') ? (int)$this->getRequest()->getParam('p') : 1;
        if (!$this->locationCollection) {
            $this->locationCollection = $this->locationCollectionFactory->create();
            $this->locationCollection->applyDefaultFilters();
            $this->locationCollection->joinScheduleTable();
            if ($attributesData = $this->prepareWidgetAttributes()) {
                $this->locationCollection->applyAttributeFilters($attributesData);
            }
        }
        $this->locationCollection->setCurPage($pageNumber);
        $this->locationCollection->setPageSize($this->configProvider->getPaginationLimit());

        return $this->locationCollection;
    }

    /**
     * Get attribute ids
     *
     * @return array
     */
    public function getAttributeIds()
    {
        if (!$this->attributeIds) {
            $this->attributeIds = $this->attributeCollection->getAllIds();
        }
        return $this->attributeIds;
    }

    public function prepareWidgetAttributes()
    {
        $params = [];
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, $this->getAttributeIds())) {
                $params[$key] = explode(',', $value);
            }
        }

        return $params;
    }

    public function isOptionSelected($attribute, $option)
    {
        $widgetAttributes = $this->prepareWidgetAttributes();
        if (isset($widgetAttributes[$attribute['attribute_id']])
            && in_array($option['value'], $widgetAttributes[$attribute['attribute_id']])
        ) {
            return true;
        }

        return false;
    }

    public function validateLocations($locationCollection, $product)
    {
        foreach ($locationCollection as $location) {
            $valid = $this->dataHelper->validateLocation($location, $product);
            if ($valid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get store list template
     *
     * @return string
     */
    public function getStoreListTemplate()
    {
        if (!$this->hasData('store_list_template')) {
            $this->setData('store_list_template', $this->configProvider->getStoreListTemplate());
        }

        return $this->getData('store_list_template');
    }

    public function getBaloonTemplate()
    {
        $baloon = $this->configProvider->getLocatorTemplate();

        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        $baloon = str_replace(
            '{{photo}}',
            '<img src="' . $store_url . '{{photo}}">',
            $baloon
        );

        return $this->jsonEncoder->encode(["baloon" => $baloon]);
    }

    public function getAmStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        return $store_url;
    }

    /**
     * Get use browser location
     *
     * @return bool
     */
    public function getUseBrowserLocation()
    {
        if (!$this->hasData('usebrowserip')) {
            $this->setData('usebrowserip', $this->configProvider->getUseBrowser());
        }

        return $this->getData('usebrowserip');
    }

    /**
     * Get use geo ip
     *
     * @return bool
     */
    public function getGeoUse()
    {
        if (!$this->hasData('use')) {
            $this->setData('use', $this->configProvider->getUseGeo());
        }

        return $this->getData('use');
    }

    /**
     * Get clustering for map
     *
     * @return bool
     */
    public function getClustering()
    {
        if (!$this->hasData('clustering')) {
            $this->setData('clustering', $this->configProvider->getClustering());
        }

        return $this->getData('clustering');
    }

    /**
     * Get counting distance
     *
     * @return bool
     */
    public function getCountingDistance()
    {
        if (!$this->hasData('count_distance')) {
            $this->setData('count_distance', $this->configProvider->getCountDistance());
        }

        return $this->getData('count_distance');
    }

    /**
     * Get allowed countries
     *
     * @return string
     */
    public function getAllowedCountries()
    {
        $countriesString = $this->configProvider->getAllowedCountries();

        if (!empty($countriesString)) {
            $countriesArray = explode(',', $countriesString);
        } else {
            $countriesArray = [];
        }

        return $this->jsonEncoder->encode($countriesArray);
    }

    public function getJsonLocations()
    {
        $locationArray = [];
        $locationArray['items'] = [];
        foreach ($this->getLocationCollection()->getLocationData() as $location) {
            $locationArray['items'][] = $location;
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        return $this->jsonEncoder->encode($locationArray);
    }

    /**
     * Get zoom for map
     *
     * @return int
     */
    public function getMapZoom()
    {
        if (!$this->hasData('zoom')) {
            $this->setData('zoom', $this->configProvider->getZoom());
        }

        return $this->getData('zoom');
    }

    /**
     * Get filter class
     *
     * @return string|null
     */
    public function getFilterClass()
    {
        if ($this->configProvider->getCollapseFilter()) {
            return ' amlocator-hidden-filter';
        }
    }

    /**
     * Get automatic locate nearest location
     *
     * @return bool
     */
    public function getAutomaticLocate()
    {
        if (!$this->hasData('automatic_locate')) {
            $this->setData('automatic_locate', $this->configProvider->getAutomaticLocate());
        }

        return $this->getData('automatic_locate');
    }

    public function getDistanceConfig()
    {
        if (!$this->hasData('distance')) {
            $this->setData('distance', $this->configProvider->getDistanceConfig());
        }

        return $this->getData('distance');
    }

    public function getRadiusType()
    {
        if (!$this->hasData('radius_type')) {
            $this->setData('radius_type', $this->configProvider->getRadiusType());
        }

        return $this->getData('radius_type');
    }

    public function getMaxRadiusValue()
    {
        if (!$this->hasData('radius_max_value')) {
            $this->setData('radius_max_value', $this->configProvider->getMaxRadiusValue());
        }

        return $this->getData('radius_max_value');
    }

    public function getMinRadiusValue()
    {
        if (!$this->hasData('radius_min_value')) {
            $this->setData('radius_min_value', $this->configProvider->getMinRadiusValue());
        }

        return $this->getData('radius_min_value');
    }

    /**
     * Get radius from config
     *
     * @return array
     */
    public function getSearchRadius()
    {
        if (!$this->hasData('radius')) {
            $this->setData('radius', $this->configProvider->getRadius());
        }

        return explode(',', $this->getData('radius'));
    }

    public function getLinkToMap($params = [])
    {
        return $this->getUrl(
            $this->configProvider->getUrl(),
            ['_query' => $params]
        );
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    public function getProduct()
    {
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product');
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category');
        }

        return false;
    }

    public function getProductId()
    {
        if ($this->getRequest()->getParam('product')) {
            return (int)$this->getRequest()->getParam('product');
        }
        if ($this->coreRegistry->registry('current_product')) {
            return $this->coreRegistry->registry('current_product')->getId();
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategoryId()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category')->getId();
        }

        return false;
    }

    public function getProductById($productId)
    {
        $product = $this->productModel->load($productId);

        return $product;
    }

    public function getLinkText()
    {
        if (!$this->hasData('linktext')) {
            $this->setData('linktext', $this->configProvider->getLinkText());
        }

        return $this->getData('linktext');
    }

    /**
     * @param array $location
     *
     * @return string|bool
     */
    public function getLocationImage($location)
    {
        $locationId = $location['id'];
        $locationImage = $this->galleryCollection
            ->getBaseLocationImage($locationId)
            ->getData('image_name');

        if ($locationImage === null) {
            return false;
        }

        return $this->imageProcessor->getImageUrl([ImageProcessor::AMLOCATOR_GALLERY_MEDIA_PATH, $locationId, $locationImage]);
    }

    public function getTarget()
    {
        $target = '';

        if ($this->configProvider->getOpenNewPage()) {
            $target = 'target="_blank"';
        }

        return $target;
    }

    public function getAttributes()
    {
        $storeId = $this->_storeManager->getStore(true)->getId();

        return $this->attributeCollection->preparedAttributes($storeId);
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false
            && strpos($this->getNameInLayout(), 'jsinit') === false
        ) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }

            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }

            $this->getPagerHtml();

            if ($this->pager && !$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                );
            }
            if ($this->pager && $this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUrl('amlocator/index/ajax', ['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Add prev/next pages
     *
     * @param string $url
     * @param array $attributes
     *
     */
    private function addPrevNext($url, $attributes)
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link_rel',
            ['attributes' => $attributes]
        );
    }

    /**
     * Return Pager for locator page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('amasty.locator.pager')) {
            $this->pager = $this->getLayout()->getBlock('amasty.locator.pager');

            return $this->pager->toHtml();
        }
        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                Pager::class,
                'amasty.locator.pager'
            );

            if ($this->pager) {

                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->configProvider->getPaginationLimit()
                )->setCollection(
                    $this->getLocationCollection()
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }
}
