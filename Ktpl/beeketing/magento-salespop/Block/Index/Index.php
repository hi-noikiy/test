<?php
/**
 * Frontend index
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Block\Index;

use Magento\Framework\View\Element\Template;

class Index extends Template
{
    /**
     * Module app api
     *
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Beeketing\SalesPop\Core\Api\App $app,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->app = $app;
    }

    /**
     * Get js app data
     * @return string
     */
    public function getSnippet()
    {
        $this->app->init();
        return $this->app->getSnippet();
    }
}
