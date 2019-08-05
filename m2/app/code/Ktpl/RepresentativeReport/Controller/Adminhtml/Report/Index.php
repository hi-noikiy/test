<?php
namespace Ktpl\RepresentativeReport\Controller\Adminhtml\Report;


class Index extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport
{
    protected $_coreSession;
    
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,    
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_coreSession = $coreSession;
        parent::__construct($context, $fileFactory, $dateFilter, $timezone);
    }

    
    public function execute()
    {   
        if(isset($_POST['start_date'])){
            $this->_coreSession->start();
            $this->_coreSession->setMyCustomData($_POST);
        }
        $this->_initAction()->_setActiveMenu(
            'Ktpl_RepresentativeReport::repreport'
        )->_addBreadcrumb(
            __('Sales Representative Report'),
            __('Sales Representative Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('Sales Representative Report')
        );

        $this->_view->renderLayout(); 
    }
    
}
