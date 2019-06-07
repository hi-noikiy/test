<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method int getId()
 * @method Justselling_Configurator_Model_Optiongroup setId(int $value)
 * @method string getTitle()
 * @method Justselling_Configurator_Model_Optiongroup setTitle(string $value)
 * @method string getGroupImage()
 * @method Justselling_Configurator_Model_Optiongroup setGroupImage(string $value)
 * @method int getTemplateId()
 * @method Justselling_Configurator_Model_Optiongroup setTemplateId(int $value)
 * @method int getSortOrder()
 * @method Justselling_Configurator_Model_Optiongroup setSortOrder(int $value)
 */
class Justselling_Configurator_Model_Optiongroup extends Mage_Core_Model_Abstract
{
	/**
	 * 
	 * Template
	 * @var Justselling_Configurator_Model_Template
	 */
	protected $_template;
	
	public $children = array();
	
	public $values = array();
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/optiongroup');
	}
	
	/**
	 * 
	 * set Template
	 * @param Justselling_Configurator_Model_Template $template
	 */
	public function setTemplate(Justselling_Configurator_Model_Template $template)
	{
		$this->_template = $template;
		return $this;
	}
	
	/**
	 * 
	 * get Template
	 * @return Justselling_Configurator_Model_Template
	 */
	public function getTemplate()
	{
		return $this->_template;
	}
	
	public function saveTemplateGroups(array $groups)
	{		
		// Zend_Debug::dump($groups); exit;
		 		
		foreach($groups as $group) {			
			$groupModel = Mage::getModel("configurator/optiongroup")->load($group['id']);
			
			
			if( $groupModel->template_id != $this->getTemplate()->getId() ) {
				$groupModel = Mage::getModel("configurator/optiongroup");
			}
			
			if( $group['is_delete'] == "1" ) {
				
				$groupModel->delete();
				
			} else {				
				$groupModel->setTemplateId( $this->getTemplate()->getId() );
				$groupModel->setTitle( $group['title'] );
				$groupModel->setSortOrder( $group['sort_order'] );

				if (!empty($group['groupimage'])) {
					$targetFileName = 'group-image-' .$group['id'] .$group['groupimage'];
					$mediaFolder = Mage::getBaseDir('media');
					$targetFolder = 'configurator' .DS .$this->getTemplate()->getId();

					if (strpos($targetFileName,'configurator/') !== false) {
						$group_image = $group['groupimage'];
					}else{
						$group_image = $targetFolder .DS .$targetFileName;
					}

					try {
						$tempFolder = Mage::getBaseDir('media') . '/tmp/upload/admin';
						$tempFile = rtrim($tempFolder, '/') . '/' . $group['groupimage'];

						if (file_exists($tempFile)) {
							Mage::helper('configurator/upload')->createAllDirectoriesFromPath($targetFolder);

							$targetFile = rtrim($mediaFolder, '/') . '/' . $group_image;
							rename($tempFile, $targetFile);
							$groupModel->setGroupImage($group_image);
						}
					} catch (Exception $e) {
						$groupModel->setGroupImage(null);
					}
				} else {
					$groupModel->setGroupImage(null);
				}

				$result = $groupModel->save();
			}			
		}
	}
	
	public function saveTemplateGroup(array $group)
	{
		
	}
	
	public function getTemplateGroups($templateId)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('template_id',$templateId);		
		return $collection;
	}


	/**
	 * Checks the currently set image path reference and copies it to the related (new structure) target location. In
	 * case the location has been adjusted it is set in the image reference (data) and - maybe - persisted.<br/>
	 * This method may be called in case of import, or copy of an OptionGroup.
	 * @param $persist bool true if the adjusted image reference should be saved, false (default) if not
	 * @return bool true if at least one has been adjusted, false if not
	 */
	public function adjustImageReferences($persist=false) {
		$hasBeenAdjusted = false;
		$imageFields = array('group_image');
		foreach ($imageFields as $imgField) {
			if ($adjusted = Justselling_Configurator_Model_Export_Processor::adjustAndCopyImageLocation($this, $imgField)) {
				$hasBeenAdjusted = true;
			}
		}
		if ($hasBeenAdjusted && $persist) {
			$this->save();
		}
		return $hasBeenAdjusted;
	}

	/**
	 * Returns the image path (without image file name), relative from 'media' folder.
	 * @param bool $absolute
	 * @param string $field the field to calculate the location path from (i.e. 'option_image')
	 * @return bool|string
	 */
	public function calculateImagePath($absolute=false, $field='') {
		if (!$this->getTemplateId()) {
			Js_Log::log("Call of Optiongroup::calculateImagePath without valid template ID!", $this, Zend_Log::ERR);
			return false;
		}
		$prefix = $absolute ? Mage::getBaseDir('media').DS : '';
		return $prefix.'configurator'.DS.$this->getTemplateId();
	}
}