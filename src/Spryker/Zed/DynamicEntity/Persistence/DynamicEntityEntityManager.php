<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence;

use Exception;
use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer;
use Generated\Shared\Transfer\DynamicEntityConditionsTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldConditionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfiguration;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

/**
 * @method \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityPersistenceFactory getFactory()
 */
class DynamicEntityEntityManager extends AbstractEntityManager implements DynamicEntityEntityManagerInterface
{
    use TransactionTrait;

    /**
     * @var string
     */
    protected const SET_METHOD_PLACEHOLDER = 'set%s';

    /**
     * @var string
     */
    protected const IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_ERROR_ENTITY_DOES_NOT_EXIST = 'dynamic_entity.validation.entity_does_not_exist';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_ERROR_MISSING_IDENTIFIER = 'dynamic_entity.validation.missing_identifier';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_ERROR_ENTITY_NOT_FOUND_OR_IDENTIFIER_IS_NOT_CREATABLE = 'dynamic_entity.validation.entity_not_found_or_identifier_is_not_creatable';

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    public function createDynamicEntityCollection(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityCollectionResponseTransfer {
        $this->startTransaction();
        $dynamicEntityCollectionResponseTransfer = $this->executeDynamicEntityCollectionCreating($dynamicEntityCollectionRequestTransfer, $dynamicEntityConfigurationTransfer);
        $this->endTransaction($dynamicEntityCollectionResponseTransfer);

        return $dynamicEntityCollectionResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    public function updateDynamicEntityCollection(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityCollectionResponseTransfer {
        $this->startTransaction();
        $dynamicEntityCollectionResponseTransfer = $this->executeDynamicEntityCollectionUpdating($dynamicEntityCollectionRequestTransfer, $dynamicEntityConfigurationTransfer);
        $this->endTransaction($dynamicEntityCollectionResponseTransfer);

        return $dynamicEntityCollectionResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer
     */
    public function createDynamicEntityConfiguration(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer {
        $dynamicEntityMapper = $this->getFactory()->createDynamicEntityMapper();

        $dynamicEntityConfigurationEntity = $dynamicEntityMapper->mapDynamicEntityConfigurationTransferToEntity($dynamicEntityConfigurationTransfer, new SpyDynamicEntityConfiguration());

        $dynamicEntityConfigurationEntity->save();

        return $dynamicEntityMapper->mapDynamicEntityConfigurationToTransfer($dynamicEntityConfigurationEntity, new DynamicEntityConfigurationTransfer());
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer
     */
    public function updateDynamicEntityConfiguration(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer {
        $dynamicEntityConfigurationEntity = $this->getFactory()
            ->createDynamicEntityConfigurationQuery()
            ->filterByIdDynamicEntityConfiguration($dynamicEntityConfigurationTransfer->getIdDynamicEntityConfiguration())
            ->findOne();

        if (!$dynamicEntityConfigurationEntity) {
            return $dynamicEntityConfigurationTransfer;
        }

        $dynamicEntityMapper = $this->getFactory()->createDynamicEntityMapper();
        $dynamicEntityConfigurationEntity = $dynamicEntityMapper->mapDynamicEntityConfigurationTransferToEntity($dynamicEntityConfigurationTransfer, $dynamicEntityConfigurationEntity);

        $dynamicEntityConfigurationEntity->save();

        return $dynamicEntityMapper->mapDynamicEntityConfigurationToTransfer($dynamicEntityConfigurationEntity, new DynamicEntityConfigurationTransfer());
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @throws \Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    protected function executeDynamicEntityCollectionCreating(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityCollectionResponseTransfer {
        $dynamicEntityCollectionResponseTransfer = new DynamicEntityCollectionResponseTransfer();

        $entityClassName = $this->getFactory()->createDynamicEntityQueryBuilder()
            ->getEntityClassName($dynamicEntityConfigurationTransfer->getTableNameOrFail());

        if ($entityClassName === null || !class_exists($entityClassName)) {
            throw new DynamicEntityModelNotFoundException(
                sprintf(
                    'Model for table "%s" not found.',
                    $dynamicEntityConfigurationTransfer->getTableNameOrFail(),
                ),
            );
        }

        foreach ($dynamicEntityCollectionRequestTransfer->getDynamicEntities() as $dynamicEntityTransfer) {
            /** @var \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord */
            $activeRecord = new $entityClassName();

            $errorTransfer = $this->getFactory()->createDynamicEntityFieldCreationPreValidator()->validate(
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $this->resolveFilterCallback($activeRecord),
            );

            if ($errorTransfer !== null) {
                return $dynamicEntityCollectionResponseTransfer->addError($errorTransfer);
            }

            $dynamicEntityTransfer = $this->getFactory()->createDynamicEntityFieldCreationFilter()->filter(
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $activeRecord,
            );

            $dynamicEntityCollectionResponseTransfer = $this->processActiveRecordSaving(
                $dynamicEntityCollectionRequestTransfer,
                $dynamicEntityCollectionResponseTransfer,
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $activeRecord,
            );
        }

        return $dynamicEntityCollectionResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @throws \Spryker\Zed\DynamicEntity\Business\Exception\DynamicEntityModelNotFoundException
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    protected function executeDynamicEntityCollectionUpdating(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityCollectionResponseTransfer {
        $dynamicEntityCollectionResponseTransfer = new DynamicEntityCollectionResponseTransfer();

        $dynamicEntityQueryClassName = $this->getFactory()->createDynamicEntityQueryBuilder()
            ->getEntityQueryClass($dynamicEntityConfigurationTransfer->getTableNameOrFail());

        if (!class_exists($dynamicEntityQueryClassName)) {
            throw new DynamicEntityModelNotFoundException(
                sprintf(
                    'Model for table "%s" not found.',
                    $dynamicEntityConfigurationTransfer->getTableNameOrFail(),
                ),
            );
        }

        foreach ($dynamicEntityCollectionRequestTransfer->getDynamicEntities() as $dynamicEntityTransfer) {
            $dynamicEntityConditionsTransfer = $this->addIdentifierToDynamicEntityConditionsTransfer($dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail(), $dynamicEntityTransfer);

            if ($dynamicEntityConditionsTransfer === null) {
                $this->addErrorToResponseTransfer(
                    $dynamicEntityCollectionResponseTransfer,
                    static::GLOSSARY_KEY_ERROR_MISSING_IDENTIFIER,
                    $dynamicEntityConfigurationTransfer->getTableAliasOrFail(),
                );

                continue;
            }

            $activeRecord = $this->resolveActiveRecord(
                $dynamicEntityCollectionRequestTransfer,
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityConditionsTransfer,
                $dynamicEntityQueryClassName,
            );

            if ($activeRecord === null) {
                $this->addErrorToResponseTransfer(
                    $dynamicEntityCollectionResponseTransfer,
                    static::GLOSSARY_KEY_ERROR_ENTITY_DOES_NOT_EXIST,
                    $dynamicEntityConfigurationTransfer->getTableAliasOrFail(),
                );

                continue;
            }

            if (
                $dynamicEntityCollectionRequestTransfer->getIsCreatable() === true &&
                !$this->isAllowCreateWithSetupIdentifier($activeRecord, $dynamicEntityConfigurationTransfer)
            ) {
                $this->addErrorToResponseTransfer(
                    $dynamicEntityCollectionResponseTransfer,
                    static::GLOSSARY_KEY_ERROR_ENTITY_NOT_FOUND_OR_IDENTIFIER_IS_NOT_CREATABLE,
                    $dynamicEntityConfigurationTransfer->getTableAliasOrFail(),
                );

                continue;
            }

            $errorTransfer = $this->getFactory()->createDynamicEntityFieldUpdatePreValidator()->validate(
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $this->resolveFilterCallback($activeRecord),
            );

            if ($errorTransfer !== null) {
                return $dynamicEntityCollectionResponseTransfer->addError($errorTransfer);
            }

            $dynamicEntityTransfer = $this->getFactory()->createDynamicEntityFieldUpdateFilter()->filter(
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $activeRecord,
            );

            $dynamicEntityCollectionResponseTransfer = $this->processActiveRecordSaving(
                $dynamicEntityCollectionRequestTransfer,
                $dynamicEntityCollectionResponseTransfer,
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityTransfer,
                $activeRecord,
            );
        }

        return $dynamicEntityCollectionResponseTransfer;
    }

    /**
     * @throw \Spryker\Shared\Kernel\Transfer\Exception\NullValueException
     *
     * @param \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return bool
     */
    protected function isAllowCreateWithSetupIdentifier(
        ActiveRecordInterface $activeRecord,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): bool {
        $dynamicEntityDefinitionTransfer = $dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail();
        $identifier = $dynamicEntityDefinitionTransfer->getIdentifierOrFail();

        $fieldDefinitions = $dynamicEntityDefinitionTransfer->getFieldDefinitions()->getArrayCopy();

        /** @var \Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer|null $identifierFieldDefinition */
        $identifierFieldDefinition = array_filter($fieldDefinitions, function (DynamicEntityFieldDefinitionTransfer $fieldDefinition) use ($identifier) {
            return $fieldDefinition->getFieldVisibleNameOrFail() === $identifier;
        })[0] ?? null;

        if ($identifierFieldDefinition === null) {
            return true;
        }

        if ($activeRecord->isNew() && $activeRecord->getByName($identifier) !== null && $identifierFieldDefinition->getIsCreatable() === false) {
            return false;
        }

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     * @param \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord
     *
     * @throws \Exception
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    protected function processActiveRecordSaving(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        DynamicEntityTransfer $dynamicEntityTransfer,
        ActiveRecordInterface $activeRecord
    ): DynamicEntityCollectionResponseTransfer {
        $dynamicEntityDefinitionTransfer = $dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail();

        /** @var \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord */
        $activeRecord = $this->getFactory()->createDynamicEntityMapper()->mapDynamicEntityTransferToDynamicEntity(
            $dynamicEntityTransfer,
            $activeRecord,
        );

        try {
            $activeRecord->save();
        } catch (Exception $exception) {
            $exceptionMapper = $this->getFactory()->createExceptionToErrorMapper();
            $errorMessageTransfer = $exceptionMapper->map($exception, $dynamicEntityConfigurationTransfer);
            if ($errorMessageTransfer !== null) {
                return $dynamicEntityCollectionResponseTransfer->addError($errorMessageTransfer);
            }

            throw $exception;
        }

        $dynamicEntityTransfer = $this->addIdentifierToFields($dynamicEntityTransfer, $dynamicEntityDefinitionTransfer, $activeRecord);

        $dynamicEntityCollectionResponseTransfer->addDynamicEntity($dynamicEntityTransfer);

        return $dynamicEntityCollectionResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConditionsTransfer $dynamicEntityConditionsTransfer
     * @param string $dynamicEntityQueryClassName
     *
     * @return \Propel\Runtime\ActiveRecord\ActiveRecordInterface|null
     */
    protected function resolveActiveRecord(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        DynamicEntityConditionsTransfer $dynamicEntityConditionsTransfer,
        string $dynamicEntityQueryClassName
    ): ?ActiveRecordInterface {
        /** @var \Propel\Runtime\ActiveQuery\ModelCriteria $dynamicEntityQuery */
        $dynamicEntityQuery = new $dynamicEntityQueryClassName();

        $dynamicEntityQuery = $this->getFactory()->createDynamicEntityQueryBuilder()
            ->buildQueryWithFieldConditions(
                $dynamicEntityQuery,
                $dynamicEntityConditionsTransfer,
                $dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail(),
            );

        if ($dynamicEntityCollectionRequestTransfer->getIsCreatable() === true) {
            return $dynamicEntityQuery->findOneOrCreate();
        }

        return $dynamicEntityQuery->findOne();
    }

    /**
     * @param \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord
     *
     * @return callable
     */
    protected function resolveFilterCallback(ActiveRecordInterface $activeRecord): callable
    {
        if ($activeRecord->isNew()) {
            return function (DynamicEntityFieldDefinitionTransfer $fieldDefinitionTransfer) {
                return $fieldDefinitionTransfer->getIsCreatableOrFail() === true;
            };
        }

        return function (DynamicEntityFieldDefinitionTransfer $fieldDefinitionTransfer) {
            return $fieldDefinitionTransfer->getIsEditableOrFail() === true;
        };
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConditionsTransfer|null
     */
    protected function addIdentifierToDynamicEntityConditionsTransfer(
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        DynamicEntityTransfer $dynamicEntityTransfer
    ): ?DynamicEntityConditionsTransfer {
        if (!isset($dynamicEntityTransfer->getFields()[static::IDENTIFIER]) && !isset($dynamicEntityTransfer->getFields()[$dynamicEntityDefinitionTransfer->getIdentifier()])) {
            return null;
        }

        $dynamicEntityConditionsTransfer = new DynamicEntityConditionsTransfer();
        $dynamicEntityConditionsTransfer->addFieldCondition(
            (new DynamicEntityFieldConditionTransfer())
                ->setName($dynamicEntityDefinitionTransfer->getIdentifier())
                ->setValue($dynamicEntityTransfer->getFields()[static::IDENTIFIER] ?? $dynamicEntityTransfer->getFields()[$dynamicEntityDefinitionTransfer->getIdentifier()]),
        );

        return $dynamicEntityConditionsTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer
     * @param string $errorMessage
     * @param string $tableAlias
     * @param array<string, string> $customErrorParameters
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer
     */
    protected function addErrorToResponseTransfer(
        DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer,
        string $errorMessage,
        string $tableAlias,
        array $customErrorParameters = []
    ): DynamicEntityCollectionResponseTransfer {
        $errorMessageTransfer = (new ErrorTransfer())
            ->setEntityIdentifier($tableAlias)
            ->setMessage($errorMessage)
            ->setParameters($customErrorParameters);

        return $dynamicEntityCollectionResponseTransfer->addError($errorMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityTransfer $dynamicEntityTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer
     * @param \Propel\Runtime\ActiveRecord\ActiveRecordInterface $activeRecord
     *
     * @return \Generated\Shared\Transfer\DynamicEntityTransfer
     */
    protected function addIdentifierToFields(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityDefinitionTransfer $dynamicEntityDefinitionTransfer,
        ActiveRecordInterface $activeRecord
    ): DynamicEntityTransfer {
        $identifier = $dynamicEntityDefinitionTransfer->getIdentifierOrFail();
        $dynamicEntityTransfer->setFields(array_merge(
            $dynamicEntityTransfer->getFields(),
            [$identifier => $activeRecord->getByName($identifier)],
        ));

        return $dynamicEntityTransfer;
    }

    /**
     * @return bool
     */
    protected function startTransaction(): bool
    {
        return $this->getFactory()->getConnection()->beginTransaction();
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer
     *
     * @return bool
     */
    protected function endTransaction(DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer): bool
    {
        if (count($dynamicEntityCollectionResponseTransfer->getErrors()) > 0) {
            return $this->getFactory()->getConnection()->rollBack();
        }

        return $this->getFactory()->getConnection()->commit();
    }
}
