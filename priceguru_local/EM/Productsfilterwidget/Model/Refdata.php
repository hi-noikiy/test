<?php

class EM_Productsfilterwidget_Model_Refdata extends Mage_Core_Model_Abstract
{
    public function refeshData()
    {
		$dir	=	Mage::getBaseDir().'/var/cache/productsfilterwidget/';
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$query	=	$write->query("select * from productsfilterwidget");		
		while ($row = $query->fetch() ) {
		
			$filename	=	'conditions_'.$row['title'];			
			$check	=	is_file($dir.$filename.'.php');
			//echo '<pre>';print_r($check);exit;
			
			if($check){			
				$actionsArr	 =	unserialize(Mage::helper('core')->urlDecode($row['content']));			
				//echo '<pre>';print_r($actionsArr);die;
			
				if($actionsArr['conditions']){							
					$conditions=$actionsArr['conditions'];		
					$strAttribute=$this->getStrAttribute($conditions);
					$arrAttribute=$this->getArrAttribute($strAttribute);	
				}
				else	$arrAttribute=array();
				
				if(Mage::registry('arrAttribute'))	Mage::unregister('arrAttribute');			
				else	Mage::register('arrAttribute',$arrAttribute);
				
				$catalogRule = Mage::getModel('productsfilterwidget/rule');
				if (!empty($actionsArr) && is_array($actionsArr))
				{
					$catalogRule->getConditions()->loadArray($actionsArr);
				}
				
				$catarule=Mage::registry('catalogRule');
				if($catarule) Mage::unregister('catalogRule');	
				Mage::register('catalogRule',$catalogRule);
				
				$productIds=Mage::getModel('productsfilterwidget/productrule')->getMatchingProductIds();
				if(!$productIds) $productIds = 'empty';
				
				$lib_multicache	=	Mage::helper('productsfilterwidget/multicache');
				$lib_multicache->delete($filename);
				$lib_multicache->set($filename,$productIds);
			}			
		}
		return $this;
    }
	
	function getStrAttribute($conditions)
	{	
		foreach($conditions as $attribute)
		{
			if($attribute['attribute'])
			{				
					$this->_arr.=$attribute['attribute'].",";
			}
			if(isset($attribute['conditions']))
			{	
				$conditions=$attribute['conditions'];
				$this->getStrAttribute($conditions);
			}
		}
		return $this->_arr;
	}
	
	public function getArrAttribute($str)
	{		
		$arr=explode(',',$str,-1);
		$n=count($arr);
		$arr1=array();
		$arr1[]=$arr[0];
			for($i=1;$i<$n;$i++)
			{
				if($this->check($arr[$i],$arr1))
					$arr1[]=$arr[$i];
			}
		return $arr1;
	}
	
	public function check($x,$arr)
	{
		$n=count($arr);
		for($i=0;$i<$n;$i++){
			if ($arr[$i]==$x)
				return false;
		}
		return true;
	}
	
}