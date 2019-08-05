<?php
 
namespace ShippyPro\ShippyPro\Controller\Index;

use Magento\Framework\App\Action\Context;
 
class Help extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_helper;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \ShippyPro\ShippyPro\Helper\Data $helper)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;

        parent::__construct($context);
    }
 
    public function execute()
    {
        $output = "";
        
        $output .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';
        $output .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';
        $output .= '<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">';
        $output .= '<div style="max-width: 80%; margin: 20px auto; font-family: \'Poppins\'">';
		$output .= file_get_contents(__DIR__ . "/Templates/welcome.html");
        $output .= file_get_contents(__DIR__ . "/Templates/promo.html");
        $output .= '</div>';        

        $this->getResponse()->setBody($output);
    }
}