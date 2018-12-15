<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Return_Address_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Mirasvit_Rma_Block_Adminhtml_Return_Address_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rma/return_address')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Mirasvit_Rma_Block_Adminhtml_Return_Address_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('address_id', array(
                'header' => Mage::helper('rma')->__('ID'),
                'index' => 'address_id',
                'filter_index' => 'main_table.address_id',
            )
        );
        $this->addColumn('title', array(
                'header' => Mage::helper('rma')->__('Title'),
                'index' => 'title',
                'filter_index' => 'main_table.title',
            )
        );
        $this->addColumn('sort_order', array(
                'header' => Mage::helper('rma')->__('Sort Order'),
                'index' => 'sort_order',
                'filter_index' => 'main_table.sort_order',
            )
        );
        $this->addColumn('is_active', array(
                'header' => Mage::helper('rma')->__('Active'),
                'index' => 'is_active',
                'filter_index' => 'main_table.is_active',
                'type' => 'options',
                'options' => array(
                    0 => $this->__('No'),
                    1 => $this->__('Yes'),
                ),
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return Mirasvit_Rma_Block_Adminhtml_Return_Address_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('address_id');
        $this->getMassactionBlock()->setFormFieldName('address_id');
        $statuses = array(
            array('label' => '', 'value' => ''),
            array('label' => $this->__('Disabled'), 'value' => 0),
            array('label' => $this->__('Enabled'), 'value' => 1),
        );
        $this->getMassactionBlock()->addItem('is_active', array(
            'label' => Mage::helper('rma')->__('Change status'),
            'url' => $this->getUrl('*/*/massChange', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'is_active',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('rma')->__('Status'),
                    'values' => $statuses,
                ),
            ),
        ));
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rma')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));

        return $this;
    }

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /************************/
}
