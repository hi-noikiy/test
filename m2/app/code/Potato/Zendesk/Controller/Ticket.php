<?php
namespace Potato\Zendesk\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action;
use Magento\Customer\Model\Session;

/**
 * Class Ticket
 */
abstract class Ticket extends Action\Action
{
    /** @var Session  */
    protected $customerSession;

    /**
     * Ticket constructor.
     * @param Action\Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Action\Context $context,
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
