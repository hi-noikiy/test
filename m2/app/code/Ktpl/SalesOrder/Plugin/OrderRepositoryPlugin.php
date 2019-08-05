<?php
namespace Ktpl\SalesOrder\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryPlugin
 */
class OrderRepositoryPlugin
{
    /**
     * Order feedback field name
     */
    const binno = 'binno';
    const shipping_notes = 'shipping_notes';
    const terms = 'terms';
    const po = 'po';
    const tax_code = 'tax_code';
    const order_type = 'order_type';
    const ship_date = 'ship_date';
    const samples = 'samples';
    const KTPL_BD = 'business_developement';
    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add "customer" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $binno = $order->getData(self::binno);
        $shipping_notes = $order->getData(self::shipping_notes);
        $terms = $order->getData(self::terms);
        $po = $order->getData(self::po);
        $tax_code = $order->getData(self::tax_code);
        $order_type = $order->getData(self::order_type);
        $ship_date = $order->getData(self::ship_date);
        $samples = $order->getData(self::samples);
        $ktplbd  = $order->getData(self::KTPL_BD);

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

        $extensionAttributes->setBinno($binno);
        $extensionAttributes->setShippingNotes($shipping_notes);
        $extensionAttributes->setTerms($terms);
        $extensionAttributes->setPo($po);
        $extensionAttributes->setTaxCode($tax_code);
        $extensionAttributes->setOrderType($order_type);
        $extensionAttributes->setShipDate($ship_date);
        $extensionAttributes->setSamples($samples);
        $extensionAttributes->setBusinessDevelopement($ktplbd);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Add "customer" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $binno          = $order->getData(self::binno);
            $shipping_notes = $order->getData(self::shipping_notes);
            $terms          = $order->getData(self::terms);
            $po             = $order->getData(self::po);
            $tax_code       = $order->getData(self::tax_code);
            $order_type     = $order->getData(self::order_type);
            $ship_date      = $order->getData(self::ship_date);
            $samples        = $order->getData(self::samples);
            $ktplbd         = $order->getData(self::KTPL_BD);

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

            $extensionAttributes->setBinno($binno);
            $extensionAttributes->setShippingNotes($shipping_notes);
            $extensionAttributes->setTerms($terms);
            $extensionAttributes->setPo($po);
            $extensionAttributes->setTaxCode($tax_code);
            $extensionAttributes->setOrderType($order_type);
            $extensionAttributes->setShipDate($ship_date);
            $extensionAttributes->setSamples($samples);
            $extensionAttributes->setBusinessDevelopement($ktplbd);

            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}