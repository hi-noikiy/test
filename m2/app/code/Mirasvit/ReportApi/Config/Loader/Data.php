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



namespace Mirasvit\ReportApi\Config\Loader;

use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\Config\CacheInterface;

class Data extends ConfigData
{
    public function __construct(
        Reader $reader,
        CacheInterface $cache
    ) {
        parent::__construct($reader, $cache, 'mst_report');
    }
}