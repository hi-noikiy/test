<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Block\Customer\StoreCreditBalance\Account;

use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\TransactionRepositoryInterface;
use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Account\Transaction;
use Aheadworks\StoreCredit\Model\Comment\CommentInterface;
use Aheadworks\StoreCredit\Model\Source\TransactionType;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Block\Html\Pager;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Aheadworks\StoreCredit\Model\Comment\CommentPoolInterface;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Block\Customer\StoreCreditBalance\Account\TransactionTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Transaction
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Context
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerSession
     */
    private $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionRepositoryInterface
     */
    private $transactionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommentRenderer
     */
    private $commentRendererMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SortOrderBuilder
     */
    private $sortOrderBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SortOrder
     */
    private $sortOrderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaInterface
     */
    private $searchCriteriaInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Pager
     */
    private $pagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LayoutInterface
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SearchResultsInterface
     */
    private $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PriceHelper
     */
    private $priceHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommentPoolInterface
     */
    private $commentPoolMock;

    /**
     * Settings
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['createBlock', 'getChildName', 'renderElement', 'setChild'])
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            ['layout' => $this->layoutMock]
        );

        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCurrentPage', 'setPageSize', 'addFilter', 'create'])
            ->getMock();

        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setField', 'setDescendingDirection', 'create'])
            ->getMock();

        $this->sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerMock = $this->getMockBuilder(Pager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentPage', 'getLimit', 'setSearchResults', 'create'])
            ->getMock();

        $this->searchCriteriaInterfaceMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->searchResultsMock = $this->getMockBuilder(SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->transactionRepositoryMock = $this->getMockBuilder(
            TransactionRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();

        $this->commentRendererMock = $this->getMockBuilder(CommentRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommentRenderer', 'toHtml'])
            ->getMock();

        $this->priceHelperMock = $this->getMockBuilder(PriceHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['currency'])
            ->getMock();

        $this->commentPoolMock = $this->getMockForAbstractClass(CommentPoolInterface::class);

        $data = [
            'context' => $this->contextMock,
            'customerSession' => $this->customerSessionMock,
            'transactionRepository' => $this->transactionRepositoryMock,
            'commentRenderer' => $this->commentRendererMock,
            'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            'sortOrderBuilder' => $this->sortOrderBuilderMock,
            'priceHelper' => $this->priceHelperMock,
            'commentPool' => $this->commentPoolMock
        ];

        $this->object = $objectManager->getObject(Transaction::class, $data);
    }

    /**
     * Test getTransactions method
     */
    public function testGetTransactions()
    {
        $expectedValue = [3, 4, 5];
        $customerId = 5;

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($expectedValue);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaInterfaceMock);

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDescendingDirection')
            ->willReturnSelf();

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sortOrderMock);

        $this->assertEquals($expectedValue, $this->object->getTransactions());
    }

    /**
     * Test renderComment method
     */
    public function testRenderCommentMethod()
    {
        $expectedValue = 'Comment';
        $transactionType = TransactionType::BALANCE_ADJUSTED_BY_ADMIN;

        $commentDefaultMock = $this->getMockForAbstractClass(CommentInterface::class);
        $commentDefaultMock->expects($this->once())
            ->method('renderComment')
            ->willReturn(null);
        $this->commentPoolMock->expects($this->once())
            ->method('get')
            ->with($transactionType)
            ->willReturn($commentDefaultMock);

        $transactionMock = $this->getMockForAbstractClass(TransactionInterface::class);
        $transactionMock->expects($this->once())
            ->method('getType')
            ->willReturn($transactionType);
        $transactionMock->expects($this->once())
            ->method('getCommentToCustomer')
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->renderComment($transactionMock));
    }

    /**
     * Test getPagerHtml method
     */
    public function testGetPagerHtmlMethod()
    {
        $expectedValue = '<div class="pager"></div>';
        $customerId = 5;

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaInterfaceMock);

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDescendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->sortOrderMock);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($this->searchResultsMock);

        $this->pagerMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(1);
        $this->pagerMock->expects($this->once())
            ->method('getLimit')
            ->willReturn(10);

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Pager::class, 'aw_sc_transaction.pager')
            ->willReturn($this->pagerMock);

        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturn('pager');

        $this->layoutMock->expects($this->once())
            ->method('renderElement')
            ->willReturn($expectedValue);

        $class = new \ReflectionClass(Transaction::class);
        $methodGetAvailableAmount = $class->getMethod('_prepareLayout');
        $methodGetAvailableAmount->setAccessible(true);
        $methodGetAvailableAmount->invoke($this->object);

        $this->assertEquals($expectedValue, $this->object->getPagerHtml());
    }

    /**
     * Test balanceFormat method
     */
    public function testBalanceFormat()
    {
        $balance = 10;
        $expectedValue = '$' . $balance;

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($balance)
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->object->balanceFormat($balance));
    }
}
