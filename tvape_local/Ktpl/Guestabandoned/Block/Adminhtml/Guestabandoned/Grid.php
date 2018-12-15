<?php

class Ktpl_Guestabandoned_Block_Adminhtml_Guestabandoned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('guestabandonedGrid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}

	protected function _prepareCollection()
	{
		//$fromDate = '2017-04-01 00:00:00';
		$fromDate = Mage::getStoreConfig('customer/guestabandoned/startdate', $storeId);
		$collection = Mage::getModel('sales/quote')->getCollection()
                
		->addFieldToFilter('main_table.customer_id',array('null' => true))
		//->addFieldToFilter('telephone',array('notnull' => true))
                /*->addFieldToFilter(array('customer_email', 'customer_firstname','customer_lastname'),
                    array(array('notnull' => true), 
                        array('notnull' => true),
                        array('notnull' => true)
                    )
                ) */       
		->addFieldToFilter('main_table.created_at',array('from' => $fromDate, true))
		->addFieldToFilter('main_table.is_active',array('eq' => '1'));
                $collection->getSelect()->joinleft('sales_flat_quote_address', 'main_table.entity_id = sales_flat_quote_address.quote_id && sales_flat_quote_address.address_type = "shipping"',array('telephone'));  
                $collection->addFieldToFilter('sales_flat_quote_address.telephone',array('notnull' => true));
                //$collection->getSelect()->distinct();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{

		$this->addColumn('quote_id', array(
			'header'    => Mage::helper('guestabandoned')->__('ID'),
			'width'     => '75px',
			'index'     => 'entity_id',
		));
		  
		$this->addColumn('store_id', array(
			'header'    => Mage::helper('guestabandoned')->__('Purchased From (Store)'),
			'width'     => '100px',
			'index'     => 'store_id',
			'type'      => 'store',
			'store_view'=> true,     
		)); 
		 
		$this->addColumn('customer_firstname', array(
			'header'    => Mage::helper('guestabandoned')->__('First Name'),
			'width'     => '75px',
			'index'     => 'customer_firstname',
		));

		$this->addColumn('customer_lastname', array(
			'header'    => Mage::helper('guestabandoned')->__('Last Name'),
			'width'     => '75px',
			'index'     => 'customer_lastname',
		));

		$this->addColumn('customer_email', array(
			'header'    => Mage::helper('guestabandoned')->__('Email'),
			'width'     => '100px',
			'index'     => 'customer_email',
		));
		$this->addColumn('telephone', array(
			'header'    => Mage::helper('guestabandoned')->__('Telephone'),
			'width'     => '100px',
			'index'     => 'telephone',
		));
			
			$this->addColumn('created_at', array(
			'header'   => Mage::helper('guestabandoned')->__('Date'),
			'index'    => 'created_at',
			'type'     => 'datetime',
			'width'    => '100px',
		));

		$statuses = Mage::getSingleton('guestabandoned/status')->getOptionArray();
		$this->addColumn('status', array(
			'header'    => Mage::helper('guestabandoned')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => $statuses,
		));

		$this->addColumn('action',
		    array(
		        'header'    => Mage::helper('guestabandoned')->__('Action'),
		        'width'     => '50px',
		        'type'      => 'action',
		        'getter'     => 'getId',
		        'actions'   => array(
		            array(
		                'caption' => Mage::helper('guestabandoned')->__('View'),
		                'url'     => array('base'=>'*/adminhtml_guestabandoned/view'),
		                'field'   => 'entity_id'
		            )
		        ),
		        'filter'    => false,
		        'sortable'  => false,
		        'index'     => 'stores',
		        'is_system' => true,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('guestabandoned')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('guestabandoned')->__('XML'));
		  
		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
	 
	    $this->setMassactionIdField('entity_id');
	    $this->getMassactionBlock()->setFormFieldName('entity_id');

	    $this->getMassactionBlock()->addItem('delete', array(
	         'label'    => Mage::helper('guestabandoned')->__('Delete'),
	         'url'      => $this->getUrl('*/*/massDelete'),
	         'confirm'  => Mage::helper('guestabandoned')->__('Are you sure?')
	    ));

	    $statuses = Mage::getSingleton('guestabandoned/status')->getOptionArray();

	    array_unshift($statuses, array('label'=>'', 'value'=>''));
	    $this->getMassactionBlock()->addItem('status', array(
	        'label'=> Mage::helper('guestabandoned')->__('Change status'),
	        'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
	        'additional' => array(
	            'visibility' => array(
	                'name' => 'status',
	                'type' => 'select',
	                'class' => 'required-entry',
	                'label' => Mage::helper('guestabandoned')->__('Status'),
	                'values' => $statuses
	            )
	        )
	    ));
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('_current'=>true,'entity_id'=>$row->getId()));
	}

	public function getGridUrl()
	{
	    return $this->getUrl('*/*/grid', array('_current'=>true));
	}  

}