<?php
/**
 * Observer customer_save_after
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Customer;

use Beeketing\MagentoCommon\Data\Webhook;

class SaveAfter implements \Magento\Framework\Event\ObserverInterface
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
     *
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param \Beeketing\MagentoCommon\Manager\CustomerManager $customerManager
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
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getCustomer();

        // Set store scope
        $storeId = $customer->getStoreId();
        $settingHelper = $this->app->getSettingHelper();
        $settingHelper->setStoreId($storeId);

        $content = $this->customerManager->formatCustomer($customer);
        $this->app->sendRequestWebhook(Webhook::CUSTOMER_UPDATE, $content);
    }
}
