<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Block\Product\View;

use Aheadworks\StoreCredit\Block\Product\View\Discount;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceInfo;
use Magento\Tax\Pricing\Adjustment;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Block\Product\View\DiscountTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends \PHPUnit\Framework\TestCase
{
    /** @var  Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /**
     * @var  CustomerStoreCreditManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerStoreCreditService;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PriceHelper
     */
    private $priceHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Product
     */
    private $productMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Discount
     */
    private $object;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn(1);
        $this->context = $objectManager->getObject(
            Context::class,
            ['request' => $this->requestMock]
        );

        $this->customerStoreCreditService = $this->getMockBuilder(CustomerStoreCreditManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->priceHelperMock = $this->getMockBuilder(PriceHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['currency'])
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo'])
            ->getMock();

        $data = [
            'context' => $this->context,
            'customerStoreCreditService' => $this->customerStoreCreditService,
            'customerSession' => $this->customerSession,
            'productRepository' => $this->productRepositoryMock,
            'priceHelper' => $this->priceHelperMock
        ];

        $this->object = $objectManager->getObject(Discount::class, $data);
    }

    /**
     * Test template property
     */
    public function testTemplateProperty()
    {
        $class = new \ReflectionClass(Discount::class);
        $property = $class->getProperty('_template');
        $property->setAccessible(true);

        $this->assertEquals('Aheadworks_StoreCredit::product/view/discount.phtml', $property->getValue($this->object));
    }

    /**
     * Test getAvailableStoreCredit method
     */
    public function testGetAvailableStoreCredit()
    {
        $customerId = 3;
        $balance = 10;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerStoreCreditService->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->with($customerId)
            ->willReturn($balance);

        $this->assertEquals($balance, $this->object->getAvailableStoreCredit());
    }

    /**
     * Test getAvailableStoreCredit method
     */
    public function testGetAvailableStoreCreditMethodNullCustomerValue()
    {
        $customerId = null;
        $balance = 0;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->assertEquals($balance, $this->object->getAvailableStoreCredit());
    }

    /**
     * Test getAvailableAmount method
     */
    public function testGetAvailableAmount()
    {
        $customerId = 4;
        $balance = 12;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerStoreCreditService->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->with($customerId)
            ->willReturn($balance);

        $class = new \ReflectionClass(Discount::class);
        $methodGetAvailableAmount = $class->getMethod('getAvailableAmount');
        $methodGetAvailableAmount->setAccessible(true);

        $this->assertEquals($balance, $methodGetAvailableAmount->invoke($this->object));
    }

    /**
     * Test getAvailableAmount method
     */
    public function testGetAvailableAmountMethodNullCustomerValue()
    {
        $customerId = null;
        $amount = 0;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $class = new \ReflectionClass(Discount::class);
        $methodGetAvailableAmount = $class->getMethod('getAvailableAmount');
        $methodGetAvailableAmount->setAccessible(true);

        $this->assertEquals($amount, $methodGetAvailableAmount->invoke($this->object));
    }

    /**
     * Test getFormattedAvailableAmount method
     */
    public function testGetFormattedAvailableAmountMethod()
    {
        $expectedValue = '$12.00';
        $customerId = 4;
        $balance = 12;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerStoreCreditService->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->with($customerId)
            ->willReturn($balance);

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($balance, true, false)
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->getFormattedAvailableAmount());
    }

    /**
     * Test getPriceWithDiscount method
     */
    public function testGetPriceWithDiscountMethod()
    {
        $customerId = 4;
        $balance = 12;
        $productPrice = 25;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerStoreCreditService->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->with($customerId)
            ->willReturn($balance);

        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($productPrice);
        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);
        $priceInfoMock = $this->getMockBuilder(PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($priceMock);

        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->productMock);

        $this->assertEquals(13, $this->object->getPriceWithDiscount());
    }

    /**
     * Test getPriceWithDiscount method
     */
    public function testGetPriceWithDiscountMethodNullAmountValue()
    {
        $customerId = 4;
        $balance = 12;
        $productPrice = 25;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerStoreCreditService->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->with($customerId)
            ->willReturn($balance);

        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($productPrice);
        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);
        $priceInfoMock = $this->getMockBuilder(PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($priceMock);

        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->productMock);

        $this->assertEquals(13, $this->object->getPriceWithDiscount());
    }

    /**
     * Test getPriceWithDiscount method
     */
    public function testGetPriceWithDiscountMethodNullCustomerValue()
    {
        $customerId = null;
        $productPrice = 25;

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($productPrice);
        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);
        $priceInfoMock = $this->getMockBuilder(PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($priceMock);

        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->productMock);

        $this->assertEquals(25, $this->object->getPriceWithDiscount());
    }

    /**
     * Test getFormattedPriceWithDiscount method
     */
    public function testGetFormattedPriceWithDiscount()
    {
        $customerId = null;
        $productPrice = 25;
        $expectedValue = '$25.00';

        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($productPrice);
        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);
        $priceInfoMock = $this->getMockBuilder(PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($priceMock);

        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->productMock);

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($productPrice)
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->getFormattedPriceWithDiscount());
    }
}
