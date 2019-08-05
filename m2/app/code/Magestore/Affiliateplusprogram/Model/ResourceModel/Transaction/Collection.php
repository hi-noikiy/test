<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Model\ResourceModel\Transaction;

/**
 * Class Collection
 * @package Magestore\Affiliateplusprogram\Model\ResourceModel\Program
 */
class Collection extends \Magestore\Affiliateplusprogram\Model\ResourceModel\AbstractCollection
{


    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\Transaction', 'Magestore\Affiliateplusprogram\Model\ResourceModel\Transaction');
    }
}
