<?php
namespace Ktpl\Customreport\Model\Renderer;
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
   protected $wholesaler;
 
    public function __construct(\Ktpl\Customreport\Model\Wholesaler $emp)
    {
        $this->wholesaler = $emp;
    }
 
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
        return [1 => __('Enabled'), 0 => __('Disabled')];
    }
}