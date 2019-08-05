<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        $handlers = array_values($handlers);

        parent::__construct($name, $handlers, $processors);
    }
}
