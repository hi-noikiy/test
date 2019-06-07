<?php
class Gearup_Bannerwidget_Block_Rightbanner extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    /**
     * Widget Parameters
     * 
     * @var array
     */
    protected $params = array();

    /**
     * Widget Config Object
     * 
     * @var Varien_Object
     */
    protected $widgetConfig;

    /**
     * ResourceModel
     * 
     * @var Mage_Widget_Model_Widget_Instance
     */
    protected $resourceModel;

    /**
     * Initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bannerwidget/rightbanner.phtml');
    }

    /**
     * On prepare layout
     */
    protected function _prepareLayout() 
    {
        parent::_prepareLayout();
        //$this->loadAssets();
    }

    /**
     * Content of block
     * 
     * @param string $blockId
     * @return string
     */
    public function getBlockContent($blockId)
    {
        return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }

    /**
     * Gets widget parametrs
     * 
     * @return array
     */
    public function getWidgetParams()
    {
        if (count($this->params) == 0 && is_object($this->getResourceModel()))
        {
            $this->params = $this->validateParams($this->getResourceModel()->getWidgetParameters());
        }
        return $this->params;
    }

    /**
     * Validation params
     * 
     * @param array $params
     * @return array
     */
    protected function validateParams($params)
    {
        return $params;
    }

    /**
     * Gets widget instance from model
     * 
     * @return Mage_Widget_Model_Widget_Instance
     */
    protected function getResourceModel()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $widgetId = Mage::getStoreConfig('bannerwidget/general/rightbannerwidgetid', $storeId);
        $this->setWidgetId($widgetId);
        if (!isset($this->resourceModel))
        {
            $this->resourceModel = Mage::getResourceModel('widget/widget_instance_collection')->getItemById($this->getWidgetId());
        }
        
        return $this->resourceModel;
    }

    /**
     * Configuration object
     * 
     * @return Varien_Object
     */
    public function getWidgetConfig()
    {
        if (!isset($this->widgetConfig))
        {
            $this->widgetConfig = new Varien_Object();
            $this->widgetConfig->addData($this->getWidgetParams());
        }
        return $this->widgetConfig;
    }

    /**
     * Check It's 
     * 
     * @return Varien_Object
     */
    public function validateLoad()
    {
        $chkIsLoad = false;
        $currentUrlKey = $_product = null;
        $catArr = $cWhiteListUrl = array();
        if($this->getWidgetConfig()->getVisibility()) {
            $whiteListUrl = $this->getWidgetConfig()->getPagelist();
            $whiteListUrl = array_filter($whiteListUrl, function($value) { return $value !== ''; });
            if (Mage::registry('current_category')) 
            {
                $currentUrlKey = Mage::registry('current_category')->getUrlKey();
            } else if (Mage::registry('current_product')) {
                $_product = Mage::registry('current_product');
                $catCollection = $_product->getCategoryCollection()
                                    ->addAttributeToSelect('url_key');
                foreach($catCollection as $category) {
                    $catArr[] = $category->getUrlKey();
                }
            } else if(Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
                $currentUrlKey = Mage::getSingleton('cms/page')->getIdentifier();
            }
            if(in_array($currentUrlKey, $whiteListUrl)) {
                $chkIsLoad = true;
            }
            if(count($catArr) > 0) {
                $catArr = array_intersect($whiteListUrl, $catArr);
                if(count($catArr) > 0)
                    $chkIsLoad = true;
            }

            $currentUrl = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
            $cWhiteListUrl = $this->getWidgetConfig()->getCustompagelist();
            $cWhiteListUrl = explode("\n", $cWhiteListUrl);
            if(in_array($url->getPath(), $cWhiteListUrl)) {
                $chkIsLoad = true;
            }
        }
        return $chkIsLoad;
    }
}