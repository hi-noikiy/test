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
 * @package   mirasvit/module-report
 * @version   1.3.16
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Type;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class Qty extends Number implements TypeInterface
{
    public function getType()
    {
        return self::TYPE_QTY;
    }

    public function getAggregators()
    {
        return ['none'];
    }

    public function getJsType()
    {
        return self::JS_TYPE_NUMBER;
    }
}