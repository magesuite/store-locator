<?php
namespace MageSuite\StoreLocator\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }


    public function execute()
    {
        return $this->pageFactory->create();
    }
}