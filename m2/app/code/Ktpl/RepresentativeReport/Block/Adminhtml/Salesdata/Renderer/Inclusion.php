<?php

namespace Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Renderer;

class Inclusion extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    protected $total_qty;
    
    protected $griditem;
    
    /**
     * 
     * @param \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Grid $griditem
     */
    public function __construct(
        \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Grid $griditem    
    ) {
        $this->griditem = $griditem;
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $row
     * @return type
     */
    public function render(\Magento\Framework\DataObject $row) {   
        $qty = $row->getData('order_quantity');
        $this->total_qty = $this->getqty();
        $a = number_format(($qty*100)/$this->total_qty,2);      
       return $a.'%';
    }
    
    /**
     * 
     * @return type
     */
    public function getqty(){
        $coll =  $this->griditem->getitems(); 
        $tqty = 0;
        foreach($coll as $c){
            $tqty += $c->getData('order_quantity');
         }
         return $tqty;
    }

}