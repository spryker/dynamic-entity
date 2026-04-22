<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldValidationTransfer;
use Generated\Shared\Transfer\DynamicEntityRelationFieldMappingTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfiguration;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfigurationRelation;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfigurationRelationFieldMapping;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Spryker\Zed\DynamicEntity\Dependency\Service\DynamicEntityToUtilEncodingServiceInterface;

class DynamicEntityMapper
{
    /**
     * @var string
     */
    protected const FIELDS = 'fields';

    /**
     * @var string
     */
    protected const FIELD_DEFINITIONS = 'fieldDefinitions';

    /**
     * @var string
     */
    protected const IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    protected const IS_DELETABLE = 'isDeletable';

    /**
     * @var string
     */
    protected const VALIDATION = 'validation';

    /**
     * @var string
     */
    protected const SET_METHOD_PLACEHOLDER = 'set%s';

    /**
     * @var string
     */
    protected const TYPE_INTEGER = 'integer';

    /**
     * @var \Spryker\Zed\DynamicEntity\Dependency\Service\DynamicEntityToUtilEncodingServiceInterface
     */
    protected DynamicEntityToUtilEncodingServiceInterface $serviceUtilEncoding;

    public function __construct(DynamicEntityToUtilEncodingServiceInterface $serviceUtilEncoding)
    {
        $this->serviceUtilEncoding = $serviceUtilEncoding;
    }

    public function mapDynamicEntityConfigurationToTransfer(
        SpyDynamicEntityConfiguration $dynamicEntityConfiguration,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer {
        $dynamicEntityConfigurationTransfer->fromArray($dynamicEntityConfiguration->toArray(), true);

        $dynamicEntityConfigurationTransfer->setDynamicEntityDefinition(
            $this->mapDynamicEntityDefinitionToDynamicEntityDefinitionTransfer(
                $dynamicEntityConfiguration->getDefinition(),
                new DynamicEntityDefinitionTransfer(),
            ),
        );

        return $dynamicEntityConfigurationTransfer;
    }

    public function mapDynamicEntityConfigurationTransferToEntity(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        SpyDynamicEntityConfiguration $dynamicEntityConfigurationEntity
    ): SpyDynamicEntityConfiguration {
        $dynamicEntityConfigurationEntity->fromArray($dynamicEntityConfigurationTransfer->toArray());

        $dynamicEntityDefinitionTransfer = $dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail();
        $definitions = $dynamicEntityDefinitionTransfer->toArray(true, true);
        $modifiedDefinitions = $dynamicEntityDefinitionTransfer->modifiedToArray(true, true);
        $definitionForEntity = [
            static::IDENTIFIER => $definitions[static::IDENTIFIER],
            static::IS_DELETABLE => $definitions[static::IS_DELETABLE],
            static::FIELDS => $modifiedDefinitions[static::FIELD_DEFINITIONS] ?? [],
        ];

        $dynamicEntityConfigurationEntity->setDefinition(
            $this->serviceUtilEncoding->encodeJson(
                $definitionForEntity,
            ) ?: '',
        );

        return $dynamicEntityConfigurationEntity;
    }

    /**
     * @param array<mixed> $entityRecordsData
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionTransfer
     */
    public function mapEntityRecordsToCollectionTransfer(
        array $entityRecordsData,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer
    ): DynamicEntityCollectionTransfer {
        $indexedFieldDefinitions = $this->indexDynamicEntityFieldDefinitionsByTableFieldName($dynamicEntityDefinitionTransfer);
        $identifierVisibleName = $this->getIdentifierVisibleName($dynamicEntityDefinitionTransfer->getIdentifierOrFail(), $dynamicEntityDefinitionTransfer);

        foreach ($entityRecordsData as $entityRecord) {
            $dynamicEntityFields = $this->mapRecordFieldsToDynamicEntityFieldsArray($entityRecord, $indexedFieldDefinitions);

            $dynamicEntityTransfer = (new DynamicEntityTransfer())
                ->setFields($dynamicEntityFields)
                ->setIdentifier($dynamicEntityFields[$identifierVisibleName]);

            $dynamicEntityCollectionTransfer->addDynamicEntity($dynamicEntityTransfer);
        }

        return $dynamicEntityCollectionTransfer;
    }

    public function mapEntityRecordToDynamicEntityTransfer(
        ActiveRecordInterface $entityRecord,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        DynamicEntityTransfer $dynamicEntityTransfer
    ): DynamicEntityTransfer {
        $indexedFieldDefinitions = $this->indexDynamicEntityFieldDefinitionsByTableFieldName($dynamicEntityDefinitionTransfer);
        $identifierVisibleName = $this->getIdentifierVisibleName($dynamicEntityDefinitionTransfer->getIdentifierOrFail(), $dynamicEntityDefinitionTransfer);
        $dynamicEntityFields = $this->mapRecordFieldsToDynamicEntityFieldsArray($entityRecord, $indexedFieldDefinitions);
        $dynamicEntityTransfer
            ->setFields($dynamicEntityFields)
            ->setIdentifier($dynamicEntityFields[$identifierVisibleName]);

        return $dynamicEntityTransfer;
    }

    /**
     * @param array<mixed> $dynamicEntityConfigurationData
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer
     */
    public function mapDynamicEntityConfigurationsToCollectionTransfer(
        array $dynamicEntityConfigurationData,
        DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
    ): DynamicEntityConfigurationCollectionTransfer {
        foreach ($dynamicEntityConfigurationData as $dynamicEntityConfiguration) {
            $dynamicEntityConfigurationTransfer = $this->mapDynamicEntityConfigurationToTransfer(
                $dynamicEntityConfiguration,
                new DynamicEntityConfigurationTransfer(),
            );

            $dynamicEntityConfigurationTransfer = $this->mapDynamicEntityConfigurationCollectionToDynamicEntityConfigurationTransfers(
                $dynamicEntityConfiguration,
                $dynamicEntityConfigurationTransfer,
            );

            $dynamicEntityConfigurationCollectionTransfer->addDynamicEntityConfiguration($dynamicEntityConfigurationTransfer);
        }

        return $dynamicEntityConfigurationCollectionTransfer;
    }

    public function mapDynamicEntityTransferToDynamicEntity(
        DynamicEntityTransfer $dynamicEntityTransfer,
        ActiveRecordInterface $activeRecord,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
    ): ?ActiveRecordInterface {
        $visibleNameToFieldName = $this->indexFieldNamesByVisibleName($dynamicEntityDefinitionTransfer);

        foreach ($dynamicEntityTransfer->getFields() as $fieldVisibleName => $fieldValue) {
            $actualFieldName = $visibleNameToFieldName[$fieldVisibleName] ?? $fieldVisibleName;
            $setFieldMethod = $this->getSetFieldMethod($actualFieldName);
            $activeRecord->$setFieldMethod($fieldValue);
        }

        return $activeRecord;
    }

    public function mapDynamicEntityConfigurationCollectionToDynamicEntityConfigurationTransfers(
        SpyDynamicEntityConfiguration $dynamicEntityConfiguration,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer {
        foreach ($dynamicEntityConfiguration->getSpyDynamicEntityConfigurationRelationsRelatedByFkParentDynamicEntityConfiguration() as $dynamicEntityRelationConfigurationRelation) {
            $childConfigurationEntity = $dynamicEntityRelationConfigurationRelation->getSpyDynamicEntityConfigurationRelatedByFkChildDynamicEntityConfiguration();

            $dynamicEntityConfigurationTransfer = $this->mapDynamicEntityConfigurationEntityRelationToDynamicEntityConfigurationTransfer(
                $childConfigurationEntity,
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityRelationConfigurationRelation,
            );
        }

        return $dynamicEntityConfigurationTransfer;
    }

    protected function mapDynamicEntityConfigurationEntityRelationToDynamicEntityConfigurationTransfer(
        SpyDynamicEntityConfiguration $dynamicEntityConfigurationEntity,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        SpyDynamicEntityConfigurationRelation $dynamicEntityRelationConfigurationRelation
    ): DynamicEntityConfigurationTransfer {
        $childDynamicEntityConfigurationTransfer = $this->mapDynamicEntityConfigurationToTransfer(
            $dynamicEntityConfigurationEntity,
            new DynamicEntityConfigurationTransfer(),
        );

        $childConfigurationRelationTransfer = (new DynamicEntityConfigurationRelationTransfer())
            ->setName($dynamicEntityRelationConfigurationRelation->getName())
            ->setIsEditable($dynamicEntityRelationConfigurationRelation->getIsEditable())
            ->setChildDynamicEntityConfiguration($childDynamicEntityConfigurationTransfer);

        foreach ($dynamicEntityRelationConfigurationRelation->getSpyDynamicEntityConfigurationRelationFieldMappings() as $fieldMapping) {
            $childConfigurationRelationTransfer = $this->mapDynamicEntityConfigurationRelationToDynamicEntityConfigurationTransfer(
                $fieldMapping,
                $childConfigurationRelationTransfer,
            );
        }

        $dynamicEntityConfigurationTransfer->addChildRelation($childConfigurationRelationTransfer);

        return $dynamicEntityConfigurationTransfer;
    }

    protected function mapDynamicEntityConfigurationRelationToDynamicEntityConfigurationTransfer(
        SpyDynamicEntityConfigurationRelationFieldMapping $fieldMapping,
        DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer
    ): DynamicEntityConfigurationRelationTransfer {
        $dynamicEntityRelationFieldMappingTransfer = (new DynamicEntityRelationFieldMappingTransfer())
            ->setParentFieldName($fieldMapping->getParentFieldName())
            ->setChildFieldName($fieldMapping->getChildFieldName());

        $dynamicEntityConfigurationRelationTransfer->addRelationFieldMapping(
            $dynamicEntityRelationFieldMappingTransfer,
        );

        return $dynamicEntityConfigurationRelationTransfer;
    }

    protected function convertSnakeCaseToCamelCase(string $input): string
    {
        return str_replace('_', '', ucwords($input, '_'));
    }

    protected function getSetFieldMethod(string $input): string
    {
        return sprintf(static::SET_METHOD_PLACEHOLDER, $this->convertSnakeCaseToCamelCase($input));
    }

    protected function mapDynamicEntityDefinitionToDynamicEntityDefinitionTransfer(
        string $definition,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
    ): DynamicEntityDefinitionTransfer {
        $config = $this->serviceUtilEncoding->decodeJson($definition, true);

        if (!is_array($config) || !isset($config[static::FIELDS])) {
            return $dynamicEntityDefinitionTransfer;
        }

        $dynamicEntityDefinitionTransfer->setIdentifier($config[static::IDENTIFIER]);
        if (array_key_exists(static::IS_DELETABLE, $config)) {
            $dynamicEntityDefinitionTransfer->setIsDeletable($config[static::IS_DELETABLE]);
        }

        foreach ($config[static::FIELDS] as $field) {
            $dynamicEntityFieldDefinitionTransfer = (new DynamicEntityFieldDefinitionTransfer())->fromArray($field, true);

            if (!empty($field[static::VALIDATION])) {
                $dynamicEntityFieldDefinitionTransfer->setValidation(
                    (new DynamicEntityFieldValidationTransfer())->fromArray($field[static::VALIDATION], true),
                );
            }

            $dynamicEntityDefinitionTransfer->addFieldDefinition(
                $dynamicEntityFieldDefinitionTransfer,
            );
        }

        return $dynamicEntityDefinitionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     *
     * @return array<mixed>
     */
    protected function indexDynamicEntityFieldDefinitionsByTableFieldName(DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer): array
    {
        $result = [];

        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinition) {
            $result[$fieldDefinition->getFieldNameOrFail()] = $fieldDefinition;
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    protected function indexFieldNamesByVisibleName(DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer): array
    {
        $result = [];

        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinition) {
            $result[$fieldDefinition->getFieldVisibleNameOrFail()] = $fieldDefinition->getFieldNameOrFail();
        }

        return $result;
    }

    /**
     * @param \Propel\Runtime\ActiveRecord\ActiveRecordInterface $entityRecord
     * @param array<mixed> $indexedFieldDefinitions
     *
     * @return array<mixed>
     */
    protected function mapRecordFieldsToDynamicEntityFieldsArray(
        ActiveRecordInterface $entityRecord,
        array $indexedFieldDefinitions
    ): array {
        $dynamicEntityFields = [];

        foreach ($entityRecord->toArray() as $fieldName => $value) {
            if (!isset($indexedFieldDefinitions[$fieldName])) {
                continue;
            }

            $dynamicEntityFields[$indexedFieldDefinitions[$fieldName]->getFieldVisibleName()] = $this->castTypes($indexedFieldDefinitions[$fieldName]->getType(), $value);
        }

        return $dynamicEntityFields;
    }

    protected function castTypes(string $type, mixed $value): mixed
    {
        return ($type === static::TYPE_INTEGER && $value !== null) ? (int)$value : $value;
    }

    protected function getIdentifierVisibleName(string $identifier, DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer): string
    {
        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinitionTransfer) {
            if ($fieldDefinitionTransfer->getFieldNameOrFail() === $identifier) {
                return $fieldDefinitionTransfer->getFieldVisibleNameOrFail();
            }
        }

        return $identifier;
    }
}
