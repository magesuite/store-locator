<?php

namespace MageSuite\StoreLocator\Plugin\InventoryApi\SourceRepository;

class LoadStoreLocatorExtensionAttributesOnGet
{
    /**
     * @var \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes
     */
    protected $setExtensionAttributes;

    /**
     * @param \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes $setExtensionAttributes
     */
    public function __construct(
        \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes $setExtensionAttributes
    ) {
        $this->setExtensionAttributes = $setExtensionAttributes;
    }

    public function afterGet(
        \Magento\InventoryApi\Api\SourceRepositoryInterface $subject,
        \Magento\InventoryApi\Api\Data\SourceInterface $source
    ) {
        $this->setExtensionAttributes->execute($source);

        return $source;
    }
}
