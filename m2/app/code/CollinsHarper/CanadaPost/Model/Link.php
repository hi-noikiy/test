<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model;


use Magento\Framework\DataObject\IdentityInterface;


/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Link extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_link';

    /**
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    
    /**
     *
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     *
     * @var \CollinsHarper\CanadaPost\Helper\Rest\Request
     */
    protected $_restRequest;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \CollinsHarper\CanadaPost\Helper\Rest\Request $restRequest
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \CollinsHarper\CanadaPost\Helper\Rest\Request $restRequest,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_restRequest = $restRequest;
        $this->_appState = $context->getAppState();
        $this->_eventManager = $context->getEventDispatcher();
        $this->_cacheManager = $context->getCacheManager();
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->_logger = $context->getLogger();
        $this->_actionValidator = $context->getActionValidator();


        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_construct();
    }

    protected function _construct()
    {
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Link');
    }
    
    /**
     * 
     * @param int $order_id
     * @param string $type
     * @return array
     */
    public function getLabelDataByOrderId($order_id, $type='label')
    {

        $collection = $this->getCollection()
            ->addFieldToFilter('s.order_id', $order_id)
            ->addFieldToFilter('rel', $type);

        // TODO how do I get a table name
        $collection->getSelect()->joinLeft(
            array('s' => $this->getResource()->getResource()->getTableName('ch_canadapost_shipment')),
            'main_table.cp_shipment_id=s.entity_id',
            array()
        );

        return $collection->getFirstItem()->getData();

    }

    /**
     * 
     * @param int $mShipmentId
     * @param string $type
     * @return array
     */
    public function getLabelDataByMageShipmentId($mShipmentId, $type='label')
    {

        $collection = $this->getCollection()
            ->addFieldToFilter('s.magento_shipment_id', $mShipmentId)
            ->addFieldToFilter('rel', $type);

        // TODO how do I get a table name
        $collection->getSelect()->joinLeft(
            array('s' => $this->getResource()->getResource()->getTableName('ch_canadapost_shipment')),
            'main_table.cp_shipment_id=s.entity_id',
            array()
        );

        return $collection->getFirstItem()->getData();

    }

    /**
     * 
     * @return array
     */
    public function getManifests()
    {

        $collection = $this->getCollection()
            ->addFieldToFilter('rel', 'manifest');

        $collection->getSelect()->joinLeft(
            array('ch_chipment'=> $this->getResource()->getResource()->getTableName('ch_canadapost_shipment')),
            'main_table.cp_shipment_id=ch_chipment.entity_id',
            array('order_id' => 'ch_chipment.order_id')
        );

        return $collection;

    }

    // identify the datatype of @return
    /**
     * 
     * @return type
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * 
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }



    /**
     * 
     * @return \SimpleXMLElement
     */
    public function fetchDetails()
    {
        $responseXml = $this->_restRequest->send($this->getUrl(), "", false, array('Accept: ' . $this->getMediaType()));
        $response = new \SimpleXMLElement($responseXml);
        return $response;
    }

}
