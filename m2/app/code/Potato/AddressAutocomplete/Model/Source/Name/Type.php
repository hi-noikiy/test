<?php

namespace Potato\AddressAutocomplete\Model\Source\Name;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 */
class Type implements OptionSourceInterface
{
    const LONG_NAME_CODE = 'long_name';
    const SHORT_NAME_CODE = 'short_name';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::LONG_NAME_CODE => __("Long Name"),
            self::SHORT_NAME_CODE => __("Short Name")
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}
