<?php
declare(strict_types=1);

namespace MageSuite\StoreLocator\Helper;

class Configuration
{
    const IS_ENABLED_CONFIG_PATH = 'store_locator/page/is_enabled';
    const META_TITLE_CONFIG_PATH = 'store_locator/page/meta_title';
    const META_DESCRIPTION_CONFIG_PATH = 'store_locator/page/meta_description';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_ENABLED_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMetaTitle($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::META_TITLE_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMetaDescription($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::META_DESCRIPTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
