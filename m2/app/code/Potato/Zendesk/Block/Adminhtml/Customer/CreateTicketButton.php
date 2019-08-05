<?php
namespace Potato\Zendesk\Block\Adminhtml\Customer;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Potato\Zendesk\Model\Config;

/**
 * Class CreateTicketButton
 */
class CreateTicketButton implements ButtonProviderInterface
{
    /** @var Config  */
    protected $config;

    /**
     * CreateTicketButton constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }
    
    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->config->isSupportCustomerSection()) {
            $buttonData = [
                'label'   => __('Create Ticket'),
                'class'   => 'zendesk-create-ticket',
                'on_click' => '',
            ];
        }
        return $buttonData;
    }
}
