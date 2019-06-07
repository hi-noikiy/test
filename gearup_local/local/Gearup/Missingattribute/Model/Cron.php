<?php
class Gearup_Missingattribute_Model_Cron{	
    public $csvFile;
    public $flag = 1;
    public $catarr = array();
    public function Run(){
        try{
            $path = Mage::getBaseDir('var').'/export/missing-attribute/';
            $this->csvFile = Mage::getBaseDir()."/import/attributes_check.csv";
            $file_handle = fopen($this->csvFile, 'r')or die("Unable to open Csv file!");
            $flag = true;
            while (!feof($file_handle) ) {
                $attrSetName = '';
                $line_of_text = fgetcsv($file_handle, 1024);
                if($flag) { $flag = false; continue; }
                if($line_of_text[0] != ''){
                    $attrSetName = $line_of_text[0];
                }
                for($i = 1; $i < sizeof($line_of_text); $i++)
                {
                    $attribute = '';
                    if($line_of_text[$i] != ''){
                        $attribute = $line_of_text[$i];
                    }
                    if($attrSetName && $attribute){
                        $this->missingAttribute($attrSetName,$attribute);
                    }
                }    
            }
            Mage::helper('missingattribute')->removefile($path);
            echo "Missing attribute report created at ".Mage::getModel('core/date')->date('d-m-Y H:i:s');
        } catch(Exception $e){
            Mage::log($e->getMessage(), null, 'missing-attr.log');
        }
    }
    
    public function missingAttribute($attrSetName,$attribute){
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->load($attrSetName, 'attribute_set_name')
                ->getAttributeSetId();
        
        $filename = Mage::getBaseDir('var').'/export/missing-attribute/'.date('Y-m-d').'.txt';
        $parts = explode('/', $filename);
                $file = array_pop($parts);
                $dir = '';
        foreach($parts as $part) {
            if (! is_dir($dir .= "{$part}/")) mkdir($dir);
        }

        if($attributeSetId){
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToFilter('attribute_set_id',$attributeSetId)
                        ->addAttributeToSelect("*")
                        ->addUrlRewrite()
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));
            $summary = array();
            foreach($_productCollection as $product){
                if (trim($product->getData($attribute)) == '') {
                    $summary[$attribute][] = $product->getSku();
                }    
            }
            if($this->flag==1){
                $myfile = fopen($filename, "w") or die("Unable to open file!");
                fwrite($myfile,sprintf("Report Generated: %s \n",Mage::getModel('core/date')->date('d-m-Y H:i:s')));
                $this->flag = $this->flag + 1;
            } else {
                $myfile = fopen($filename, "a") or die("Unable to open file!");
            }    
            if(!in_array($attrSetName, $this->catarr)) {
                fwrite($myfile,sprintf("=========================================================================== \n\n\n"));
                fwrite($myfile,sprintf("Attribute set name : %s \n",$attrSetName));
                array_push($this->catarr, $attrSetName);
            }
            if(isset($summary[$attribute])){
                fwrite($myfile,sprintf("Attribute : %s \nitems: (%d) \nSKUs: %s \n\n",$attribute, count($summary[$attribute]), implode(', ', $summary[$attribute])));
            }    
            fclose($myfile);
        }
    }
    
    public function Runimage() {
        try {
            $path = Mage::getBaseDir('var').'/export/missing-images/';
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                            ->addAttributeToSelect("*")
                            ->addUrlRewrite()
                            ->addFieldToFilter('status', array('eq' => 1))
                            ->addFieldToFilter('discontinued_product', array('neq' => 1))
                    ;
            $_productCollection->getSelect()->joinLeft(array
                    ('_gallery_table' => $_productCollection->getTable('catalog/product_attribute_media_gallery')),
                    'e.entity_id = _gallery_table.entity_id',
                    array()
                    )->where('_gallery_table.value IS NULL');
            
            $_productCollection1 = Mage::getModel("catalog/product")->getCollection()
                            ->addAttributeToSelect("*")
                            ->addUrlRewrite()
                            ->addFieldToFilter('status', array('eq' => 1))
                            ->addFieldToFilter('discontinued_product', array('eq' => 1))
                    ;
            $_productCollection1->getSelect()->joinLeft(array
                    ('_gallery_table' => $_productCollection1->getTable('catalog/product_attribute_media_gallery')),
                    'e.entity_id = _gallery_table.entity_id',
                    array()
                    )->where('_gallery_table.value IS NULL');
            
            $summary = array();
            foreach($_productCollection as $product){
                $summary['missing_image'][] = $product->getSku();
            }
            foreach($_productCollection1 as $product){
                $summary['missing_dis_image'][] = $product->getSku();
            }
            if (count($summary['missing_image']) || count($summary['missing_dis_image'])) {
                $filename = $path.date('Y-m-d').'.txt';
                $parts = explode('/', $filename);
                    $file = array_pop($parts);
                    $dir = '';
                foreach($parts as $part) {
                    if (! is_dir($dir .= "{$part}/")) mkdir($dir);
                }
                $myfile = fopen($filename, "w") or die("Unable to open file!");
                fwrite($myfile,sprintf("Report Generated: %s \n",Mage::getModel('core/date')->date('d-m-Y H:i:s')));
                fwrite($myfile,sprintf("Product with missing images: %d, items: %s \n", count($summary['missing_image']), implode(', ', $summary['missing_image'])));
                $dotedLine = '=====================================================================';
                fwrite($myfile, $dotedLine);
                fwrite($myfile,sprintf("\n \nDiscontinued Product with missing images: %d, items: %s", count($summary['missing_dis_image']), implode(', ', $summary['missing_dis_image'])));
                fclose($myfile);
            }
            Mage::helper('missingattribute')->removefile($path);

        } catch(Exception $e){
            Mage::log($e->getMessage(), null, 'missing-img.log');
        }
    }
}