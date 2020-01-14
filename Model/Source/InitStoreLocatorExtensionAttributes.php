<?php

namespace MageSuite\StoreLocator\Model\Source;

class InitStoreLocatorExtensionAttributes
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(\Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    public function execute(\Magento\InventoryApi\Api\Data\SourceInterface $source): void
    {
        if (!$source instanceof \Magento\Framework\DataObject) {
            return;
        }

        $url = $source->getData('url');

        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(\Magento\InventoryApi\Api\Data\SourceInterface::class);
            /** @noinspection PhpParamsInspection */
            $source->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setUrl($url);
    }
}
