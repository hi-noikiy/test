<?php
namespace Ktpl\CustomizeOrderApi\Model\ResourceModel;

class SalesrepRepository implements \Ktpl\CustomizeOrderApi\Api\SalesrepRepositoryInterface
{
    private $salesrepFactory;
    private $orderRepositoryInterface;
    private $productRepositoryInterface;
    private $scopeConfig;
    private $salesrepHelper;

    public function __construct(
        \Ktpl\CustomizeOrderApi\Model\SalesrepFactory $salesrepFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cminds\Salesrep\Helper\Data $salesrepHelper
    ) {
        $this->salesrepFactory = $salesrepFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->scopeConfig = $scopeConfig;
        $this->salesrepHelper = $salesrepHelper;
    }

    public function getByOrderId($order_id)
    {
        $salesrepData = $this->salesrepFactory->create();
        // $salesrepData->addAttributeToFilter('order_id', $order_id);
        $salesrepData->load($order_id, 'order_id');
        return $salesrepData;
    }

    public function save(
        \Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface $salesrepInterface
    ) {
        $salesrepData = $this->salesrepFactory->create();
        if ($salesrepInterface->getSalesrepId()) {
            $salesrepData->load($salesrepInterface->getSalesrepId());
        }
        $salesrepData->setData($salesrepInterface->getData());
        $salesrepData->save();

        return $salesrepData;
    }

    public function getRepCommissionEarned($order_id, $rep_commission)
    {
        $order = $this->orderRepositoryInterface->get($order_id);
        $commission_earned = 0;
        foreach ($order->getItems() as $order_item) {
            $ordered_qty = $order_item->getQtyOrdered();
            $price = $order_item->getPrice();
            $discount = $order_item->getBaseDiscountAmount();
            $product_id = $order_item->getProductId();

            $product = $this->productRepositoryInterface->getById($product_id);

            $commission = $product->getSalesrepRepCommissionRate();

            if ($commission === null) {
                if ($rep_commission !== null) {
                    $commission = $rep_commission;
                } else {
                    $commission = $this->salesrepHelper
                        ->getConfigDefaultSalesrepComm();
                }
            }
            $commission_earned += ($price * $ordered_qty - $discount) * ($commission / 100);
        }
        return floatval(round($commission_earned, 2));
    }

    public function getManagerCommissionEarned(
        $order_id,
        $manager_commission_rate,
        $salesrep_commission
    ) {
        $order = $this->orderRepositoryInterface->get($order_id);
        $orderSubtotal = $order->getBaseSubtotal();

        $manager_commission = 0;

        if ($manager_commission_rate !== null) {
            $manager_commission_rate = $this->salesrepHelper
                ->getConfigDefaultManagerComm();
        }

        if (( (int)$manager_commission_rate / 100) > 0) {
            $manager_commission_based_on = $this->salesrepHelper
                ->getConfigManagerCommBasedOn();

            if ($manager_commission_based_on == 1) {
                $manager_commission = round(
                    $orderSubtotal * ($manager_commission_rate / 100),
                    2
                );
            } else {
                if ($salesrep_commission && $salesrep_commission != '') {
                    $manager_commission = round(
                        $salesrep_commission * ($manager_commission_rate / 100),
                        2
                    );
                }
            }
        }
        return floatval($manager_commission);
    }

    public function get()
    {
        $salesrepData = $this->salesrepFactory->create();

        return $salesrepData;
    }
}
