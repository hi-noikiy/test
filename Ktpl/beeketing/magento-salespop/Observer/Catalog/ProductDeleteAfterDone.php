<?php
/**
 * Observer catalog_product_delete_after_done
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Catalog;

use Beeketing\MagentoCommon\Data\Webhook;

class ProductDeleteAfterDone implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * Constructor.
     */
    public function __construct(\Beeketing\SalesPop\Core\Api\App $app)
    {
        $this->app = $app;
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
        $product = $observer->getProduct();
        $this->app->sendRequestWebhook(Webhook::PRODUCT_DELETE, ['id' => $product->getId()]);
    }
}
