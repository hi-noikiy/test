<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    EM
 * @package     EM_Sliderwidget
 */


/**
 * Widget which displays tab system
 *
 * @category    EM
 * @package     EM_Sliderwidget
 * @author      Emthemes <emthemes.com>
 */
class EM_Sliderwidget_Block_Slide
extends Mage_Core_Block_Template
implements Mage_Widget_Block_Interface
{
	protected $idJs;
	public function _toHtml(){
		$this->setTemplate('em_sliderwidget/slide.phtml');
		return parent::_toHtml();
	}
	
	public function generateIdJs(){
		$this->idJs = "slide_".rand(0,100);
		return $this;
	}
	
	public function getIdJs(){
		if(!$this->idJs)
			$this->generateIdJs();
		return $this->idJs;	
	}
	
	public function getSelector(){
		$container = $this->getData('container');
		$containerArray = explode(',',$container);
		$selector = array();
		foreach($containerArray as $item){
			$selector[] = '#'.$this->getIdJs().' '.$item;
		}
		
		return implode(',',$selector);
	}
	
	public function getKeyBoard(){
		$keyboard = $this->getData('keyboard');
		if($keyboard)
			return 'true';
		return 'false';	
	}
	
	public function getContent(){
		$helper = Mage::helper('cms');
		$processor = $helper->getBlockTemplateProcessor();
		$content = '';
		$staticBlockId = $this->getData('static_block');
		if($staticBlockId)
			$content .= $processor->filter(Mage::getModel('cms/block')->load($staticBlockId)->getContent());
		$content .= $processor->filter($this->getWidgetInstance());
		return $content;	
	}
	
	/* 
		Get directives of widget 
		param : int $number -> order of widget instance
		return : {{widget type='...' ...}}
	*/
	public function getWidgetInstance(){
		
		$idInstance = $this->getData('instance');
		$directives = '';
		if($idInstance){
			$instance = Mage::getModel('widget/widget_instance')->load($idInstance);
			if(!count(array_intersect(array(0,Mage::app()->getStore()->getId()),$instance->getStoreIds())))
				return '';
			$params = $instance->getWidgetParameters();
			$pageGroups = $instance->getData('page_groups');
			$handles = Mage::app()->getFrontController()->getAction()->getLayout()->getUpdate()->getHandles();
			if(is_array($pageGroups)){
				foreach($pageGroups as $page){
					if(in_array($page['layout_handle'],$handles)){
						$params['template'] = $page['page_template'];
						break;
					}	
				}
			}
			$directives = Mage::getSingleton('widget/widget')->getWidgetDeclaration($instance->getInstanceType(),$params);
		}		
		return $directives;
	}
}
