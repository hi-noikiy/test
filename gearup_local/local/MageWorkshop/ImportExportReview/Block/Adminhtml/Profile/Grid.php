<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mageworkshop_importexportreview/profile_collection');
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
            'adminhtml/drie_profile/edit',
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

        $this->addColumn('name', array(
            'header' => $this->_getHelper()->__('Profile Name'),
            'type' => 'text',
            'index' => 'name',
        ));

        $this->addColumn('type', array(
            'header' => $this->_getHelper()->__('Profile Type'),
            'type' => 'text',
            'index' => 'type',
            'renderer' => 'MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Grid_Renderer_Type'
        ));

        $this->addColumn('action', array(
            'header' => $this->_getHelper()->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'actions' => array(
                array(
                    'caption' => $this->_getHelper()->__('Edit'),
                    'url' => array(
                        'base' => 'adminhtml/drie_profile/edit',
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