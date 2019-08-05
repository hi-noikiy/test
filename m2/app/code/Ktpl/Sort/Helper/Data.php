<?php
namespace Ktpl\Sort\Helper;

use Ktpl\Sort\Block\Toolbar;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_block ;

    public function __construct(\Ktpl\Sort\Block\Toolbar $block)
        {        
            $this->_block = $block;
        }

    public function getCurrentAttributeCode(){    	
        return $this->_block->getCurrentOrder(); 
    }


}
?>