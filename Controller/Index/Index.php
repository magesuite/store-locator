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

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \MageSuite\StoreLocator\Helper\Configuration $configuration
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $result = $this->pageFactory->create();

        if (!$this->configuration->isEnabled()) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($baseUrl);

            return $resultRedirect;
        }

        if (!empty($this->configuration->getMetaTitle())) {
            $result->getConfig()->getTitle()->set($this->configuration->getMetaTitle());
        }

        if (!empty($this->configuration->getMetaDescription())) {
            $result->getConfig()->setDescription($this->configuration->getMetaDescription());
        }

        return $result;
    }
}
