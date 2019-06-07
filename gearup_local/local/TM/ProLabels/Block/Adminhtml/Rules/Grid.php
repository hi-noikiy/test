<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('prolabelsGrid');
        $this->setDefaultSort('rules_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('pro_rules_filter');
    }

    public function getCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::getModel('prolabels/label')
                ->getCollection()
                ->addFilter('system_label', array('eq' => '0'));
        }

        return $this->_collection;
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $position = Mage::getModel('prolabels/entity_attribute_source_position');
        $this->addColumn('rules_id', array(
            'header'    => Mage::helper('prolabels')->__('ID'),
            'align'     =>'right',
            'width'     => '20px',
            'index'     => 'rules_id',
            'type'      => 'number'
        ));

        $this->addColumn('label_name', array(
            'header'    => Mage::helper('prolabels')->__('Name'),
            'align'     =>'left',
            'width'     => '450px',
            'index'     => 'label_name'
        ));

        $this->addColumn('product_position', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Position'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'product_position',
            'type'      => 'options',
            'options'   => $position->toOptionArray()
        ));

        $this->addColumn('product_image', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Image'),
            'align'     => 'center',
            'index'     => 'product_image',
            'width'     => '150',
            'renderer'  => 'TM_ProLabels_Block_Adminhtml_Rules_Grid_Renderer_Pimg'
        ));

        $this->addColumn('product_image_text', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Text'),
            'align'     =>'left',
            'width'     => '150px',
            'index'     => 'product_image_text'
        ));

        $this->addColumn('category_position', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Position'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'category_position',
            'type'      => 'options',
            'options'   => $position->toOptionArray()
        ));

        $this->addColumn('category_image', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Image'),
            'align'     => 'center',
            'index'     => 'category_image',
            'width'     => '150px',
            'renderer'  => 'TM_ProLabels_Block_Adminhtml_Rules_Grid_Renderer_Cimg'
        ));

        $this->addColumn('category_image_text', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Text'),
            'align'     =>'left',
            'width'     => '150px',
            'index'     => 'category_image_text'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                    => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('priority', array(
            'header'    => Mage::helper('prolabels')->__('Priority'),
            'align'     =>'left',
            'width'     => '40px',
            'index'     => 'priority'
        ));

        $this->addColumn('label_status', array(
            'header'    => Mage::helper('prolabels')->__('Enabled'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'label_status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            ),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        return parent::_prepareColumns();
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getLabelStatus() + $row->getLStatus()) {
            case 1 :
                $class = 'grid-severity-notice';
                break;
            case 0 :
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRulesId()));
    }
}
