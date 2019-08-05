<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Block\Customer;

use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;
use Aheadworks\StoreCredit\Model\Config;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Block\Customer\StoreCreditBalanceTest
 */
class StoreCreditBalanceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Config
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CurrentCustomer
     */
    private $currentCustomerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PriceHelper
     */
    private $priceHelperMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPointsBalanceTopLinkAtFrontend'])
            ->getMockForAbstractClass();

        $this->customerStoreCreditServiceMock = $this->getMockBuilder(
            CustomerStoreCreditManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCustomerStoreCreditBalance'
                ]
            )
            ->getMockForAbstractClass();

        $this->currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMockForAbstractClass();

        $this->priceHelperMock = $this->getMockBuilder(PriceHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['currency'])
            ->getMock();

        $data = [
            'context' => $contextMock,
            'config' => $this->configMock,
            'customerStoreCreditService' => $this->customerStoreCreditServiceMock,
            'currentCustomer' => $this->currentCustomerMock,
            'priceHelper' => $this->priceHelperMock,
        ];

        $this->object = $objectManager->getObject(StoreCreditBalance::class, $data);
    }

    /**
     * Test getCustomerStoreCreditBalanceFormatted method
     */
    public function testGetCustomerStoreCreditBalanceFormattedMethod()
    {
        $expectedValue = 88.00;
        $customerId = 3;

        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerStoreCreditServiceMock->expects($this->once())
            ->method('getCustomerStoreCreditBalance')
            ->willReturn($expectedValue);

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($expectedValue, true, false)
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->getCustomerStoreCreditBalanceFormatted());
    }
}
