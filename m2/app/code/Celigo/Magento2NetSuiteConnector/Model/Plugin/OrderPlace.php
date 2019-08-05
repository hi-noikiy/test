<?php
/**
 * Created by PhpStorm.
 * User: Celigo Developer
 * Date: 8/1/2016
 * Time: 2:39 PM
 */

namespace Celigo\Magento2NetSuiteConnector\Model\Plugin;

use \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface;
use Celigo\Magento2NetSuiteConnector\Model\CeligoOrderRepository;
use Celigo\Magento2NetSuiteConnector\Model\CeligoSalesOrderFactory;
use Celigo\Magento2NetSuiteConnector\Model\CeligoSalesOrder;
use \Celigo\Magento2NetSuiteConnector\Logger\Logger;

class OrderPlace
{
    /** @var \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface */
    private $celigoOrderRepository;

    private $logger;

    private $celigoSalesOrderFactory;

    public function __construct(
        CeligoOrderRepositoryInterface $celigoOrderRepository,
        CeligoSalesOrderFactory $celigoSalesOrderFactory,
        Logger $logger
    ) {
        $this->celigoOrderRepository = $celigoOrderRepository;
        $this->logger = $logger;
        $this->celigoSalesOrderFactory = $celigoSalesOrderFactory;
    }

    public function verifyAndCreateCeligoInfo(
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->logger->addInfo("fetching order Id : ". $order->getEntityId());
        $orderId = $order->getEntityId();
        $celigoOrderSalesold = $this->celigoOrderRepository->get($orderId);

        if ($celigoOrderSalesold->getParentId() === null) {
            try {
                $celigoOrderSales = $this->celigoSalesOrderFactory->create();
                $celigoOrderSales->setData($celigoOrderSalesold::PARENT_ID, $orderId);
                $celigoOrderSales->setData($celigoOrderSalesold::IS_EXPORTED_TO_IO, 0);
                $celigoOrderSales->save();
            } catch (\Exception $e) {
                $this->logger->addError("Failed to add celigo info", $e);
            }
        }

        $this->logger->addInfo("saved order Id : ". $order->getEntityId());

        return $order;
    }
 
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        if ($resultOrder != null) {
            $this->verifyAndCreateCeligoInfo($resultOrder);
        } else {
            $this->logger->addinfo("failed to add Celigo info", "already exists");
        }

        return $resultOrder;
    }
}
