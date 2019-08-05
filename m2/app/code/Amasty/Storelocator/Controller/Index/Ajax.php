<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Index;

class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    private $locationCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->locationCollection = $locationCollection;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $storeListId = $this->getRequest()->getParam('storeListId');
        $mapId = $this->getRequest()->getParam('mapId');
        $this->locationCollection->applyDefaultFilters();

        $this->_view->loadLayout();

        $arrayCollection = [];

        foreach ($this->locationCollection->getLocationData() as $item) {
            $arrayCollection['items'][] = $item;
        }

        $arrayCollection['totalRecords'] = isset($arrayCollection['items']) ? count($arrayCollection['items']) : 0;

        $left = $this->_view->getLayout()
            ->createBlock(\Amasty\Storelocator\Block\Location::class)
            ->setTemplate('Amasty_Storelocator::left.phtml')
            ->setMapId($mapId)
            ->setAmlocatorStoreList($storeListId)
            ->toHtml();

        $res = array_merge_recursive(
            $arrayCollection,
            ['block' => $left, 'storeListId' => $storeListId]
        );

        $json = $this->jsonEncoder->encode($res);

        $this->getResponse()->setBody($json);
    }
}
