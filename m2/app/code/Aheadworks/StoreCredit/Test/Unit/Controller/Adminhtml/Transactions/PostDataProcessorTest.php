<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Controller\Adminhtml\Transactions;

use Aheadworks\StoreCredit\Controller\Adminhtml\Transactions\PostDataProcessor;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Controller\Adminhtml\Transactions\PostDataProcessorTest
 */
class PostDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PostDataProcessor
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    private $filterManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StripTags
     */
    private $filterStripTagsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'aw_storecredit_custselect'])
            ->getMockForAbstractClass();

        $this->filterStripTagsMock = $this->getMockBuilder(StripTags::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $data = [
            'filterManager' => $this->filterManagerMock,
            'messageManager' => $this->messageManagerMock,
        ];

        $this->object = $objectManager->getObject(PostDataProcessor::class, $data);
    }

    /**
     * Test filter method with empty params
     */
    public function testFilterMethodEmptyParams()
    {
        $this->assertEmpty($this->object->filter([]));
    }

    /**
     * Test filter method with 'comment_to_customer' and 'comment_to_admin' params
     */
    public function testFilterMethodForCommentFields()
    {
        $testData = [
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
        ];

        $this->filterManagerMock->expects($this->exactly(2))
            ->method('get')
            ->with('stripTags')
            ->willReturn($this->filterStripTagsMock);

        $this->filterStripTagsMock->expects($this->exactly(2))
            ->method('filter')
            ->withConsecutive(
                ['comment to customer'],
                ['comment to admin']
            )
            ->willReturnOnConsecutiveCalls(
                'comment to customer',
                'comment to admin'
            );

        $this->assertEquals($testData, $this->object->filter($testData));
    }

    /**
     * Test customerSelectionFilter method
     */
    public function testCustomerSelectionFilterMethod()
    {
        $testData = [
            'customer_selection' => [1],
        ];

        $this->filterManagerMock->expects($this->once())
            ->method('aw_storecredit_custselect')
            ->with($testData)
            ->willReturn($testData);

        $this->assertEquals($testData, $this->object->customerSelectionFilter($testData));
    }
}
