<?php
/**
 * Observer controller_action_catalog_product_save_entity_after
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Controller;

use Beeketing\MagentoCommon\Data\Webhook;

class CatalogProductSaveEntityAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * @var \Beeketing\MagentoCommon\Manager\ProductManager
     */
    private $productManager;

    /**
     * FrontSendResponseBefore constructor.
     *
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param \Beeketing\MagentoCommon\Manager\ProductManager $productManager
     */
    public function __construct(
        \Beeketing\SalesPop\Core\Api\App $app,
        \Beeketing\MagentoCommon\Manager\ProductManager $productManager
    ) {
        $this->app = $app;
        $this->productManager = $productManager;
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
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();
        if ($product) {
            $content = $this->productManager->formatProduct($product);
            $storeIds = $product->getStoreIds();
            $settingHelper = $this->app->getSettingHelper();
            foreach ($storeIds as $storeId) {
                // Set store scope
                $settingHelper->setStoreId($storeId);
                $this->app->sendRequestWebhook(Webhook::PRODUCT_UPDATE, $content);
            }
        }
    }
}
