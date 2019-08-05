<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Block;

use Aheadworks\StoreCredit\Block\Ajax;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\StoreCredit\Block\Ajax
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Ajax
     */
    private $block;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getRouteName', 'getControllerName', 'getActionName', 'getRequestUri']
        );
        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'layout' => $this->layoutMock,
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock
            ]
        );

        $this->block = $objectManager->getObject(
            Ajax::class,
            [
                'context' => $contextMock
            ]
        );
    }

    /**
     * Testing of getScriptOptions method
     */
    public function testGetScriptOptions()
    {
        $isSecure = false;
        $routeName = 'catalog';
        $controllerName = 'category';
        $actionName = 'view';
        $requestUri = '/index.php/gear/bags.html';
        $url = 'https://ecommerce.aheadworks.com/aw_store_credit/block/render/id/1369/';
        $expected = '{"url":"https:\/\/ecommerce.aheadworks.com\/aw_store_credit\/block\/render\/id\/1369\/",'
            . '"originalRequest":{"route":"catalog","controller":"category",'
            . '"action":"view","uri":"\/index.php\/gear\/bags.html"}}';

        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $this->requestMock->expects($this->once())
            ->method('getRouteName')
            ->willReturn($routeName);
        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controllerName);
        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);
        $this->requestMock->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($requestUri);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                'aw_store_credit/block/render/',
                [
                    '_current' => true,
                    '_secure' => $isSecure,
                ]
            )->willReturn($url);

        $this->assertEquals($expected, $this->block->getScriptOptions());
    }
}
