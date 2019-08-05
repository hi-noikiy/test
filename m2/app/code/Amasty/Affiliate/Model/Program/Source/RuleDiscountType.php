<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Program\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RuleDiscountType extends \Amasty\Affiliate\Model\Program\Source\AbstractOptions implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $this->availableOptions = $this->program->getAvailableDiscountTypes();

        $options = parent::toOptionArray();

        return $options;
    }
}
