<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class TermsConditions implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => 'Not Accepted'],
            ['value' => 1, 'label' => 'Accepted']
        ];
    }
}
