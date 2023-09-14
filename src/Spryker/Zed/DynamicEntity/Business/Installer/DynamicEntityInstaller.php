<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Installer;

use Generated\Shared\Transfer\DynamicEntityConfigurationCriteriaTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityFileNotReadableException;
use Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapper;
use Spryker\Zed\DynamicEntity\DynamicEntityConfig;
use Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManagerInterface;
use Spryker\Zed\DynamicEntity\Persistence\DynamicEntityRepositoryInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class DynamicEntityInstaller implements DynamicEntityInstallerInterface
{
    use TransactionTrait;

    /**
     * @var string
     */
    protected const TABLE_ALIAS = 'tableAlias';

    /**
     * @var string
     */
    protected const TABLE_NAME = 'tableName';

    /**
     * @var string
     */
    protected const MESSAGE_FILE_NOT_READABLE = 'Could not read from file: %s';

    /**
     * @var string
     */
    protected const MESSAGE_FILE_CONTAINS_INVALID_JSON = 'File contains invalid JSON: %s';

    /**
     * @var \Spryker\Zed\DynamicEntity\DynamicEntityConfig
     */
    protected DynamicEntityConfig $dynamicEntityConfig;

    /**
     * @var \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityRepositoryInterface
     */
    protected DynamicEntityRepositoryInterface $dynamicEntityRepository;

    /**
     * @var \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManagerInterface
     */
    protected DynamicEntityEntityManagerInterface $entityManager;

    /**
     * @var \Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapper
     */
    protected DynamicEntityMapper $dynamicEntityMapper;

    /**
     * @param \Spryker\Zed\DynamicEntity\DynamicEntityConfig $dynamicEntityConfig
     * @param \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityRepositoryInterface $dynamicEntityRepository
     * @param \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManagerInterface $entityManager
     * @param \Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapper $dynamicEntityMapper
     */
    public function __construct(
        DynamicEntityConfig $dynamicEntityConfig,
        DynamicEntityRepositoryInterface $dynamicEntityRepository,
        DynamicEntityEntityManagerInterface $entityManager,
        DynamicEntityMapper $dynamicEntityMapper
    ) {
        $this->dynamicEntityConfig = $dynamicEntityConfig;
        $this->dynamicEntityRepository = $dynamicEntityRepository;
        $this->entityManager = $entityManager;
        $this->dynamicEntityMapper = $dynamicEntityMapper;
    }

    /**
     * @return void
     */
    public function install(): void
    {
        $this->getTransactionHandler()->handleTransaction(function (): void {
            $this->executeTransaction();
        });
    }

    /**
     * @throws \Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityFileNotReadableException
     *
     * @return void
     */
    protected function executeTransaction(): void
    {
        $installerConfigurationDataPath = $this->dynamicEntityConfig->getInstallerConfigurationDataFilePath();
        $installerConfigurationData = file_get_contents($installerConfigurationDataPath);

        if ($installerConfigurationData === false) {
            throw new DynamicEntityFileNotReadableException(sprintf(static::MESSAGE_FILE_NOT_READABLE, $installerConfigurationDataPath));
        }

        $installerConfigurationDecodedData = json_decode($installerConfigurationData, true);
        if ($installerConfigurationDecodedData === false) {
            throw new DynamicEntityFileNotReadableException(sprintf(static::MESSAGE_FILE_CONTAINS_INVALID_JSON, $installerConfigurationData));
        }

        $tableAliases = $this->getTableAliases();
        foreach ($installerConfigurationDecodedData as $dynamicEntityConfiguration) {
            if (!isset($dynamicEntityConfiguration[static::TABLE_ALIAS])) {
                continue;
            }

            if (
                !array_key_exists($dynamicEntityConfiguration[static::TABLE_ALIAS], $tableAliases)
                && !in_array($dynamicEntityConfiguration[static::TABLE_NAME], $tableAliases)
            ) {
                $dynamicEntityConfigurationTransfer = $this->dynamicEntityMapper->mapDynamicEntityConfigurationToDynamicEntityConfigurationTransfer(
                    $dynamicEntityConfiguration,
                    new DynamicEntityConfigurationTransfer(),
                );

                $this->entityManager->createDynamicEntityConfiguration($dynamicEntityConfigurationTransfer);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getTableAliases(): array
    {
        $dynamicEntityConfigurationCollectionTransfer = $this->dynamicEntityRepository->getDynamicEntityConfigurationCollection(new DynamicEntityConfigurationCriteriaTransfer());
        $tableAliases = [];
        foreach ($dynamicEntityConfigurationCollectionTransfer->getDynamicEntityConfigurations() as $dynamicEntityConfiguration) {
            $tableAliases[$dynamicEntityConfiguration->getTableAliasOrFail()] = $dynamicEntityConfiguration->getTableNameOrFail();
        }

        return $tableAliases;
    }
}