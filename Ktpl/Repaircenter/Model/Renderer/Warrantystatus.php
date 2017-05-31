<?php
namespace Ktpl\Repaircenter\Model\Renderer;
class Warrantystatus implements \Magento\Framework\Data\OptionSourceInterface
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
        return [1 => __('Warranty Ok'), 0 => __('Warranty Void'), ];
    }
}