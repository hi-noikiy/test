<?php
namespace Ktpl\Repaircenter\Model\Renderer;
class Wholesaler implements \Magento\Framework\Data\OptionSourceInterface
{
   protected $wholesaler;
   protected $_objectManager;
 
    public function __construct(\Ktpl\Customreport\Model\Wholesaler $emp)
            //\Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->wholesaler = $emp;
       // $this->_objectManager = $objectmanager;
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $wholesaler = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->getCollection();
         $data=array();
        // print_r($wholesaler->getData()); exit;
         foreach($wholesaler as $whole){
             $data[$whole->getWholesalerId()]=$whole->getName();
         }
        return $data;
    }
}