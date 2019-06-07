<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml complaint grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Complaint_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('complaintGrid');
        $this->setDefaultSort('entity_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('detailedreview/complaintType')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Mage_Review_Helper_Data $reviewHelper */
        $reviewHelper =  Mage::helper('review');

        $this->addColumn('entity_id', array(
            'header'        => $reviewHelper->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'entity_id',
            'index'         => 'entity_id',
        ));

        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn('status', array(
                'header'        => $reviewHelper->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       =>  Mage::getModel('detailedreview/source_common_status')->toOptionArray(),
                'width'         => '100px',
                'filter_index'  => 'main_table.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn('title', array(
            'header'        => $reviewHelper->__('Title'),
            'align'         => 'left',
            'filter_index'  => 'title',
            'index'         => 'title',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEntityId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/mageworkshop_detailedreview_complaint/edit',
                         ),
                         'field'   => 'entity_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        return parent::_prepareColumns();
    }

    /**
     * @inherit
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('complaint_data');

        $helper = Mage::helper('detailedreview');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $helper->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $helper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem(
            'update_status',
            array(
                'label'      => $helper->__('Update status'),
                'url'        => $this->getUrl('*/*/massUpdateStatus'),
                'additional' => array(
                    'status' => array(
                        'name'   => 'update_status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $helper->__('Status'),
                        'values' => array(
                            1 => $helper->__('Enabled'),
                            0 => $helper->__('Disabled')
                        )
                    )
                )
            )
        );
        Mage::dispatchEvent('detailedreview_adminhtml_complaint_grid_prepare_massaction', array('block' => $this));
        return $this;
    }
}
