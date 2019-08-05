<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel;
use \Magento\Framework\Model\AbstractModel;

class Withdrawal extends Transaction
{
    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        /** @var \Amasty\Affiliate\Model\Withdrawal $loadedObject */
        $loadedObject = $this->entityManager->load($object, $value);

        return $loadedObject;
    }
}
