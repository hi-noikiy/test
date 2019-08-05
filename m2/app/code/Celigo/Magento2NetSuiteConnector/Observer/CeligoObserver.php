<?php
namespace Celigo\Magento2NetSuiteConnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Celigo\Magento2NetSuiteConnector\Logger\Logger;
use Magento\Framework\App\ObjectManager;

class CeligoObserver implements ObserverInterface
{
    private $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderPlace = ObjectManager::getInstance()
                ->get(\Celigo\Magento2NetSuiteConnector\Model\Plugin\OrderPlace::class);
        if (!$orderPlace) {
            $orderPlace = ObjectManager::getInstance()
                ->create(\Celigo\Magento2NetSuiteConnector\Model\Plugin\OrderPlace::class);
        }
        // getting orders means it will be executed for multishipping checkouts
        $orders = $observer->getData('orders');
        if ($orders) {
            foreach ($orders as $resultOrder) {
                if ($resultOrder != null) {
                    $orderPlace->verifyAndCreateCeligoInfo($resultOrder);
                } else {
                    $this->logger->addinfo("failed to add Celigo info", "already exists");
                }
            }
        }
    }
}
