<?php
/**
 * Module uninstall
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Module app api
     *
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * Uninstall constructor.
     *
     * @param \Beeketing\SalesPop\Core\Api\App $app
     */
    public function __construct(\Beeketing\SalesPop\Core\Api\App $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Send webhook and remove settings
        $this->app->init();
        $this->app->uninstallApp();
    }
}
