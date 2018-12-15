<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('filterGrid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection() {
		$attrCodes = array();	// filterable attribute ids
		$filterables = Mage::helper('layerednavigation')->getFilterableAttributes();
		foreach($filterables as $attr) {
			$attrCodes[] = $attr->getAttributeCode();
			$filterExists = Mage::getModel('layerednavigation/filter')
				->getCollection()
				->addFieldToFilter('attribute_code', $attr->getAttributeCode())
				->getSize();

			// save filter
			if (!$filterExists) {
				$model = Mage::helper('layerednavigation')->createFilter($attr, $storeId);
				$model->save();
			}
		}
		
		// get all filter
		$collection = Mage::getModel('layerednavigation/filter')->getCollection()
			->addFieldToFilter('attribute_code', array('in' => $attrCodes));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$this->addColumn('position', array(
		  'header'    => Mage::helper('layerednavigation')->__('Position'),
		  'align'     =>'left',
		  'index'     => 'position',
		  'width'     => '100px'
		));

		$this->addColumn('title', array(
		  'header'    => Mage::helper('layerednavigation')->__('Title'),
		  'align'     =>'left',
		  'index'     => 'title'
		));

		$this->addColumn('attribute_code', array(
		  'header'    => Mage::helper('layerednavigation')->__('Attribute'),
		  'align'     =>'left',
		  'index'     => 'attribute_code'
		));

		$this->addColumn('display_as', array(
			'header' => Mage::helper('layerednavigation')->__('Display As'),
			'align' => 'left',
			'index' => 'display_as',
			'type'      => 'options',
			'options'   => Mage::helper('layerednavigation')->getDisplayOptions()
			)
		);
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array(
			'id' => $row->getId()
		));
	}
}