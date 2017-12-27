<?php
/**
 * Observer customer_delete_after
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Customer;

use Beeketing\MagentoCommon\Data\Webhook;

class DeleteAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;
    /**
     * @var \Beeketing\MagentoCommon\Manager\CustomerManager
     */
    private $customerManager;

    /**
     * Constructor.
     */
    public function __construct(
        \Beeketing\SalesPop\Core\Api\App $app,
        \Beeketing\MagentoCommon\Manager\CustomerManager $customerManager
    ) {
        $this->app = $app;
        $this->customerManager = $customerManager;
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
        $customer = $observer->getCustomer();
        $this->app->sendRequestWebhook(Webhook::CUSTOMER_DELETE, ['id' => $customer->getId()]);
    }
}
