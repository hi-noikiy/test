<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model\Service;

use Aheadworks\StoreCredit\Model\Service\TransactionService;
use Aheadworks\StoreCredit\Api\Data\TransactionInterfaceFactory;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\TransactionRepositoryInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Model\Source\TransactionType;
use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Model\Service\TransactionServiceTest
 */
class TransactionServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionService
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionRepositoryInterface
     */
    private $transactionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionInterface
     */
    private $transactionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    private $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreInterface
     */
    private $storeMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);

        $this->transactionRepositoryMock = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'create'])
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getWebsiteId'])
            ->getMockForAbstractClass();

        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getEmail',
                    'getFirstname',
                    'getLastname',
                ]
            )
            ->getMock();

        $data = [
            'transactionRepository' => $this->transactionRepositoryMock,
            'storeManager' => $this->storeManagerMock,
        ];

        $this->object = $objectManager->getObject(TransactionService::class, $data);
    }

    /**
     * Test createEmptyTransaction method
     */
    public function testCreateEmptyTransactionMethod()
    {
        $this->transactionRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->transactionMock);

        $this->assertEquals($this->transactionMock, $this->object->createEmptyTransaction());
    }

    /**
     * Test saveTransaction method
     */
    public function testSaveTransactionMethod()
    {
        $this->transactionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->transactionMock)
            ->willReturn(true);

        $this->assertTrue($this->object->saveTransaction($this->transactionMock));
    }

    /**
     * Test saveTransaction method, throw exception
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable save transaction
     */
    public function testSaveTransactionMethodException()
    {
        $this->transactionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->transactionMock)
            ->willThrowException(new \Exception('Unable save transaction'));

        $this->assertTrue($this->object->saveTransaction($this->transactionMock));
    }

    /**
     * Test createTransaction method
     */
    public function testCreateTransactionMethod()
    {
        $customerId = 4;
        $customerEmail = 'test-spend@test.com';
        $firstname = 'Fisrtname';
        $lastname = 'Lastname';
        $customerName = 'Fisrtname Lastname';

        $websiteId = 1;
        $type = TransactionType::BALANCE_ADJUSTED_BY_ADMIN;
        $balanceUpdateNotified = NotifiedStatus::NOT_SUBSCRIBED;
        $balance = 12;
        $commentToCustomer = 'comment_to_customer';
        $commentToAdmin = 'comment_to_admin';

        $this->expectedCustomerMock(
            $customerId,
            $customerEmail,
            $firstname,
            $lastname
        );

        $this->transactionRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->transactionMock);

        $this->expectedTransactionMock(
            $customerId,
            $customerEmail,
            $customerName,
            $websiteId,
            $type,
            $balance,
            $commentToCustomer,
            $commentToAdmin,
            $balanceUpdateNotified
        );

        $this->expectedStoreMock($websiteId);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->transactionMock)
            ->willReturn(true);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->assertTrue($this->object->createTransaction(
            $this->customerMock,
            $balance,
            $type,
            $commentToCustomer,
            null,
            $commentToAdmin,
            null,
            $balanceUpdateNotified
        ));
    }

    /**
     * Private method for expected customer model
     *
     * @param int $customerId
     * @param string $customerEmail
     * @param string $firstname
     * @param string $lastname
     * @return \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    private function expectedCustomerMock($customerId, $customerEmail, $firstname, $lastname)
    {
        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $this->customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($firstname);
        $this->customerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($lastname);

        return $this->customerMock;
    }

    /**
     * Private method for expected transaction model
     *
     * @param int $customerId
     * @param string $customerEmail
     * @param string $customerName
     * @param int $websiteId
     * @param int $type
     * @param float $balance
     * @param string $commentToCustomer
     * @param string $commentToAdmin
     * @param string $balanceUpdateNotified
     * @return \PHPUnit_Framework_MockObject_MockObject|TransactionInterface
     */
    private function expectedTransactionMock(
        $customerId,
        $customerEmail,
        $customerName,
        $websiteId,
        $type,
        $balance,
        $commentToCustomer,
        $commentToAdmin,
        $balanceUpdateNotified
    ) {
        $this->transactionMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setCustomerEmail')
            ->with($customerEmail)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setCustomerName')
            ->with($customerName)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setType')
            ->with($type)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setBalance')
            ->with($balance)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setCommentToCustomer')
            ->with($commentToCustomer)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setCommentToAdmin')
            ->with($commentToAdmin)
            ->willReturnSelf();
        $this->transactionMock->expects($this->once())
            ->method('setBalanceUpdateNotified')
            ->with($balanceUpdateNotified)
            ->willReturnSelf();

        return $this->transactionMock;
    }

    /**
     * Private method for expected store model
     *
     * @param int $websiteId
     * @return \PHPUnit_Framework_MockObject_MockObject|StoreInterface
     */
    private function expectedStoreMock($websiteId)
    {
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        return $this->storeMock;
    }
}
