<?php

namespace Ktpl\SalesOrder\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Updateshipdate extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\Order $order,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->resultPageFactory = $resultPageFactory;
        $this->resource = $resource;
    }

    public function execute()
    {
        $responce = array(
            'status'    => 'fail',
            'message'   => __('Not Found')
        );
        try {
            $post       = $this->getRequest()->getPostValue();
            $shipdate   = $post['shipdate'];
            $orderId    = $post['orderId'];
            $order      = $this->order->load($orderId);
            $order->setShipDate($shipdate);
            $order->save();

            $this->resource->getConnection()->update(
                $this->resource->getTableName('sales_order_grid'),
                array("ship_date" => $shipdate),
                ['entity_id = ?' => $orderId]
            );

            $message    = __("Shipdate Saved Successfully");
            $responce = array(
                'status'    => 'success',
                'message'   => $message
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            $responce = array(
                'status'    => 'fail',
                'message'   => $message
            );
        }
        echo json_encode($responce);
    }


}
