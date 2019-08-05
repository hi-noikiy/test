<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Api;

/**
 * Interface CeligoModuleInfoInterface
 * @api
 */
interface CeligoModuleInfoInterface
{
    /**
     * Return the version number of Module.
     *
     * @return string version number.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get();
}
