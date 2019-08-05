<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Controller\Adminhtml\Transactions;

use Aheadworks\StoreCredit\Controller\Adminhtml\Transactions\Save;
use Aheadworks\StoreCredit\Controller\Adminhtml\Transactions\PostDataProcessor;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Controller\Adminhtml\Transactions\SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Save
     */
    private $object;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestHttp
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MessageManagerInterface
     */
    private $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RedirectFactory
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Redirect
     */
    private $resultRedirectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PostDataProcessor
     */
    private $dataProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DataPersistorInterface
     */
    private $dataPersistorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionInterface
     */
    private $transactionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostValue'])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(MessageManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $this->dataProcessorMock = $this->getMockBuilder(PostDataProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter', 'customerSelectionFilter'])
            ->getMock();

        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['set', 'clear'])
            ->getMockForAbstractClass();

        $this->transactionMock = $this->getMockBuilder(TransactionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->customerStoreCreditServiceMock = $this->getMockForAbstractClass(
            CustomerStoreCreditManagementInterface::class
        );

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );

        $data = [
            'context' => $this->context,
            'dataProcessor' => $this->dataProcessorMock,
            'dataPersistor' => $this->dataPersistorMock,
            'customerStoreCreditService' => $this->customerStoreCreditServiceMock

        ];

        $this->object = $objectManager->getObject(Save::class, $data);
    }

    /**
     * Test execute method for null POST
     */
    public function testExecuteMethodEmptyPost()
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->object->execute();
    }

    /**
     * Test execute method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteMethod()
    {
        $postData = [
            'website_id' => 1,
            'balance' => 50,
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
            'customer_selections' => [
                [
                    'customer_id' => 1,
                    'customer_name' => 'Veronica Costello',
                    'customer_email' => 'roni_cost@example.com',
                    'website_id' => 1
                ],
            ]
        ];
        $filterPostData = [
            'website_id' => 1,
            'balance' => 50,
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
            'customer_selections' => [
                [
                    'customer_id' => 1,
                    'customer_name' => 'Veronica Costello',
                    'customer_email' => 'roni_cost@example.com',
                    'website_id' => 1
                ],
            ]
        ];

        $transactionData = [
                'website_id' => 1,
                'balance' => 50,
                'comment_to_customer' => 'comment to customer',
                'comment_to_admin' => 'comment to admin',
                'customer_id' => 1,
                'customer_name' => 'Veronica Costello',
                'customer_email' => 'roni_cost@example.com',
        ];

        $customerSelectionData = [
            $transactionData
        ];

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('transaction', $postData)
            ->willReturnSelf();

        $this->dataProcessorMock->expects($this->once())
            ->method('filter')
            ->with($postData)
            ->willReturn($filterPostData);

        $this->dataProcessorMock->expects($this->once())
            ->method('customerSelectionFilter')
            ->with($filterPostData)
            ->willReturn($customerSelectionData);

        $this->customerStoreCreditServiceMock->expects($this->once())
            ->method('resetCustomer')
            ->willReturnSelf();
        $this->customerStoreCreditServiceMock->expects($this->once())
            ->method('saveAdminTransaction')
            ->with($transactionData)
            ->willReturnSelf();

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You saved the transactions.')
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->object->execute();
    }
}
