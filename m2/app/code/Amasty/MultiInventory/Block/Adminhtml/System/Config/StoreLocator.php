<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;

class StoreLocator extends Field
{
    /**
     * @var Manager
     */
    private $manager;

    public function __construct(
        Manager $manager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->manager = $manager;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        if ($this->manager->isEnabled('Amasty_Storelocator')) {
            $element->setValue(__('Installed'));
            $element->setHtmlId('amasty_is_instaled');
            $url = $this->getUrl('adminhtml/system_config/edit', ['section' => 'amlocator']);
            $element->setComment(__('Specify Store Locator settings properly See more details '
                . '<a href="%1" target="_blank">here</a>', $url
            ));
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('amasty_not_instaled');
            $element->setComment(__('Let customers quickly find the nearest offline stores with your products '
                . 'by displaying them on a handy Google map. See more details '
                . '<a href="https://amasty.com/store-locator-for-magento-2.html'
                . '?utm_source=extension&utm_medium=backend&utm_campaign=from_multiInventory_to_storelocator_m2" '
                . 'target="_blank">here.</a>'
            ));
        }

        return parent::render($element);
    }
}
