<?php 

class Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract { 

    public function render(Varien_Object $row) { 
        $product = Mage::getModel('catalog/product')->load($row->getEntityId());
        $cats = $product->getCategoryIds();
        $allCats = '';
        foreach($cats as $key => $cat)
        {
            $_category = Mage::getModel('catalog/category')->load($cat);
            $allCats.= $_category->getName();
            if($key < count($cats)-1)
                $allCats.= ' , ';
        }
        return $allCats;
    }
     
 }