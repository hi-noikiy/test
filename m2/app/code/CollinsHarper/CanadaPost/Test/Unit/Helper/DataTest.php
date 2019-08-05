<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Test\Unit\Helper;


use Magento\Framework\Xml\Security;
//use Collinsharper\MeasureUnit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Ups config helper
     *
     * @var \Magento\Ups\Helper\Config
     */
    protected $helper;


    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject('CollinsHarper\CanadaPost\Helper\Data');

    }


    // TODO run through a conversion for each
    public function testDateConversion()
    {
        $this->assertEquals(date(\CollinsHarper\CanadaPost\Model\Source\Date\Formats::FULL_FORMAT),
            $this->helper->formatDate(date('Y-m-d'), \CollinsHarper\CanadaPost\Model\Source\Date\Formats::FULL));

    }
}
