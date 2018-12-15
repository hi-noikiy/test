<?php
class HN_Salesforce_Block_Adminhtml_Map_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'mapGrid' );
		$this->setDefaultSort ( 'id' );
		$this->setDefaultDir ( 'ASC' );
		$this->setSaveParametersInSession ( true );
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ( 'salesforce/map' )->getResourceCollection();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
	protected function _prepareColumns() {
		$this->addColumn ( 'id', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'ID' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'id' 
		) );
		
		$this->addColumn ( 'name', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Description' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'name' 
		) );
		
		$this->addColumn ( 'salesforce', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Saleforces Field' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'salesforce' 
		) );
		
		$this->addColumn ( 'magento', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Magento Field' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'magento' 
		) );

		$options = array(
				'0' => __('In active') ,
				'1' => __('Active'),
		);
		$this->addColumn ( 'status', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Status' ),
				'align' => 'right',
				'width' => '50px',
				'type'=> 'options',
				'options'=>$options,
				'index' => 'status' 
		) );
		
		$types = Mage::getSingleton('salesforce/field')->changeFields();
		$types += [
			'PriceBookEntry' => 'Price Book Entry',
			'PriceBook' => 'Price Book',
			'OrderItem' => 'OrderItem',
		];

		$this->addColumn ( 'type', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Type' ),
				'align' => 'right',
				'width' => '50px',
				'type'=> 'options',
				'options'=> $types,
				'index' => 'type' 
		) );
		$this->addColumn ( 'action', array (
				'header' => Mage::helper ( 'salesforce' )->__ ( 'Action' ),
				'align' => 'center',
				'width' => '30px',
				'type' => 'action',
				'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('salesforce')->__('Edit'),
                        'url'     => array('base'=>'*/*/edit'),
                        'field'   => 'id'
                        )
                    ),
                'filter'    => false,
                'sortable'  => false
		) );
		
		
		$this->addExportType ( '*/*/exportCsv', Mage::helper ( 'salesforce' )->__ ( 'CSV' ) );
		$this->addExportType ( '*/*/exportXml', Mage::helper ( 'salesforce' )->__ ( 'Excel XML' ) );
		return parent::_prepareColumns ();
	}
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array (
				'id' => $row->getId () 
		) );
	}
	protected function _prepareMassaction() {
		$this->setMassactionIdField ( 'id' );
		$this->getMassactionBlock ()->setFormFieldName ( 'id' );
		$this->getMassactionBlock ()->setUseSelectAll ( true );
	
	
		$this->getMassactionBlock ()->addItem ( 'delete', array (
				'label' => Mage::helper ( 'salesforce' )->__ ( 'Delete' ),
				'url'  => $this->getUrl('*/*/massDelete', array('' => '')),        // public function massDeleteAction() in Mage_Adminhtml_Tax_RateController
				'confirm' => Mage::helper('salesforce')->__('Are you sure ?')
		) );
		
		$statuses = [
			1    => Mage::helper('salesforce')->__('Active'),
			0  => Mage::helper('salesforce')->__('In active')
		];
		$this->getMassactionBlock()->addItem('status', array(
			'label' => Mage::helper('salesforce')->__('Change status'),
			'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('salesforce')->__('Status'),
					'values' => $statuses
			))
		));
		return $this;
	
	}
}
