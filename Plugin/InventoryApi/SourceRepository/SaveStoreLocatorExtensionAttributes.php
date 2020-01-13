<?php

namespace MageSuite\StoreLocator\Plugin\InventoryApi\SourceRepository;

class SaveStoreLocatorExtensionAttributes
{
    public function beforeSave(
        \Magento\InventoryApi\Api\SourceRepositoryInterface $subject,
        \Magento\InventoryApi\Api\Data\SourceInterface $source
    ) {
        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes !== null && $source instanceof \Magento\Framework\DataObject) {
            $source->setData('url', $extensionAttributes->getUrl());
        }

        return [$source];
    }
}
