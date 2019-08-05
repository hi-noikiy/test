<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model\Management;



/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends \Magento\Framework\Model\AbstractModel
{


    private $xml;

    private $error;
    private $_shipment;
    private $_magento_shipment_id;
    private $chLogger;
    private $objectFactory;
    private $helperFactory;
    private $_moduleManager;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \CollinsHarper\Core\Logger\Logger $chLogger
     * @param \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \CollinsHarper\Core\Logger\Logger $chLogger,
        \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->_moduleManager = $moduleManager;

        $this->chLogger = $chLogger;
        $this->objectFactory = $objectFactory;
        $this->helperFactory = $helperFactory;


        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_construct();
    }


    /**
     * 
     * @param Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    public function setShipment($shipment)
    {
        $this->_shipment = $shipment;

        return $this;
    }

    /**
     * 
     * @return Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
       return $this->_shipment;
    }

    /**
     * 
     * @return int
     */
    public function getIncrementId()
    {
       return $this->_shipment->getIncrementId();
    }

    // TODO identify the datatype of the return
    /**
     * 
     * @return type
     */
    public function getOrder()
    {
       return $this->_shipment->getOrder();
    }

    /**
     * 
     * @return int
     */
    public function getOrderId()
    {
       return $this->_shipment->getOrder()->getId();
    }

    // TODO identify datatype of $track and return
    /**
     * 
     * @param type $track
     * @return type
     */
    public function addTrack($track)
    {
        $shipment = $this->objectFactory->create([], 'Magento\Sales\Model\Order\Shipment')->load($this->_magento_shipment_id);
       return $shipment->addTrack($track);
    }

    /**
     * 
     * @return array
     */
    public function getAllTracks()
    {
        if(!$this->_magento_shipment_id) {
            // issue?
        }
        $shipment = $this->objectFactory->create([], 'Magento\Sales\Model\Order\Shipment')->load($this->_magento_shipment_id);
       return $shipment && $shipment->getId() ? $shipment->getAllTracks() : null;
    }

    /**
     * on add shipment to manifest
     *
     * @param int $group_id
     * @param int $manifestId
     * @param int $magentoShipmentId
     * @return bool
     */
    public function createCpShipment($group_id, $manifestId, $magentoShipmentId) {

        $this->_shipment = $this->objectFactory->create([], 'Magento\Sales\Model\Order\Shipment')->load($magentoShipmentId);

        $shippingAddress = $this->_shipment->getShippingAddress();

        $quote = $this->objectFactory->create([], 'Magento\Quote\Model\Quote')->getCollection()->addFieldToFilter('entity_id', $this->getOrder()->getQuoteId())->getFirstItem();

        $params = array('weight' => 0);

        $params['_order'] = $this->getOrder();

        $pack = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Data')->getBoxForItems($this->_shipment->getAllItems());

        // TODO this is not being multi box aware
        $params['box'] = $pack[0]['box'];

        $params['weight'] = 0;
        if (!empty($params['box']['weight'])) {
            $params['weight'] = $params['box']['weight'];
        }

        foreach($this->_shipment->getAllItems() as $item) {
            $weight = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Data')->getConvertedWeight($item->getWeight());
            $params['weight'] += ($weight * $item->getQty()) ;
        }

        $params['service_code'] = str_replace(\CollinsHarper\CanadaPost\Model\Carrier::CODE . '_', '', $quote->getShippingAddress()->getShippingMethod());

        if ($params['service_code'] == 'failure') {

            $data = array(
                'country_code' => $quote->getShippingAddress()->getCountryId(),
                'postal-code'  => $quote->getShippingAddress()->getPostcode(),
                'weight'       => $params['weight'],
                'box'          => $params['box'],
                'xmlns' => 'http://www.canadapost.ca/ws/ship/rate'
            );

            $data = $this->objectFactory->create([], 'Magento\Quote\Model\Quoteparam')->getParamsByQuote($quote->getId(), $data);

            $rates = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\GetRates')->getRates($data);

            $service_price = 0;

            if (!empty($rates)) {

                foreach ($rates as $rate) {

                    if ($params['service_code'] == 'failure' || $rate['price'] < $service_price) {

                        $service_price = $rate['price'];

                        $params['service_code'] = $rate['code'];

                    }

                }

            }

        }

        $service_info = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\Service')->getInfo($params['service_code'], $quote->getShippingAddress()->getCountry());

        $mandatory_options = array();
        $available_options = array();


        if (!empty($service_info->options->option)) {

            foreach ($service_info->options->option as $opt) {

                if (strtolower((string)$opt->mandatory) == 'true') {

                    $mandatory_options[] = (string)$opt->{'option-code'};

                }

                $available_options[] = (string)$opt->{'option-code'};

            }

        }

        $quote_params = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Quoteparam')->getParamsByQuote($quote->getId());

        if (!empty($quote_params)) {

            $params['options'] = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Option')->composeForOrder($quote_params, $this->_shipment, $shippingAddress, $mandatory_options, $available_options);

            $params['cp_office_id'] = $quote_params['office_id'];

        }

        $params['current_shipment_id'] = $magentoShipmentId;

        if ($this->_moduleManager->isOutputEnabled('CollinsHarper_ShippingBox')) {

            // TODO not tested
            $packages = $this->helperFactory->create('CollinsHarper\ShippingBox\Helper\Data')->selectBoxForItems(getBoxForItems($quote->getAllItems()));

        }

        $response = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\Shipment')->create($shippingAddress, $quote, $group_id, $params);

        $result = false;

        $this->xml = new \SimpleXMLElement($response);

        if (!empty($this->xml->{'shipment-id'})
            //&& !empty($this->xml->{'tracking-pin'}) //hmmm - it seems this part is not always present
            && !empty($this->xml->links)
        ) {

            $this->saveShipmentInfo($manifestId, $magentoShipmentId);

            $result = true;

        } else if (!empty($this->xml->message->description)) {

            $this->chLogger->info(__METHOD__ . __LINE__);
            $this->chLogger->info(__METHOD__ . " - " . print_r($this->xml->message->description, 1));

            $this->error = $this->xml->message->description;

        }

        return $result ? $result :  $this->error;

    }


    private function saveShipmentInfo($manifestId, $magentoShipmentId)
    {

        $this->_magento_shipment_id = $magentoShipmentId;
        $shipmentId = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Shipment')
            ->setOrderId($this->getOrderId())
            ->setShipmentId($this->xml->{'shipment-id'})
            ->setStatus($this->xml->{'shipment-status'})
            ->setTrackingPin($this->xml->{'tracking-pin'})
            ->setManifestId($manifestId)
            ->setMagentoShipmentId($magentoShipmentId)
            ->save()
            ->getId();

        if (!empty($this->xml->links)) {

            foreach ($this->xml->links->link as $link) {

                $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Link')
                    ->setCpShipmentId($shipmentId)
                    ->setUrl($link['href'])
                    ->setMediaType($link['media-type'])
                    ->setRel($link['rel'])
                    ->save();

            }

        }

        $track = $this->objectFactory->create([], 'Magento\Sales\Model\Order\Shipment\Track')->addData(array(
            'carrier_code' => \CollinsHarper\CanadaPost\Model\Carrier::CODE,
            'title' => __('Shipment for order #%s' , $this->getIncrementId()),
            'number' => $this->xml->{'tracking-pin'},
        ));

        $this->addTrack($track);

    }


    /**
     *
     * @param Collinsharper_Canpost_Model_Shipment $cp_shipment
     * @return bool
     */
    public function removeCpShipment($cp_shipment)
    {
        if ($cp_shipment->getId()) {
            $this->_magento_shipment_id = $cp_shipment->getMagentoShipmentId();

            $result = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\Shipment')->void($cp_shipment->getId());

            $cp_shipment->delete();

            if($this->getAllTracks()) {
                foreach ($this->getAllTracks() as $track) {

                    $track->delete();

                }
            }

        }

        return true;

    }

    /**
     * 
     * @return string
     */
    public function getError()
    {

        return $this->error;

    }

    /**
     * 
     * @param string $tableName
     * @return string
     */
    public function getTableName($tableName)
    {
        return $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\ResourceModel\Link')->create()->getResource()->getTableName($tableName);
    }


    /**
     * 
     * @param int $manifestId
     * @return array
     */
    public function prepareGridCollection($manifestId = null)
    {

        $collection = $this->objectFactory->setClass('Magento\Sales\Model\Order\Shipment')->create()->getCollection();

        $collection->getSelect()->columns(array('shipment_increment_id' => 'increment_id'));

        $collection->getSelect()->joinLeft(
            array('cs' => $this->getTableName('ch_canadapost_shipment')),
            'main_table.entity_id = cs.magento_shipment_id',
            array('manifest_id')
        );

        if (!empty($manifestId) && $manifestId !== null) { //view

            $manifest = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Manifest')->create()->load($manifestId);

            if ($manifest->getStatus() == 'pending') {

                $collection->addFieldToFilter('cs.manifest_id', array(array('eq' => $manifestId), array('null'=>true)));

            } else {

                $collection->addFieldToFilter('cs.manifest_id', $manifestId);

            }

        } else { //create


            // TODO we want to show more states.
            //  $collection->addFieldToFilter('cs.manifest_id', array('null'=>true));

        }

        $collection->getSelect()->joinLeft(
            array('o' => $this->getTableName('sales_order')),
            'main_table.order_id = o.entity_id',
            array(
                'order_increment_id' => 'o.increment_id',
                'order_created_at' => 'o.created_at',
                'ordered_by' => 'CONCAT(o.customer_firstname, \' \',o.customer_lastname)',
            )
        );

        $collection->addFieldToFilter('o.shipping_method', array('like' => \CollinsHarper\CanadaPost\Model\Carrier::CODE . '_%'));

        return $collection;

    }



    // TODO remove this?
    // DEPRECATED
//    public function getExpiredCollection()
    public function _getExpiredCollection()
    {

        $collection = $this->getCollection();

        $collection->getSelect()
            ->joinLeft(
                array('s'=> $this->getTableName('sales_flat_shipment')),
                'main_table.magento_shipment_id = s.entity_id',
                array(
                    'shipment_increment_id' => 's.increment_id',
                    'created_at' => 's.created_at',
                    'total_qty' => 's.total_qty'
                )
            )
            ->joinLeft(
                array('o'=> $this->getTableName('sales_flat_order')),
                's.order_id = o.entity_id',
                array(
                    'order_increment_id' => 'o.increment_id',
                    'order_created_at' => 'o.created_at',
                    // TODO should we use new \Zend_Db_Expr
                    'ordered_by' => 'CONCAT(o.customer_firstname, \' \',o.customer_lastname)',
                )
            )
            ->joinLeft(
                array('p'=> $this->getTableName('ch_canadapost_quote_param')),
                'o.quote_id = p.magento_quote_id',
                array('est_delivery_date' => 'p.est_delivery_date')
            );

        return $collection;
    }

    // TODO remove this?
    // DEPRECATED
//    public function getExpired()
    public function _getExpired()
    {

        $collection = $this->getExpiredCollection();


        $collection->addFieldToFilter('is_delivered', 0);

        $collection->addFieldToFilter('is_checked', 0);

        $collection->addFieldToFilter('p.est_delivery_date', array('lt' => time()));

//         $collection->getSelect()
//             ->where('(is_delivered = 0');


        return $collection;

    }


}
