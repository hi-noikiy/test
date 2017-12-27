<?php
/**
 * Observer catalog_category_change_products
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Catalog;

use Beeketing\MagentoCommon\Data\Webhook;

class CategoryChangeProducts implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;
    /**
     * @var \Beeketing\MagentoCommon\Manager\CollectionManager
     */
    private $collectionManager;

    /**
     * Constructor.
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param \Beeketing\MagentoCommon\Manager\CollectionManager $collectionManager
     */
    public function __construct(
        \Beeketing\SalesPop\Core\Api\App $app,
        \Beeketing\MagentoCommon\Manager\CollectionManager $collectionManager
    ) {
        $this->app = $app;
        $this->collectionManager = $collectionManager;
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
        /** @var \Magento\Catalog\Model\Category $collection */
        $collection = $observer->getCategory();

        // Set store scope
        $storeId = $collection->getStoreId();
        $settingHelper = $this->app->getSettingHelper();
        $settingHelper->setStoreId($storeId);

        $content = $this->collectionManager->formatCollection($collection);
        $this->app->sendRequestWebhook(Webhook::COLLECTION_UPDATE, $content);
    }
}
