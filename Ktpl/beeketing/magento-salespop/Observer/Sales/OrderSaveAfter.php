<?php
/**
 * Observer sales_order_save_after
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Sales;

use Beeketing\MagentoCommon\Data\Webhook;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;
    /**
     * @var \Beeketing\MagentoCommon\Manager\OrderManager
     */
    private $orderManager;

    /**
     * Constructor.
     *
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param \Beeketing\MagentoCommon\Manager\OrderManager $orderManager
     */
    public function __construct(
        \Beeketing\SalesPop\Core\Api\App $app,
        \Beeketing\MagentoCommon\Manager\OrderManager $orderManager
    ) {
        $this->app = $app;
        $this->orderManager = $orderManager;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->app->init();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        // Set store scope
        $storeId = $order->getStoreId();
        $settingHelper = $this->app->getSettingHelper();
        $settingHelper->setStoreId($storeId);

        $content = $this->orderManager->formatOrder($order);
        $this->app->sendRequestWebhook(Webhook::ORDER_UPDATE, $content);
    }
}
