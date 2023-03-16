<?php
declare(strict_types=1);

namespace MageSuite\StoreLocator\Model\Import;

class Source extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    public const ENTITY_CODE = 'inventory_source';
    public const SOURCE_CODE_COLUMN = 'source_code';

    protected $needColumnCheck = true;
    protected $logInHistory = true;
    protected array $stockCache = [];
    protected int $linkPriority = 0;

    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Framework\App\ResourceConnection $resource;
    protected \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository;
    protected int $batchSize;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository,
        int $batchSize = 100,
        array $validColumnNames = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->stockRepository = $stockRepository;
        $this->batchSize = $batchSize;
        $this->validColumnNames = $validColumnNames;
        $this->initMessageTemplates();
    }

    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    public function initMessageTemplates(): void
    {
        $this->addMessageTemplate(
            'SourceCodeIsRequired',
            __('The source code cannot be empty.')
        );
        $this->addMessageTemplate(
            'NameIsRequired',
            __('The name cannot be empty.')
        );
        $this->addMessageTemplate(
            'CountryIdIsRequired',
            __('The country_id cannot be empty.')
        );
        $this->addMessageTemplate(
            'PostcodeIsRequired',
            __('The postcode cannot be empty.')
        );
        $this->addMessageTemplate(
            'StockNameIsInvalid',
            __('The stock_name is invalid.')
        );
    }

    public function validateRow(array $rowData, $rowNum): bool
    {
        $sourceCode = $rowData['source_code'] ?? null;
        $name = $rowData['name'] ?? null;
        $countryId = $rowData['country_id'] ?? null;
        $postcode = $rowData['postcode'] ?? null;
        $stockName = $rowData['stock_name'] ?? null;

        if (!$sourceCode) {
            $this->addRowError('SourceCodeIsRequired', $rowNum);
        }

        if (!$name) {
            $this->addRowError('NameIsRequired', $rowNum);
        }

        if (!$countryId) {
            $this->addRowError('CountryIdIsRequired', $rowNum);
        }

        if (!$postcode) {
            $this->addRowError('PostcodeIsRequired', $rowNum);
        }

        if ($stockName && !$this->getStockIdByName($stockName)) {
            $this->addRowError('StockNameIsInvalid', $rowNum);
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE:
            case \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    protected function saveAndReplaceEntity(): void
    {
        $behavior = $this->getBehavior();
        $rows = [];
        $this->linkPriority = 0;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $this->processBunch($bunch);
        }
    }

    public function processBunch(array $bunch): void
    {
        $entityList = [];
        $sourceToStockLink = [];

        foreach ($bunch as $rowNum => $row) {
            if (!$this->validateRow($row, $rowNum)) {
                continue;
            }

            if ($this->getErrorAggregator()->hasToBeTerminated()) {
                $this->getErrorAggregator()->addRowToSkip($rowNum);
                continue;
            }

            $rowId = $row[static::SOURCE_CODE_COLUMN];
            $columnValues = [];

            foreach ($this->getAvailableColumns() as $columnKey) {
                $columnValues[$columnKey] = $row[$columnKey] ?? null;
            }

            if (isset($row['stock_name']) && $stockId = $this->getStockIdByName($row['stock_name'])) {
                $sourceToStockLink[] = [
                    'stock_id' => $stockId,
                    'source_code' => $row['source_code'],
                    'priority' => $this->linkPriority
                ];
                ++$this->linkPriority;
            }

            $entityList[$rowId] = $columnValues;
            $this->countItemsCreated += (int) !isset($row[static::SOURCE_CODE_COLUMN]);
            $this->countItemsUpdated += (int) isset($row[static::SOURCE_CODE_COLUMN]);
        }

        $this->saveSourceData($entityList);
        $this->saveSourceToStockLink($sourceToStockLink);
    }

    protected function saveSourceData(array $entityList): void
    {
        if (empty($entityList)) {
            return;
        }

        $this->connection->insertOnDuplicate(
            $this->resource->getTableName('inventory_source'),
            $entityList,
            $this->getAvailableColumns()
        );
    }

    protected function saveSourceToStockLink(array $sourceToStockLink): void
    {
        if (empty($sourceToStockLink)) {
            return;
        }

        $this->connection->insertOnDuplicate(
            $this->resource->getTableName('inventory_source_stock_link'),
            $sourceToStockLink,
            ['stock_id', 'source_code', 'priority']
        );
    }

    public function getAvailableColumns(): array
    {
        return array_diff($this->validColumnNames, ['stock_name']);
    }

    protected function getStockIdByName(string $stockName): int
    {
        if (empty($this->stockCache)) {
            $stockList = $this->stockRepository->getList();
            $this->stockCache = $stockList->getItems();
        }

        foreach ($this->stockCache as $stock) {
            if ($stock->getName() != $stockName) {
                continue;
            }

            return $stock->getStockId();
        }

        return 0;
    }
}
