<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\Address as Repository;
use Amasty\MultiInventory\Helper\Distance as Helper;
use Amasty\MultiInventory\Model\CustomerCoordinatesFactory;
use Amasty\MultiInventory\Model\CustomerCoordinates;

class SaveCustomerCoordinates implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CustomerCoordinates
     */
    private $customerCoordinates;

    /**
     * SaveCustomerCoordinates constructor.
     * @param Repository $repository
     * @param Helper $helper
     * @param CustomerCoordinatesFactory $customerCoordinates
     */
    public function __construct(Repository $repository, Helper $helper, CustomerCoordinatesFactory $customerCoordinates)
    {
        $this->repository = $repository;
        $this->helper = $helper;
        $this->customerCoordinates = $customerCoordinates->create();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $latlng = null;
        /** @var \Magento\Customer\Model\ResourceModel\Address $customerAddress */
        $customerAddress = $observer->getCustomerAddress();
        $originAddress = $customerAddress->getStoredData();
        if ($this->helper->prepareAddressForGoogle($originAddress)
            != $this->helper->prepareAddressForGoogle($customerAddress->getData())
        ) {
            $addressId = $customerAddress->getId();
            $this->customerCoordinates->load($addressId, CustomerCoordinates::ADDRESS_ID);
            $this->customerCoordinates->setAddressId($addressId);
            $address = $this->helper->prepareAddressForGoogle($customerAddress->getData());
            $latlng = $this->helper->getCoordinatesByAddress($address);
            if ($latlng) {
                $this->customerCoordinates->setLat($latlng['lat']);
                $this->customerCoordinates->setLng($latlng['lng']);
                try {
                    $this->customerCoordinates->save();
                } catch (\Exception $e) {
                    // todo::add Log
                }
            }
        }
    }
}
