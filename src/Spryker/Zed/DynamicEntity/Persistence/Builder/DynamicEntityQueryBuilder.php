<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence\Builder;

use Generated\Shared\Transfer\DynamicEntityConditionsTransfer;
use Generated\Shared\Transfer\DynamicEntityCriteriaTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Map\DatabaseMap;
use Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

class DynamicEntityQueryBuilder implements DynamicEntityQueryBuilderInterface
{
    /**
     * @var string
     */
    protected const QUERY_CLASS_PLACEHOLDER = '%sQuery';

    /**
     * @var string
     */
    protected const IDENTIFIER_KEY = 'identifier';

    /**
     * @var string
     */
    protected const ERROR_ENTITY_MODEL_NOT_FOUND = 'Model for table "%s" not found.';

    /**
     * @var \Propel\Runtime\Map\DatabaseMap
     */
    protected DatabaseMap $databaseMap;

    /**
     * @var array<\Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\FilterStrategyInterface>
     */
    protected array $filterStrategies;

    /**
     * @param \Propel\Runtime\Map\DatabaseMap $databaseMap
     * @param array<\Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\FilterStrategyInterface> $filterStrategies
     */
    public function __construct(
        DatabaseMap $databaseMap,
        array $filterStrategies
    ) {
        $this->databaseMap = $databaseMap;
        $this->filterStrategies = $filterStrategies;
    }

    public function getEntityClassName(string $tableName): ?string
    {
        return $this->databaseMap->getTable($tableName)->getClassName();
    }

    /**
     * @param string $tableName
     *
     * @throws \Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException
     *
     * @return string
     */
    public function assertEntityClassNameExists(string $tableName): string
    {
        $entityClassName = $this->getEntityClassName($tableName);

        if ($entityClassName === null || !class_exists($entityClassName)) {
            throw new DynamicEntityModelNotFoundException(
                sprintf(static::ERROR_ENTITY_MODEL_NOT_FOUND, $tableName),
            );
        }

        return $entityClassName;
    }

    public function getEntityQueryClass(string $tableName): string
    {
        return sprintf(static::QUERY_CLASS_PLACEHOLDER, $this->getEntityClassName($tableName));
    }

    /**
     * @param string $tableName
     *
     * @throws \Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException
     *
     * @return string
     */
    public function assertEntityQueryClassNameExists(string $tableName): string
    {
        $dynamicEntityQueryClassName = $this->getEntityQueryClass($tableName);

        if (!class_exists($dynamicEntityQueryClassName)) {
            throw new DynamicEntityModelNotFoundException(
                sprintf(static::ERROR_ENTITY_MODEL_NOT_FOUND, $tableName),
            );
        }

        return $dynamicEntityQueryClassName;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $query
     * @param \Generated\Shared\Transfer\DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     * @param array<string, array<int|string>> $foreignKeyFieldMappingArray
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildQueryWithFieldConditions(
        ModelCriteria $query,
        DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        array $foreignKeyFieldMappingArray = []
    ): ModelCriteria {
        if ($dynamicEntityCriteriaTransfer->getDynamicEntityConditions() !== null) {
            $query = $this->filterByFieldConditions(
                $query,
                $dynamicEntityDefinitionTransfer,
                $dynamicEntityCriteriaTransfer->getDynamicEntityConditions(),
            );
        }

        if ($foreignKeyFieldMappingArray === []) {
            return $query;
        }

        foreach ($foreignKeyFieldMappingArray as $fieldConditionName => $fieldConditionValues) {
            $query->filterBy($this->convertSnakeCaseToCamelCase($fieldConditionName), $fieldConditionValues, Criteria::IN);
        }

        return $query;
    }

    protected function convertSnakeCaseToCamelCase(string $input): string
    {
        return str_replace('_', '', ucwords($input, '_'));
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     *
     * @return array<string>
     */
    protected function collectDefinedFieldVisibleNames(DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer): array
    {
        $definedFieldVisibleNames = [];

        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinition) {
            $definedFieldVisibleNames[] = $fieldDefinition->getFieldVisibleNameOrFail();
        }

        return $definedFieldVisibleNames;
    }

    protected function filterByFieldConditions(
        ModelCriteria $query,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        ?DynamicEntityConditionsTransfer $dynamicEntityConditionsTransfer
    ): ModelCriteria {
        if ($dynamicEntityConditionsTransfer === null || $dynamicEntityConditionsTransfer->getFieldConditions()->getArrayCopy() === []) {
            return $query;
        }

        $definedFieldNames = $this->collectDefinedFieldVisibleNames($dynamicEntityDefinitionTransfer);

        foreach ($dynamicEntityConditionsTransfer->getFieldConditions() as $fieldCondition) {
            $fieldConditionName = $fieldCondition->getNameOrFail();

            if ($fieldConditionName === static::IDENTIFIER_KEY) {
                $fieldConditionName = $this->getVisibleIdentifier($dynamicEntityDefinitionTransfer, $fieldConditionName);
            }

            if (!in_array($fieldConditionName, $definedFieldNames)) {
                continue;
            }

            // fieldConditionName is the visible name (API alias); Propel needs the actual column name
            $actualFieldName = $this->resolveFieldNameByVisibleName($dynamicEntityDefinitionTransfer, $fieldConditionName);
            $query = $this->applyConditionToQuery($query, $this->convertSnakeCaseToCamelCase($actualFieldName), $fieldCondition->getValue());
        }

        return $query;
    }

    protected function applyConditionToQuery(
        ModelCriteria $query,
        string $fieldConditionName,
        ?string $fieldConditionValue
    ): ModelCriteria {
        foreach ($this->filterStrategies as $filterStrategy) {
            if ($filterStrategy->isApplicable($fieldConditionValue)) {
                return $filterStrategy->applyConditionToQuery($query, $fieldConditionName, $fieldConditionValue);
            }
        }

        return $query;
    }

    protected function resolveFieldNameByVisibleName(
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        string $fieldVisibleName
    ): string {
        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->getFieldVisibleNameOrFail() === $fieldVisibleName) {
                return $fieldDefinition->getFieldNameOrFail();
            }
        }

        return $fieldVisibleName;
    }

    protected function getVisibleIdentifier(
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        string $fieldConditionName
    ): string {
        $identifier = $dynamicEntityDefinitionTransfer->getIdentifierOrFail();
        foreach ($dynamicEntityDefinitionTransfer->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->getFieldNameOrFail() === $identifier) {
                $fieldConditionName = $fieldDefinition->getFieldVisibleNameOrFail();
            }
        }

        return $fieldConditionName;
    }
}
