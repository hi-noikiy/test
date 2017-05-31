<?php
namespace Ktpl\Customreport\Model\Renderer;
class DeliveryStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
 
    public static function getOptionArray()
    {
        return [1 => __('Pending'), 2 => __('Cancel'),3 => __('Complete'), 4 => __('On Hold')];
    }
}