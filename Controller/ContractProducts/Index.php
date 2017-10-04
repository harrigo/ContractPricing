<?php


namespace Harrigo\ContractPricing\Controller\ContractProducts;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set("Contract Pricing");
        $resultPage->getConfig()->setDescription("your contract pricing");
        $resultPage->getConfig()->setKeywords("Contract, Pricing");
        return $this->resultPageFactory->create();
    }
}
