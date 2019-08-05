<?php
/**
 * Copyright � 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Block\Adminhtml\Manifest;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \CollinsHarper\CanadaPost\Model\ObjectFactory
     */
    private $objectFactory;

    /**
     * @var \CollinsHarper\Core\Logger\Logger
     */
    protected $chLogger;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \CollinsHarper\Core\Logger\Logger $chLogger
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \CollinsHarper\Core\Logger\Logger $chLogger,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->chLogger = $chLogger;

        $this->moduleManager = $moduleManager;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('main_table.entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {


        // collection?
        $collection = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Management\Shipment')->prepareGridCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset(
                    $data[$columnId]
                ) && (is_array(
                        $data[$columnId]
                    ) && !empty($data[$columnId]) || strlen(
                        $data[$columnId]
                    ) > 0) && $column->getFilter()
            ) {

                if('manifest_id' == $columnId) {

                    if($data[$columnId] == \CollinsHarper\CanadaPost\Model\Source\Manifest\Status::CURRENT && $this->getLoadedManifest()) {
                        $column->getFilter()->setValue($this->getLoadedManifest()->getId());
                    } else if ($data[$columnId] == \CollinsHarper\CanadaPost\Model\Source\Manifest\Status::YES) {
                        // TODO how do we say not empty?
                        //$column->getFilter()->setValue($this->getLoadedManifest()->getId());
                    } else {
                        // TODO  how do we say null?
                        // $column->getFilter()->setValue($this->getLoadedManifest()->getId());
                    }
                } else {
                    $column->getFilter()->setValue($data[$columnId]);
                }

                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * 
     * @return CollinsHarper\Model\Manifest.php
     */
    protected function getLoadedManifest()
    {
        return $this->_coreRegistry->registry('manifest');
    }


    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'filter_index' => 'main_table.entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        
        $this->addColumn(
            'shipment_increment_id',
            [
            'header'    => __('Shipment #'),
            'index'     => 'shipment_increment_id',
            'filter_index'     => 'main_table.increment_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);



        $this->addColumn('manifest_id',
            [
                'header' => __('Is in Manifest'),
                'index' => 'manifest_id',
               'type' => 'options',
                'renderer' => 'CollinsHarper\CanadaPost\Block\Adminhtml\Manifest\Grid\Renderer\ManifestId',
                'options' => $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Source\Manifest\Status')->toOptionArray()
            ]);

        $this->addColumn('created_at', [
            'header'    => __('Date Shipped'),
            'index'     => 'created_at',
            'filter_index'     => 'main_table.created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('order_increment_id', [
            'header'    => __('Order #'),
            'index'     => 'order_increment_id',
            'filter_index'     => 'o.increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('order_created_at', [
            'header'    => __('Order Date'),
            'index'     => 'order_created_at',
            'filter_index'     => 'o.created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('ordered_by', [
            'header' => __('Ordered By'),
            'index' => 'ordered_by',
            // TODO can we filter on both ?
            'filter_index' => 'o.customer_firstname',
        ]);

        $this->addColumn('total_qty', [
            'header' => __('Total Qty'),
            'index' => 'total_qty',
            'filter_index' => 'main_table.total_qty',
            'type'  => 'number',
        ]);




        $this->addColumn(
            'view',
            [
                'header' => __('View'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('view'),
                        'url' => [
                            'base' => '*/sales_shipment/view'
                        ],
                        'field' => 'shipment_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.entity_id');
 //       $this->getMassactionBlock()->setTemplate('CollinsHarper_CanadaPost::manifest/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('manifest');

        $label = __('Create Canada Post Shipments');
        $action =  $this->getUrl('cpcanadapost/manifest/massCreate');

        $hasManifest = $this->getLoadedManifest() && $this->getLoadedManifest()->getId();

        if($hasManifest) {
            $label =  __('Add Shipments');
            $action =  $this->getUrl('cpcanadapost/manifest/massLabel', array('manifest_id' => $this->getLoadedManifest()->getId()));
        }

        $this->getMassactionBlock()->addItem(
            'create',
            [
                'label' => $label,
                'url' => $action,
             //   'confirm' => __('Are you sure?')
            ]
        );

        if($hasManifest) {

            if($this->getLoadedManifest()->getStatus() == \CollinsHarper\CanadaPost\Model\Manifest::STATUS_PENDING) {
                $this->getMassactionBlock()->addItem(
                    'remove',
                    [
                        'label' => __('Remove Shipments'),
                        'url' => $this->getUrl('cpcanadapost/manifest/removeShipments',  array('manifest_id' => $this->getLoadedManifest()->getId())),
                        'confirm' => __('Are you sure?')
                    ]
                );
                /*
                 * TODO - previously we allowed to delete shipments; was there an actual reason for that?
                 *                 $this->getMassactionBlock()->addItem(
                    'delete',
                    [
                        'label' => __('Delete Shipments'),
                        'url' => $this->getUrl('cpcanadapost/manifest/removeShipments', array('manifest_id' => $this->getLoadedManifest()->getId(), '_delete' => true)),
                        'confirm' => __('This will remove the shipments from the manifest as well as delete the Magento Shipment, continue?')
                    ]
                );
                 */

            }

            $this->getMassactionBlock()->addItem(
                'print',
                [
                    'label' => __('Print Shipments'),
                    'url' => $this->getUrl('cpcanadapost/manifest/printShipments'),
                 //   'confirm' => __('Are you sure?')
                ]
            );

        }



        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('cpcanadapost/manifest/grid', ['_current' => true]);
    }

    /**
     * @param \CollinsHarper\CanadaPost\Model\Shipment|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/shipment/edit',
            ['shipment_id' => $row->getId()]
        );
    }
}
