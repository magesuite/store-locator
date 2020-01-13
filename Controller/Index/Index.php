<?php
namespace MageSuite\StoreLocator\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \MageSuite\StoreLocator\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MageSuite\StoreLocator\Helper\Configuration $configuration
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->configuration = $configuration;
    }


    public function execute()
    {
        $result = $this->pageFactory->create();

        if (!empty($this->configuration->getMetaTitle())) {
            $result->getConfig()->getTitle()->set($this->configuration->getMetaTitle());
        }

        if (!empty($this->configuration->getMetaDescription())) {
            $result->getConfig()->setDescription($this->configuration->getMetaDescription());
        }

        return $result;
    }
}
