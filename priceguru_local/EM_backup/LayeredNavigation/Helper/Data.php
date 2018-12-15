<?php
class EM_LayeredNavigation_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * XML configuration paths for Layered Navigation
	 */
	const XML_PATH_AJAX = 'layerednavigation/general/enable_ajax';
	const XML_PATH_TEMPLATE = 'layerednavigation/template';
	
	public function isAjaxEnabled() {
		return intval(Mage::getStoreConfig(self::XML_PATH_AJAX));
	}
	
	/**
	 * Retrieve image file path
	 *
	 * @return string
	 */
	public function getImagePath($fileName) {
		return Mage::getBaseDir('media') . DS . 'em_layerednavigation' . DS . $fileName;
	}
	
	/**
	 * Get unique filename
	 *
	 * @return string
	 */
	public function getImageSaveName($fileName) {
		return md5(uniqid(rand(), true)).'_'. $fileName;
	}
	
	public function getImageUrl($fileName) {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'em_layerednavigation/' . $fileName;
	}
	
	/**
	 * Get image of filter value
	 *
	 * @return string
	 */
	public function getOptionImage($optionId) {
		$result = '';
		$model = Mage::getModel('layerednavigation/option')->getCollection()
			->addFieldToFilter('option_id', $optionId)
			->getFirstItem();
			
		if ($model->getId() && $model->getImage())
			$result = $this->getImageUrl($model->getImage());
		return $result;
	}
	
	/**
	 * Get filterable attributes
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection
	 */
	public function getFilterableAttributes() {
		/* snippet from Catalog/Model/Layer.php */
		$collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
			->addIsFilterableFilter()
            ->setOrder('position', 'ASC');
		return $collection;
	}
	
	/**
	 * Get filter model
	 *
	 * @return EM_LayeredNavigation_Model_Filter
	 */
	public function createFilter($attr) {
		$data = array(
			'position' => $attr->getPosition(),
			'title' => $attr->getFrontendLabel(),
			'attribute_code' => $attr->getAttributeCode(),
			'attribute_id' => $attr->getId(),
			'display_as' => 0
		);
		$model = Mage::getModel('layerednavigation/filter');
		$model->setData($data);
		return $model;
	}
	
	/**
	 * Get option model
	 *
	 * @return EM_LayeredNavigation_Model_Option
	 */
	public function createOption($option) {
		$data = array(
			'option_id' => $option['value'],
			'value' => $option['label'],
			'image' => ''
		);
		$model = Mage::getModel('layerednavigation/option');
		$model->setData($data);
		return $model;
	}
	
	/**
	 * Retrieve filter's available templates
	 *
	 * @return array
	 */
	public function getDisplayOptions($model = null) {
		$result = array(
			'slider' => 'Slider',
			'checkbox' => 'Checkbox', 
			'select' => 'Select', 
			'radio' => 'Radio', 
			'image' => 'Image', 
			'text' => 'Text'
		);
		
		if ($model && $model->getId()) {
			switch ($model->getAttributeCode()) {
				case 'price':
					$result = array(
						'slider' => 'Slider', 
						'text' => 'Text'
					);
					break;
				default:	// option
					$result = array(
						'checkbox' => 'Checkbox', 
						'select' => 'Select', 
						'radio' => 'Radio', 
						'image' => 'Image', 
						'text' => 'Text'
					);
					break;
			}
		}

		return $result;
	}

	/**
	 * Get filter remove url
	 *
	 * @return string
	 */
	public function getRemoveUrl($filter) {
		$result = '';
		// check if filter is currently applied
		if (isset($_GET[$filter->getRequestVar()])) {
			$query = array($filter->getRequestVar()=>$filter->getResetValue());
			$params['_current']     = true;
			$params['_use_rewrite'] = true;
			$params['_query']       = $query;
			$params['_escape']      = true;
			$result = Mage::getUrl('*/*/*', $params);
		}
		
		return $result;
	}

	/**
	 * Get all filter template. For touchable device, use default template for price filter
	 *
	 * @return array
	 */
	public function getFilterTemplate() {
		$result = Mage::getStoreConfig(self::XML_PATH_TEMPLATE);

		require_once(Mage::getBaseDir('lib') . DS . 'em/Mobile_Detect.php');
		$detect = new Mobile_Detect();
		$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
		if ($deviceType != 'computer')
			$result['slider'] = $result['text'];
		
		return $result;
	}
	
	/**
	 * Make the root category anchor
	 */
	public function setupRootCategory() {
		$rootId = Mage::app()->getStore()->getRootCategoryId();
		$cat = Mage::getModel('catalog/category')->load($rootId);
		if (!$cat->getIsAnchor()) {
			$cat->setIsAnchor(true);
			$cat->save();
		}
	}
}