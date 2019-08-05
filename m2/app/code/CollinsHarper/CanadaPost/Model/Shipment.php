<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model;


use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\App\ResourceConnection;



/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_shipment';

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
     * @var \CollinsHarper\CanadaPost\Model\ObjectFactory 
     */
    protected $objectFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->objectFactory = $objectFactory;
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
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Shipment');
    }

    public function getShipmentByOrderId($order_id)
    {
        $cp_shipment = $this->getCollection()
            ->addFieldToFilter('order_id', $order_id);

        return $cp_shipment->getSelect()
            ->joinLeft(array('m' => $this->getTableName('ch_canadapost_manifest')),
                'main_table.manifest_id = m.entity_id',
                array('manifest_status' => 'm.status'))
            ->getFirstItem();

    }

    public function getShipmentById($shipment_id)
    {
        return $this->getCollection()
            ->addFieldToFilter('magento_shipment_id', $shipment_id)
            ->getFirstItem();
    }


    public function getTableName($tableName)
    {
        return $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\ResourceModel\Link')->create()->getResource()->getTableName($tableName);
    }

    /**
     * Makes a request to Canada Post for this shipment's price details and
     * returns the final price of the shipment as a float.
     * @return number
     */
    public function fetchShipmentPrice()
    {
        return (float) $this->fetchLink('price')->{'due-amount'};
    }

    public function fetchLink($rel = 'self')
    {
        return $this->getLink($rel)->fetchDetails();
    }

    /**
     * @return Collinsharper_Canpost_Model_Link
     */
    public function getLink($rel = 'self')
    {
        $collection = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Link')->create()->getCollection()
            ->addFieldToFilter('cp_shipment_id', $this->getId())
            ->addFieldToFilter('rel', $rel);

        if (count($collection) < 1) {
            throw new \Exception("Tried to load '{$rel}' link for shipment #{$this->getId()} but could not find one.");
        }

        return $collection->getFirstItem();
    }


    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


}
