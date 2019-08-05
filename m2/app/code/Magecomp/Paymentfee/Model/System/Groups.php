<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Model\System;

use Magento\Customer\Model\Customer\Source\GroupFactory;
use Magento\Customer\Model\Group;

class Groups {
    /**
     * @var GroupFactory
     */
    protected $_customerGroupFactory;

    public function __construct(GroupFactory $customerGroupFactory)
    {
        $this->_customerGroupFactory = $customerGroupFactory;

    }

    public function toOptionArray() 
	{
        $returnArray = $this->_customerGroupFactory->create()->toOptionArray();
		array_shift($returnArray);
        return $returnArray;
    }
}