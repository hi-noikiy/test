<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Moneris\Test\Unit\Model;

use Magento\Framework\Xml\Security;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Model
     *
     * @var \CollinsHarper\Moneris\Model\Transaction
     */
    protected $_model;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('CollinsHarper\Moneris\Model\Exception');
    }

    public function testClass()
    {
        $this->assertTrue(get_class($this->_model) == 'CollinsHarper\Moneris\Model\Exception');
    }
}