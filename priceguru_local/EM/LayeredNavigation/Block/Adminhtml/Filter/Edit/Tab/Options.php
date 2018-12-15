<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() {
		parent::__construct();
		$this->setId('optionsGrid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('asc');
	}
	
	protected function _prepareCollection() {
		// get all select option of filter
		$filter = Mage::registry('layerednavigation_filter');
		$attribute = Mage::getResourceModel('catalog/eav_attribute')->load($filter->getAttributeId());
		$options = $attribute->getFrontend()->getSelectOptions();
		$oIds = array();
		foreach ($options as $option) {
			if (empty($option['value'])) continue;
			$oIds[] = $option['value'];
			$oExists = Mage::getModel('layerednavigation/option')
				->getCollection()
				->addFieldToFilter('option_id', $option['value'])
				->getSize();

			// save filter
			if (!$oExists) {
				$model = Mage::helper('layerednavigation')->createOption($option);
				$model->save();
			}
		}
		
		// get all options
		$collection = Mage::getModel('layerednavigation/option')->getCollection()
			->addFieldToFilter('option_id', array('in' => $oIds));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$this->addColumn('value', array(
		  'header'    => Mage::helper('layerednavigation')->__('Value'),
		  'align'     =>'left',
		  'index'     => 'value',
		  'width'     => '150px'
		));

		$this->addColumn('image', array(
		  'header'    => Mage::helper('layerednavigation')->__('Image'),
		  'align'     =>'left',
		  'index'     => 'image',
		  'filter'    => false,
		  'sortable'  => false,
		  'renderer'  => 'layerednavigation/adminhtml_renderer_image'
		));

		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row) {
		return '';	// prevent row click
	}
	
	public function getRowClass($row) {
		$result = parent::getRowClass($row);
		$result .= "r-".$row->getId();
		return $result;
	}
}
