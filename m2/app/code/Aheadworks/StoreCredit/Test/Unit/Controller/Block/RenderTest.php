<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Controller\Block;

use Aheadworks\StoreCredit\Block\Product\View\Discount;
use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Toplink;
use Aheadworks\StoreCredit\Controller\Block\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\View;
use Magento\Framework\View\Layout;

/**
 * Test for \Aheadworks\StoreCredit\Controller\Block\Render
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Render
     */
    private $controller;

    /**
     * @var InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translateInlineMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->translateInlineMock = $this->getMockForAbstractClass(InlineInterface::class);
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'isAjax',
                'getRouteName',
                'getControllerName',
                'getActionName',
                'getRequestUri',
                'setRouteName',
                'setControllerName',
                'setActionName',
                'setRequestUri'
            ]
        );
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['appendBody']
        );
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLayout', 'getLayout'])
            ->getMock();
        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'view' => $this->viewMock
            ]
        );

        $this->controller = $objectManager->getObject(
            Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInlineMock
            ]
        );
    }

    /**
     * Testing of execute method, if is not ajax request
     */
    public function testExecuteIsNotAjax()
    {
        $resultRedirectMock = $this->createMock(ResultRedirect::class);
        $resultRedirectMock->expects($this->once())
            ->method('setRefererOrBaseUrl')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Testing of execute method, if is ajax request     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteIsAjax()
    {
        $blocks = ['aw_store_credit.product.view.discount'];
        $expected = ['aw_store_credit.product.view.discount' => 'html content'];
        $origRequest = [
            'route' => 'catalog',
            'controller' => 'category',
            'action' => 'view',
            'uri' => '/index.php/gear/bags.html'
        ];
        $currentRoute = 'aw_store_credit';
        $currentControllerName = 'block';
        $currentActionName = 'render';
        $currentRequestUri = '/index.php/aw_store_credit/block/render/id/4';

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['blocks', null, json_encode($blocks)],
                    ['originalRequest', null, json_encode($origRequest)]
                ]
            );
        $this->requestMock->expects($this->once())
            ->method('getRouteName')
            ->willReturn($currentRoute);
        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($currentControllerName);
        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($currentActionName);
        $this->requestMock->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($currentRequestUri);

        $this->requestMock->expects($this->at(6))
            ->method('setRouteName')
            ->with($origRequest['route'])
            ->willReturnSelf();
        $this->requestMock->expects($this->at(7))
            ->method('setControllerName')
            ->with($origRequest['controller'])
            ->willReturnSelf();
        $this->requestMock->expects($this->at(8))
            ->method('setActionName')
            ->with($origRequest['action'])
            ->willReturnSelf();
        $this->requestMock->expects($this->at(9))
            ->method('setRequestUri')
            ->with($origRequest['uri'])
            ->willReturnSelf();

        $this->requestMock->expects($this->at(11))
            ->method('setRouteName')
            ->with($currentRoute)
            ->willReturnSelf();
        $this->requestMock->expects($this->at(12))
            ->method('setControllerName')
            ->with($currentControllerName)
            ->willReturnSelf();
        $this->requestMock->expects($this->at(13))
            ->method('setActionName')
            ->with($currentActionName)
            ->willReturnSelf();
        $this->requestMock->expects($this->at(14))
            ->method('setRequestUri')
            ->with($currentRequestUri)
            ->willReturnSelf();

        $blockInstanceMock = $this->getMockBuilder(Discount::class)
            ->disableOriginalConstructor()
            ->setMethods(['toHtml', 'setNameInLayout'])
            ->getMock();
        $blockInstanceMock->expects($this->once())
            ->method('setNameInLayout')
            ->with($blocks[0] . '_0');
        $blockInstanceMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expected['aw_store_credit.product.view.discount']);
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Discount::class)
            ->willReturn($blockInstanceMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->controller->execute();
    }
}
