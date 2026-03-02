<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness;

use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\DynamicEntity\Business\Indexer\DynamicEntityIndexerInterface;
use Spryker\Zed\DynamicEntity\Business\Resolver\DynamicEntityErrorPathResolverInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface;
use Spryker\Zed\DynamicEntity\DynamicEntityConfig;

class RequestFieldValidator implements DynamicEntityValidatorInterface
{
    /**
     * @var string
     */
    protected const IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_PROVIDED_FIELD_IS_INVALID = 'dynamic_entity.validation.provided_field_is_invalid';

    /**
     * @var \Spryker\Zed\DynamicEntity\Business\Resolver\DynamicEntityErrorPathResolverInterface
     */
    protected DynamicEntityErrorPathResolverInterface $dynamicEntityErrorPathResolver;

    /**
     * @var \Spryker\Zed\DynamicEntity\Business\Indexer\DynamicEntityIndexerInterface
     */
    protected DynamicEntityIndexerInterface $dynamicEntityIndexer;

    public function __construct(
        DynamicEntityErrorPathResolverInterface $dynamicEntityErrorPathResolver,
        DynamicEntityIndexerInterface $dynamicEntityIndexer
    ) {
        $this->dynamicEntityErrorPathResolver = $dynamicEntityErrorPathResolver;
        $this->dynamicEntityIndexer = $dynamicEntityIndexer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param int $index
     *
     * @return array<\Generated\Shared\Transfer\ErrorTransfer>
     */
    public function validate(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        int $index
    ): array {
        $errorPath = $this->dynamicEntityErrorPathResolver->getErrorPath(
            $index,
            $dynamicEntityConfigurationTransfer->getTableAliasOrFail(),
        );

        $errorTransfers = $this->validateFieldNames(
            $dynamicEntityTransfer,
            $dynamicEntityConfigurationTransfer,
            $dynamicEntityConfigurationTransfer->getTableAliasOrFail(),
            $errorPath,
        );

        return array_merge($errorTransfers, $this->validateRelationChains(
            $dynamicEntityTransfer,
            $dynamicEntityConfigurationTransfer,
            $errorPath,
        ));
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param string $errorPath
     *
     * @return array<\Generated\Shared\Transfer\ErrorTransfer>
     */
    protected function validateRelationChains(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        string $errorPath
    ): array {
        $errorTransfers = [];
        $childRelationsIndexedByRelationName = $this->dynamicEntityIndexer->getChildRelationsIndexedByRelationName($dynamicEntityConfigurationTransfer);

        foreach ($dynamicEntityTransfer->getChildRelations() as $childRelationTransfer) {
            $errorTransfers = array_merge(
                $errorTransfers,
                $this->validateChildRelationEntities(
                    $childRelationTransfer,
                    $dynamicEntityConfigurationTransfer,
                    $childRelationsIndexedByRelationName,
                    $errorPath,
                ),
            );
        }

        return $errorTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityRelationTransfer $childRelationTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param array<mixed> $childRelationsIndexedByRelationName
     * @param string $errorPath
     *
     * @return array<\Generated\Shared\Transfer\ErrorTransfer>
     */
    protected function validateChildRelationEntities(
        DynamicEntityRelationTransfer $childRelationTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        array $childRelationsIndexedByRelationName,
        string $errorPath
    ): array {
        $errorTransfers = [];
        foreach ($childRelationTransfer->getDynamicEntities() as $index => $dynamicEntityTransfer) {
            $childTableAlias = $childRelationsIndexedByRelationName[$childRelationTransfer->getNameOrFail()]->getChildDynamicEntityConfigurationOrFail()->getTableAliasOrFail();
            $childErrorPath = $this->dynamicEntityErrorPathResolver->getErrorPath($index, $childTableAlias, $errorPath);

            $fieldNamesErrorTransfers = $this->validateFieldNames(
                $dynamicEntityTransfer,
                $dynamicEntityConfigurationTransfer,
                $childTableAlias,
                $childErrorPath,
            );

            $relationChainsErrorTransfers = $this->validateRelationChains(
                $dynamicEntityTransfer,
                $dynamicEntityConfigurationTransfer,
                $childErrorPath,
            );

            $errorTransfers = array_merge($errorTransfers, $fieldNamesErrorTransfers, $relationChainsErrorTransfers);
        }

        return $errorTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param string $entityIdentifier
     * @param string $errorPath
     *
     * @return array<\Generated\Shared\Transfer\ErrorTransfer>
     */
    protected function validateFieldNames(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        string $entityIdentifier,
        string $errorPath
    ): array {
        $errorTransfers = [];
        $definitionsIndexedByFieldVisibleName = $this->dynamicEntityIndexer->getDefinitionsIndexedByFieldVisibleName($dynamicEntityConfigurationTransfer);

        foreach ($dynamicEntityTransfer->getFields() as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                continue;
            }

            if (isset($definitionsIndexedByFieldVisibleName[$entityIdentifier][$fieldName]) || $fieldName === static::IDENTIFIER) {
                continue;
            }

            $errorTransfers[] = (new ErrorTransfer())
                ->setEntityIdentifier($entityIdentifier)
                ->setMessage(static::GLOSSARY_KEY_PROVIDED_FIELD_IS_INVALID)
                ->setParameters([
                    DynamicEntityConfig::PLACEHOLDER_FIELD_NAME => $fieldName,
                    DynamicEntityConfig::ERROR_PATH => $errorPath,
                ]);
        }

        return $errorTransfers;
    }
}
