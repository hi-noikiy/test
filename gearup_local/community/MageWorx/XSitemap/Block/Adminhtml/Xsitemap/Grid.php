<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemapGrid');
        $this->setDefaultSort('sitemap_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('mageworx_xsitemap/sitemap')->getCollection();
        /* @var $collection MageWorx_XSitemap_Model_Mysql4_Sitemap_Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'sitemap_id',
            array(
            'header' => Mage::helper('sitemap')->__('ID'),
            'width'  => '50px',
            'index'  => 'sitemap_id'
            )
        );

        $this->addColumn(
            'sitemap_filename',
            array(
            'index'  => 'sitemap_filename',
            'header' => Mage::helper('sitemap')->__('Filename')
            )
        );

        $this->addColumn(
            'sitemap_path',
            array(
            'index'  => 'sitemap_path',
            'header' => Mage::helper('sitemap')->__('Path'),
            )
        );

        $this->addColumn(
            'link',
            array(
            'header'   => Mage::helper('sitemap')->__('Link for Google'),
            'index'    => 'concat(sitemap_path, sitemap_filename)',
            'renderer' => 'mageworx_xsitemap/adminhtml_xsitemap_grid_renderer_link',
            )
        );

        $this->addColumn(
            'sitemap_time',
            array(
            'header' => Mage::helper('sitemap')->__('Last Time Generated'),
            'index'  => 'sitemap_time',
            'width'  => '150px',
            'type'   => 'datetime',
            )
        );


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                'header' => Mage::helper('sitemap')->__('Store View'),
                'index'  => 'store_id',
                'type'   => 'store',
                )
            );
        }

        $this->addColumn(
            'action',
            array(
            'header'   => Mage::helper('sitemap')->__('Action'),
            'filter'   => false,
            'sortable' => false,
            'width'    => '100',
            'renderer' => 'mageworx_xsitemap/adminhtml_xsitemap_grid_renderer_action'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('sitemap_id' => $row->getId()));
    }

}
