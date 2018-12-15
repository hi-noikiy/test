<?php
class EM_LayeredNavigation_Block_Search_Layer extends Mage_CatalogSearch_Block_Layer
{
	/**
	 * Default display for all filter type
	 *
	 * @var string
	 */
	protected $_defaultDisplay = 'text';
	
	/**
	 * Templates for all filters
	 *
	 * @var array
	 */
	protected $_filterTemplates = array();
	
	/**
	 * Display type for each filter (stored in config)
	 *
	 * @var array
	 */
	protected $_displayConfig = null;
	
	/**
	 * Change block type of catagory & price filter
	 *
	 */
	protected function _initBlocks() {
		parent::_initBlocks();
		
		$helper = Mage::helper('layerednavigation');
		$helper->setupRootCategory();
		$this->_filterTemplates = $helper->getFilterTemplate();
		
		$this->_categoryBlockName = 'layerednavigation/catalog_filter_category';
		
		$config = $this->_getDisplayConfig();
		if (isset($config['price']) && $config['price'] == 'slider')
			$this->_priceFilterBlockName = 'layerednavigation/catalog_filter_price';
	}
	
	/**
	 * Set custom template for filter
	 *
	 * @return string
	 */
	protected function _toHtml() {
		$filterableAttributes = $this->_getFilterableAttributes();
		foreach ($filterableAttributes as $attribute) {
			$this->getChild($attribute->getAttributeCode() . '_filter')->setTemplate($this->_getFilterTemplate($attribute->getAttributeCode()));
		}
		
		return parent::_toHtml();
	}
	
	/**
	 * Retrieve filter's configuration
	 *
	 * @return array
	 */
	protected function _getDisplayConfig() {
		if (!$this->_displayConfig)
			$this->_displayConfig = Mage::getModel('layerednavigation/filter')->getCollection()->getDisplayConfigs();
		return $this->_displayConfig;
	}
	
	/**
	 * Retrieve filter's template
	 *
	 * @return string
	 */
	protected function _getFilterTemplate($code) {
		// get template for filter has been configured in admin
		$config   = $this->_getDisplayConfig();
		$template = isset($config[$code]) && in_array($config[$code], array_keys($this->_filterTemplates)) ? $this->_filterTemplates[$config[$code]] : $this->_filterTemplates[$this->_defaultDisplay];
		return $template;
	}
}
