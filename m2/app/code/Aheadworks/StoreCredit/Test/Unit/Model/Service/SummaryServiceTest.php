<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model\Service;

use Aheadworks\StoreCredit\Model\Service\SummaryService;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Api\SummaryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Model\Config;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Model\Service\SummaryServiceTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SummaryServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SummaryService
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionInterface
     */
    private $transactionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SummaryRepositoryInterface
     */
    private $summaryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SummaryInterface
     */
    private $summaryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Config
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreInterface
     */
    private $storeMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->transactionMock = $this->getMockBuilder(TransactionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setCustomerId',
                    'setCustomerEmail',
                    'setCustomerName',
                    'setWebsiteId',
                    'setBalance',
                    'setCommentToCustomer',
                    'setCommentToAdmin',
                    'getTransactionId',
                    'getCustomerId',
                    'getBalance'
                ]
            )
            ->getMockForAbstractClass();

        $this->summaryRepositoryMock = $this->getMockBuilder(SummaryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'get', 'create'])
            ->getMockForAbstractClass();

        $this->summaryMock = $this->getMockBuilder(SummaryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getSummaryId',
                    'setWebsiteId',
                    'setCustomerId',
                    'setStoreCredit',
                    'getStoreCredit',
                    'setEarn',
                    'getEarn',
                    'setSpend',
                    'getSpend',
                    'setBalanceUpdateNotificationStatus',
                    'getBalanceUpdateNotificationStatus'
                ]
            )
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getWebsiteId'])
            ->getMockForAbstractClass();
        $this->configMock =  $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSubscribeCustomersToNotificationsByDefault'])
            ->getMockForAbstractClass();

        $data = [
            'storeCreditSummaryRepository' => $this->summaryRepositoryMock,
            'storeManager' => $this->storeManagerMock,
            'config' => $this->configMock
        ];

        $this->object = $objectManager->getObject(SummaryService::class, $data);
    }

    /**
     * test getStoreCreditSummary method
     */
    public function testGetStoreCreditSummaryMethod()
    {
        $customerId = 6;

        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($this->summaryMock);

        $class = new \ReflectionClass(SummaryService::class);
        $method = $class->getMethod('getStoreCreditSummary');
        $method->setAccessible(true);

        $this->assertEquals(
            $this->summaryMock,
            $method->invokeArgs($this->object, ['customerId' => $customerId])
        );
    }

    /**
     * test getStoreCreditSummary method, not found model
     */
    public function testGetStoreCreditSummaryMethodNotFoundModel()
    {
        $customerId = 6;

        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $this->summaryRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->summaryMock);

        $class = new \ReflectionClass(SummaryService::class);
        $method = $class->getMethod('getStoreCreditSummary');
        $method->setAccessible(true);

        $this->assertEquals(
            $this->summaryMock,
            $method->invokeArgs($this->object, ['customerId' => $customerId])
        );
    }

    /**
     * Test getCustomerStoreCreditBalance method
     */
    public function testGetCustomerStoreCreditBalanceMethod()
    {
        $customerId = 6;
        $storeCredit = 10;

        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($this->summaryMock);

        $this->summaryMock->expects($this->once())
            ->method('getBalance')
            ->willReturn($storeCredit);

        $this->assertEquals($storeCredit, $this->object->getCustomerStoreCreditBalance($customerId));
    }

    /**
     * Test getCustomerBalanceUpdateNotificationStatus method
     */
    public function testGetCustomerBalanceUpdateNotificationStatusMethod()
    {
        $customerId = 6;
        $balanceUpdateNotification = SubscribeStatus::SUBSCRIBED;

        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($this->summaryMock);

        $this->configMock->expects($this->once())
            ->method('isSubscribeCustomersToNotificationsByDefault')
            ->willReturn(SubscribeStatus::SUBSCRIBED);

        $this->summaryMock->expects($this->exactly(2))
            ->method('getBalanceUpdateNotificationStatus')
            ->willReturn($balanceUpdateNotification);

        $this->assertEquals(
            $balanceUpdateNotification,
            $this->object->getCustomerBalanceUpdateNotificationStatus($customerId)
        );
    }

    /**
     * Test addSummaryToCustomer method for new customer
     */
    public function testAddSummaryToCustomerMethodNewCustomer()
    {
        $customerId = 5;
        $websiteId = 1;
        $balance = 10;
        $newStoreCredit = 10;

        $this->transactionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->transactionMock->expects($this->once())
            ->method('getBalance')
            ->willReturn($balance);

        $this->transactionMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->configMock->expects($this->once())
            ->method('isSubscribeCustomersToNotificationsByDefault')
            ->willReturn(SubscribeStatus::SUBSCRIBED);

        $this->expectedSetupSummaryNewCustomer($customerId, $websiteId, $newStoreCredit);
        $this->expectedSaveSummary();

        $this->assertTrue($this->object->addSummaryToCustomer($this->transactionMock));
    }

    /**
     * Test addSummaryToCustomer method for exists customer
     */
    public function testAddSummaryToCustomerMethodExistsCustomer()
    {
        $customerId = 5;
        $summaryId = 1;
        $balance = 10;
        $oldStoreCredit = 5;
        $newStoreCredit = 15;

        $this->transactionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->transactionMock->expects($this->once())
            ->method('getBalance')
            ->willReturn($balance);

        $this->expectedSetupSummaryExistsCustomer($customerId, $summaryId, $oldStoreCredit, $newStoreCredit);
        $this->expectedSaveSummary();

        $this->assertTrue($this->object->addSummaryToCustomer($this->transactionMock));
    }

    /**
     * Test addSummaryToCustomer method for exists customer throw exception
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable save data
     */
    public function testAddSummaryToCustomerMethodExistsCustomerThrowException()
    {
        $customerId = 5;
        $summaryId = 1;
        $balance = 10;
        $oldStoreCredit = 5;
        $newStoreCredit = 15;

        $this->transactionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->transactionMock->expects($this->once())
            ->method('getBalance')
            ->willReturn($balance);

        $this->expectedSetupSummaryExistsCustomer($customerId, $summaryId, $oldStoreCredit, $newStoreCredit);
        $this->expectedSaveSummaryThrowException();

        $this->object->addSummaryToCustomer($this->transactionMock);
    }

    /**
     * Expected setupSummary method for new customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @param int $newStoreCredit
     * @return void
     */
    private function expectedSetupSummaryNewCustomer($customerId, $websiteId, $newStoreCredit = 0)
    {
        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $this->summaryRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->summaryMock);

        $this->summaryMock->expects($this->once())
            ->method('getSummaryId')
            ->willReturn(null);

        $this->summaryMock->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId)
            ->willReturnSelf();

        $this->summaryMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();

        $this->summaryMock->expects($this->exactly(2))
            ->method('getBalance')
            ->willReturn(0);

        $this->summaryMock->expects($this->once())
            ->method('setBalance')
            ->with($newStoreCredit)
            ->willReturnSelf();

        $this->summaryMock->expects($this->once())
            ->method('getEarn')
            ->willReturn(0);
    }

    /**
     * Expected setupSummary method for exists customer
     *
     * @param int $customerId
     * @param int $summaryId
     * @param int $oldStoreCredit
     * @param int $newStoreCredit
     */
    private function expectedSetupSummaryExistsCustomer($customerId, $summaryId, $oldStoreCredit, $newStoreCredit)
    {
        $this->summaryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($this->summaryMock);

        $this->summaryMock->expects($this->once())
            ->method('getSummaryId')
            ->willReturn($summaryId);

        $this->summaryMock->expects($this->exactly(2))
            ->method('getBalance')
            ->willReturn($oldStoreCredit);

        $this->summaryMock->expects($this->once())
            ->method('setBalance')
            ->with($newStoreCredit)
            ->willReturnSelf();
    }

    /**
     * Expected saveSummary method
     */
    private function expectedSaveSummary()
    {
        $this->summaryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->summaryMock)
            ->willReturn(true);
    }

    /**
     * Expected saveSummary method
     */
    private function expectedSaveSummaryThrowException()
    {
        $this->summaryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->summaryMock)
            ->willThrowException(new \Exception('Unable save data'));
    }
}
