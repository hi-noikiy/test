<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;
use \Mockery as m;


/**
 * @covers \Mirasvit\Rma\Observer\RmaChangedObserver
 * @codingStandardsIgnoreFile
 */
class RmaChangedObserverTest2 extends PHPUsableTest
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $statusMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $rmaMailMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $rmaMock;

    public function tearDown()
    {
        m::close();
    }

    public function tests() {
        PHPUsableTest::$current_test = $this;


        describe('#onRmaStatusChange', function($test) {
            before(function($test) {
                $this->rmaMailMock = m::mock('\Mirasvit\Rma\Helper\Mail');
                $this->rmaMock = m::mock('\Mirasvit\Rma\Model\Rma');
                $this->statusMock = m::mock('\Mirasvit\Rma\Model\Status');
                $this->rmaMock->shouldReceive('getStatus')->andReturn($this->statusMock);
                $this->objectManager = new ObjectManager($this);

                $this->service = $this->objectManager->getObject(
                    '\Mirasvit\Rma\Observer\RmaChangedObserver',
                    [
                        'rmaMail' => $this->rmaMailMock
                    ]
                );
            });

            describe('with customer message', function($test) {
                before(function($test) {
                    $this->message = 'some message';
                    $this->rmaMock->shouldReceive('getUser')->andReturn(false);
                    $this->statusMock->shouldReceive('getCustomerMessage')->andReturn($this->message);
                    $this->statusMock->shouldReceive('getAdminMessage')->andReturn($this->message);
                    $this->statusMock->shouldReceive('getHistoryMessage')->andReturn($this->message);
                });

                it ('should send message to customer', function($test) {

                    $this->rmaMailMock->shouldReceive('sendNotificationCustomerEmail')->times(1)->andReturn($this->message);
                    $this->rmaMailMock->shouldReceive('sendNotificationAdminEmail')->times(1)->andReturn($this->message);
                    $this->rmaMailMock->shouldReceive('parseVariables')->times(1)->andReturn($this->message);
//                    $this->rmaMailMock->expects($this->once())->method('sendNotificationCustomerEmail')
//                        ->with($this->rmaMock, $this->message, true)
//                        ->will($this->returnValue($this->message));
                    $this->service->onRmaStatusChange($this->rmaMock);
                });
            });

//            describe('without customer message', function($test) {
//                before(function($test) {
//                    $this->statusMock->expects($this->any())->method('getCustomerMessage')
//                        ->will($this->returnValue(false));
//                });
//
//                it ('should not send message to customer', function($test) {
//                    $this->rmaMailMock->expects($this->once())->method('sendNotificationCustomerEmail')
//                        ->with($this->rmaMock, $this->message, true)
//                        ->will($this->returnValue($this->message));
//                    $this->service->onRmaStatusChange($this->rmaMock);
//                    $this->assertTrue(true);
//                });
//            });
        });
    }
}
