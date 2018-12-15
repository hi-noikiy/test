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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_ThirdPartyEmail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('third_party_email_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdesk/thirdPartyEmail')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('third_party_email_id', array(
            'header'       => Mage::helper('helpdesk')->__('ID'),
            'index'        => 'third_party_email_id',
            'filter_index' => 'main_table.third_party_email_id',
            )
        );
        $this->addColumn('name', array(
            'header'         => Mage::helper('helpdesk')->__('Contact Name'),
            'index'          => 'name',
            'filter_index'   => 'main_table.name',
            )
        );
        $this->addColumn('sort_order', array(
            'header'       => Mage::helper('helpdesk')->__('Email'),
            'index'        => 'email',
            'filter_index' => 'main_table.email',
            )
        );
        $this->addColumn('active', array(
            'header'       => Mage::helper('helpdesk')->__('Is Active'),
            'index'        => 'is_active',
            'filter_index' => 'main_table.is_active',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('third_party_email_id');
        $this->getMassactionBlock()->setFormFieldName('third_party_email_id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('helpdesk')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
        ));

        return $this;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_ThirdPartyEmail $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /************************/
}
