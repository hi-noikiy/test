<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Program\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractOptions implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Program
     */
    protected $program;

    protected $availableOptions;

    /**
     * IsActive constructor.
     * @param \Amasty\Affiliate\Model\Program $program
     */
    public function __construct(\Amasty\Affiliate\Model\Program $program)
    {
        $this->program = $program;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
