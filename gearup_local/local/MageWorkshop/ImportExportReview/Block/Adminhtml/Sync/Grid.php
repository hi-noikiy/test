<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Sync_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mageworkshop_importexportreview/sync_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/drie_sync/edit',
            array(
                'id' => $row->getId()
            )
        );
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->_getHelper()->__('ID'),
            'type' => 'number',
            'index' => 'id',
        ));

        $this->addColumn('store_name', array(
            'header' => $this->_getHelper()->__('Store Name'),
            'type' => 'text',
            'index' => 'store_name',
        ));

        $this->addColumn('store_identity', array(
            'header' => $this->_getHelper()->__('Store Identity'),
            'type' => 'text',
            'index' => 'store_identity',
        ));

        $this->addColumn('store_url', array(
            'header' => $this->_getHelper()->__('Store URL'),
            'type' => 'text',
            'index' => 'store_url',
        ));

        $this->addColumn('last_export', array(
            'header' => $this->_getHelper()->__('Last Sync Date'),
            'type' => 'text',
            'index' => 'last_export',
        ));

        $this->addColumn('action', array(
            'header' => $this->_getHelper()->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'actions' => array(
                array(
                    'caption' => $this->_getHelper()->__('Edit'),
                    'url' => array(
                        'base' => 'adminhtml/drie_sync/edit',
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }
}