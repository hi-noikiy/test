<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller;

use Magento\Framework\Module\Manager;
use Amasty\Storelocator\Model\ResourceModel\Location;
use Amasty\Storelocator\Model\ConfigProvider;

class Router implements \Magento\Framework\App\RouterInterface
{
    const AJAX_CONTROLLER_PATH = 'amlocator/index/ajax';

    const SAVE_CONTROLLER_PATH = 'amlocator/location/savereview';
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Location
     */
    private $locationResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Location $locationResource,
        ConfigProvider $configProvider
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->locationResource = $locationResource;
        $this->configProvider = $configProvider;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
        $locatorPage = $this->configProvider->getUrl();

        $identifier = trim($this->request->getPathInfo(), '/');

        $request->setRouteName('amlocator');
        if ($identifier === self::SAVE_CONTROLLER_PATH) {
            $this->request->setModuleName('amlocator')->setControllerName('location')->setActionName('savereview');

            return $this->actionFactory->create(\Amasty\Storelocator\Controller\Location\SaveReview::class);
        }

        if (strpos($identifier, self::AJAX_CONTROLLER_PATH) !== false) {
            $this->request->setModuleName('amlocator')->setControllerName('index')->setActionName('ajax');

            return $this->actionFactory->create(\Amasty\Storelocator\Controller\Index\Ajax::class);
        }

        $identifier = current(explode("/", $identifier));

        if ($identifier == $locatorPage) {
            if ($this->getUrlKey()) {
                if ($locationId = $this->matchLocationUrl($this->getUrlKey())) {
                    $this->request->setModuleName('amlocator')->setControllerName('location')->setActionName('view');
                    $this->request->setParam('id', $locationId);
                    $this->request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                    $this->request->setDispatched(true);

                    return $this->actionFactory->create(\Amasty\Storelocator\Controller\Location\View::class);
                } else {
                    return null;
                }
            }
            $this->request->setDispatched(true);
            $this->request->setModuleName('amlocator')->setControllerName('index')->setActionName('index');
            $this->request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(\Amasty\Storelocator\Controller\Index\Index::class);
        } else {
            return null;
        }

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    /**
     * @return string
     */
    private function getUrlKey()
    {
        return urldecode(trim(
            str_replace($this->configProvider->getUrl(), '', $this->request->getPathInfo()),
            '/'
        ));
    }

    /**
     * @param string $urlKey
     *
     * @return bool
     */
    private function matchLocationUrl($urlKey)
    {
        return $this->locationResource->matchLocationUrl($urlKey);
    }
}
