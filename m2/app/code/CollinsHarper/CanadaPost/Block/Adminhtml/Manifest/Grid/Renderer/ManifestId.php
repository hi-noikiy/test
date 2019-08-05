<?php
/**
 * Copyright � 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Block\Adminhtml\Manifest\Grid\Renderer;

/**
 * Collins Harper Manifest Id renderer
 */
class ManifestId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_authorization = $context->getAuthorization();
    }

    /**
    * @return CollinsHarper\Model\Manifest.php
    *
    */ 
    protected function getLoadedManifest()
    {
        return $this->_coreRegistry->registry('manifest');
    }


    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $returnLabel = __('Not in Manifest');
        if($this->getLoadedManifest() && $row->getManifestId() == $this->getLoadedManifest()->getEntityId()) {
            $returnLabel = __('In this Manifest');
        } else if($row->getManifestId()) {
            $returnLabel = __('In a Manifest (#%1)', $row->getManifestId());
        }
        return $returnLabel;
    }

}
