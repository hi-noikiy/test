<?php
namespace Ktpl\Repaircenter\Model\Renderer;
class Status implements \Magento\Framework\Data\OptionSourceInterface
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
        return [1 => __('Pending'), 2 => __('Complete'), 3 => __('SAV Home')];
    }
}