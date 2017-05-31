<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Repaircenter\Block\Adminhtml;

use Magento\Sales\Model\Order\Item;

/**
 * Adminhtml sales order item renderer
 */ 
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
      protected $_messageHelper;

    /**
     * Checkout helper
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     */
    protected $_giftMessage = [];
    protected $request;
    protected $_backendUrl;
    protected $view;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Request\Http $request, 
        \Magento\Sales\Block\Adminhtml\Order\View $view,    
        \Magento\Backend\Model\UrlInterface $backendUrl,    
        array $data = []
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->_backendUrl = $backendUrl;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_messageHelper = $messageHelper;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry,$messageHelper, $checkoutHelper,$data);
    }

    
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                break;
            case 'status':
                $html = $item->getStatus();
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('original_price');
                break;
            case 'tax-amount':
                $html = $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-percent':
                $html = $this->displayTaxPercent($item);
                break;
            case 'discont':
                $html = $this->displayPriceAttribute('discount_amount');
                break;
            case 'repair':
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $pickup = $objectManager->create('\Ktpl\Repaircenter\Model\Repairtocenter')->getCollection()
                           ->addFieldToFilter('product',array('like' => '%'.$item->getSku().'%'))
                           ->addFieldToFilter('increment_id',$this->getOrder()->getIncrementId())
                           ->getFirstItem();
                  if(!$pickup->getRepairId()) {
                $message ='Are you sure you want to do this?';
                $url = $this->_backendUrl->getUrl('repaircenter/repairtocenter/create',['order_id'=>$this->request->getParam('order_id'),'sku'=> base64_encode($item->getSku())]);
                $html= $addButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setData(array(
                    'label'     => 'Repair',
                    'onclick'   => "confirmSetLocation('{$message}', '{$url}')",
                    'class'   => 'task'
                ))->toHtml();
                  } else{$html = __('Already Exist');}
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }



}
