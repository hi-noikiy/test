<?php
namespace Ktpl\Customreport\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ViewAction
 */
class Customername extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    protected $_objectManager;
    

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        
        $this->urlBuilder = $urlBuilder;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
   public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
               $name = $this->getData('name');
                $orderdata = $this->_objectManager->create('Magento\Sales\Model\Order')->load($item['real_order_id']);
                $comment_content = $item[$name];
                $history = $orderdata->getStatusHistoryCollection()->getFirstItem();
                if($item[$name]=='' )
                {
                    $comment_content  = $orderdata->getCustomerFirstname()." ".$orderdata->getCustomerLastname();
                }
                
            try {
                
                $item[$name] = $comment_content;
            } catch (NoSuchEntityException $e) {
                $item[$name] = __('-');
            }
            }
        }
        return $dataSource;
}

}
