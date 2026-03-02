<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business;

use Propel\Runtime\Map\DatabaseMap;
use Propel\Runtime\Propel;
use Spryker\Zed\DynamicEntity\Business\Builder\DynamicEntityCollectionRequestBuilder;
use Spryker\Zed\DynamicEntity\Business\Builder\DynamicEntityCollectionRequestBuilderInterface;
use Spryker\Zed\DynamicEntity\Business\Builder\DynamicEntityRelationConfigurationTreeBuilder;
use Spryker\Zed\DynamicEntity\Business\Builder\DynamicEntityRelationConfigurationTreeBuilderInterface;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityConfiguration\DynamicEntityConfigurationColumnDetailProvider;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityConfiguration\DynamicEntityConfigurationColumnDetailProviderInterface;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityConfigurationCreator;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityConfigurationCreatorInterface;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityCreator;
use Spryker\Zed\DynamicEntity\Business\Creator\DynamicEntityCreatorInterface;
use Spryker\Zed\DynamicEntity\Business\Deleter\DynamicEntityDeleter;
use Spryker\Zed\DynamicEntity\Business\Deleter\DynamicEntityDeleterInterface;
use Spryker\Zed\DynamicEntity\Business\Expander\DynamicEntityPostEditRequestExpander;
use Spryker\Zed\DynamicEntity\Business\Expander\DynamicEntityPostEditRequestExpanderInterface;
use Spryker\Zed\DynamicEntity\Business\Filter\DynamicEntityFieldCreationFilter;
use Spryker\Zed\DynamicEntity\Business\Filter\DynamicEntityFieldUpdateFilter;
use Spryker\Zed\DynamicEntity\Business\Filter\DynamicEntityFilterInterface;
use Spryker\Zed\DynamicEntity\Business\Indexer\DynamicEntityIndexer;
use Spryker\Zed\DynamicEntity\Business\Indexer\DynamicEntityIndexerInterface;
use Spryker\Zed\DynamicEntity\Business\Installer\DynamicEntityInstaller;
use Spryker\Zed\DynamicEntity\Business\Installer\DynamicEntityInstallerInterface;
use Spryker\Zed\DynamicEntity\Business\Installer\Validator\FieldMappingValidator;
use Spryker\Zed\DynamicEntity\Business\Installer\Validator\FieldMappingValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapper;
use Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapperInterface;
use Spryker\Zed\DynamicEntity\Business\Reader\DisallowedTablesReader;
use Spryker\Zed\DynamicEntity\Business\Reader\DisallowedTablesReaderInterface;
use Spryker\Zed\DynamicEntity\Business\Reader\DynamicEntityReader;
use Spryker\Zed\DynamicEntity\Business\Reader\DynamicEntityReaderInterface;
use Spryker\Zed\DynamicEntity\Business\Resolver\DynamicEntityErrorPathResolver;
use Spryker\Zed\DynamicEntity\Business\Resolver\DynamicEntityErrorPathResolverInterface;
use Spryker\Zed\DynamicEntity\Business\Transaction\Propel\TransactionProcessor;
use Spryker\Zed\DynamicEntity\Business\Transaction\Propel\TransactionProcessorInterface;
use Spryker\Zed\DynamicEntity\Business\Updater\DynamicEntityConfigurationUpdater;
use Spryker\Zed\DynamicEntity\Business\Updater\DynamicEntityConfigurationUpdaterInterface;
use Spryker\Zed\DynamicEntity\Business\Updater\DynamicEntityUpdater;
use Spryker\Zed\DynamicEntity\Business\Updater\DynamicEntityUpdaterInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityComprehensiveValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityComprehensiveValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityConfigurationTreeValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityConfigurationTreeValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityConfigurationValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityConfigurationValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\Constraint\ConstraintInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\Constraint\UrlConstraint;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\ConstraintValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\RequestFieldValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\RequiredFieldValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Immutability\CreationFieldImmutabilityValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Immutability\UpdateFieldImmutabilityValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Type\BooleanFieldTypeValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Type\DecimalFieldTypeValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Type\IntegerFieldTypeValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Type\StringFieldTypeValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Relation\EditableRelationValidator;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Configuration\AllowedTablesValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Configuration\ResourceNameValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Configuration\UniqueTableNameAliasValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\FieldTypeBooleanValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\FieldTypeDecimalValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\FieldTypeIntegerValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\FieldTypeStringValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\RequiredFieldsValidatorRule;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\ValidatorRuleInterface;
use Spryker\Zed\DynamicEntity\Business\Writer\DynamicEntityWriter;
use Spryker\Zed\DynamicEntity\Business\Writer\DynamicEntityWriterInterface;
use Spryker\Zed\DynamicEntity\Dependency\External\DynamicEntityToConnectionInterface;
use Spryker\Zed\DynamicEntity\DynamicEntityDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\DynamicEntity\DynamicEntityConfig getConfig()
 * @method \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityRepositoryInterface getRepository()
 * @method \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManagerInterface getEntityManager()
 */
class DynamicEntityBusinessFactory extends AbstractBusinessFactory
{
    public function createDynamicEntityReader(): DynamicEntityReaderInterface
    {
        return new DynamicEntityReader(
            $this->getRepository(),
            $this->createDynamicEntityMapper(),
            $this->createDynamicEntityConfigurationTreeValidator(),
            $this->createDynamicEntityRelationConfigurationTreeBuilder(),
        );
    }

    public function createDynamicEntityCreator(): DynamicEntityCreatorInterface
    {
        return new DynamicEntityCreator(
            $this->createDynamicEntityReader(),
            $this->createDynamicEntityCreateWriter(),
            $this->createDynamicEntityMapper(),
            $this->createTransactionProcessor(),
            $this->getDynamicEntityPostCreatePlugins(),
        );
    }

    public function createDynamicEntityUpdater(): DynamicEntityUpdaterInterface
    {
        return new DynamicEntityUpdater(
            $this->createDynamicEntityReader(),
            $this->createDynamicEntityUpdateWriter(),
            $this->createDynamicEntityMapper(),
            $this->createTransactionProcessor(),
            $this->getDynamicEntityPostUpdatePlugins(),
        );
    }

    public function createDynamicEntityCreateWriter(): DynamicEntityWriterInterface
    {
        return new DynamicEntityWriter(
            $this->getEntityManager(),
            $this->createDynamicEntityComprehensiveCreateValidator(),
            $this->createDynamicEntityFieldCreationFilter(),
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityMapper(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    public function createDynamicEntityUpdateWriter(): DynamicEntityWriterInterface
    {
        return new DynamicEntityWriter(
            $this->getEntityManager(),
            $this->createDynamicEntityComprehensiveUpdateValidator(),
            $this->createDynamicEntityFieldUpdateFilter(),
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityMapper(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    public function createDynamicEntityComprehensiveCreateValidator(): DynamicEntityComprehensiveValidatorInterface
    {
        return new DynamicEntityComprehensiveValidator(
            $this->createDynamicEntityConfigurationValidator(),
            $this->createDynamicEntityConfigurationTreeValidator(),
            $this->createDynamicEntityCreateValidator(),
        );
    }

    public function createDynamicEntityComprehensiveUpdateValidator(): DynamicEntityComprehensiveValidatorInterface
    {
        return new DynamicEntityComprehensiveValidator(
            $this->createDynamicEntityConfigurationValidator(),
            $this->createDynamicEntityConfigurationTreeValidator(),
            $this->createDynamicEntityUpdateValidator(),
        );
    }

    public function createDynamicEntityCreateValidator(): DynamicEntityValidatorInterface
    {
        return new DynamicEntityValidator(
            $this->getDynamicEntityCreateValidators(),
        );
    }

    public function createDynamicEntityUpdateValidator(): DynamicEntityValidatorInterface
    {
        return new DynamicEntityValidator(
            $this->getDynamicEntityUpdateValidators(),
        );
    }

    public function createDynamicEntityCollectionRequestBuilder(): DynamicEntityCollectionRequestBuilderInterface
    {
        return new DynamicEntityCollectionRequestBuilder(
            $this->createDynamicEntityMapper(),
        );
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface>
     */
    public function getDynamicEntityCreateValidators(): array
    {
        return [
            $this->createEditableRelationValidator(),
            $this->createRequestFieldValidator(),
            $this->createRequiredFieldValidator(),
            $this->createIntegerFieldTypeValidator(),
            $this->createStringFieldTypeValidator(),
            $this->createBooleanFieldTypeValidator(),
            $this->createDecimalFeildTypeValidator(),
            $this->createCreationFieldImmutableValidator(),
            $this->createConstraintValidator(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface>
     */
    public function getDynamicEntityUpdateValidators(): array
    {
        return [
            $this->createEditableRelationValidator(),
            $this->createRequiredFieldValidator(),
            $this->createRequestFieldValidator(),
            $this->createIntegerFieldTypeValidator(),
            $this->createStringFieldTypeValidator(),
            $this->createBooleanFieldTypeValidator(),
            $this->createDecimalFeildTypeValidator(),
            $this->createUpdateFieldImmutableValidator(),
            $this->createConstraintValidator(),
        ];
    }

    public function createRequiredFieldValidator(): DynamicEntityValidatorInterface
    {
        return new RequiredFieldValidator(
            $this->createDynamicEntityErrorPathResolver(),
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createConstraintValidator(): DynamicEntityValidatorInterface
    {
        return new ConstraintValidator(
            $this->createDynamicEntityErrorPathResolver(),
            $this->getFieldsValidationConstraints(),
        );
    }

    public function createRequestFieldValidator(): DynamicEntityValidatorInterface
    {
        return new RequestFieldValidator(
            $this->createDynamicEntityErrorPathResolver(),
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createEditableRelationValidator(): DynamicEntityValidatorInterface
    {
        return new EditableRelationValidator(
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createIntegerFieldTypeValidator(): DynamicEntityValidatorInterface
    {
        return new IntegerFieldTypeValidator(
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    public function createStringFieldTypeValidator(): DynamicEntityValidatorInterface
    {
        return new StringFieldTypeValidator(
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    public function createBooleanFieldTypeValidator(): DynamicEntityValidatorInterface
    {
        return new BooleanFieldTypeValidator(
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    public function createDecimalFeildTypeValidator(): DynamicEntityValidatorInterface
    {
        return new DecimalFieldTypeValidator(
            $this->createDynamicEntityIndexer(),
            $this->createDynamicEntityErrorPathResolver(),
        );
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntityExtension\Dependency\Plugin\DynamicEntityPostCreatePluginInterface>
     */
    public function getDynamicEntityPostCreatePlugins(): array
    {
        return $this->getProvidedDependency(DynamicEntityDependencyProvider::PLUGINS_DYNAMIC_ENTITY_POST_CREATE);
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntityExtension\Dependency\Plugin\DynamicEntityPostUpdatePluginInterface>
     */
    public function getDynamicEntityPostUpdatePlugins(): array
    {
        return $this->getProvidedDependency(DynamicEntityDependencyProvider::PLUGINS_DYNAMIC_ENTITY_POST_UPDATE);
    }

    public function createDynamicEntityInstaller(): DynamicEntityInstallerInterface
    {
        return new DynamicEntityInstaller(
            $this->getConfig(),
            $this->getRepository(),
            $this->getEntityManager(),
            $this->createDynamicEntityMapper(),
            $this->createFieldMappingValidator(),
            $this->createDynamicEntityConfigurationColumnDetailProvider(),
        );
    }

    public function createFieldMappingValidator(): FieldMappingValidatorInterface
    {
        return new FieldMappingValidator(
            $this->getPropelDatabaseMap(),
        );
    }

    public function getPropelDatabaseMap(): DatabaseMap
    {
        return Propel::getDatabaseMap();
    }

    public function createDynamicEntityConfigurationCreator(): DynamicEntityConfigurationCreatorInterface
    {
        return new DynamicEntityConfigurationCreator(
            $this->createDynamicEntityConfigurationValidator(),
            $this->getEntityManager(),
            $this->createDynamicEntityConfigurationColumnDetailProvider(),
        );
    }

    public function createDynamicEntityConfigurationColumnDetailProvider(): DynamicEntityConfigurationColumnDetailProviderInterface
    {
        return new DynamicEntityConfigurationColumnDetailProvider(
            $this->getConfig(),
        );
    }

    public function createDynamicEntityConfigurationUpdater(): DynamicEntityConfigurationUpdaterInterface
    {
        return new DynamicEntityConfigurationUpdater(
            $this->createDynamicEntityConfigurationValidator(),
            $this->getEntityManager(),
        );
    }

    public function createDynamicEntityConfigurationValidator(): DynamicEntityConfigurationValidatorInterface
    {
        return new DynamicEntityConfigurationValidator(
            $this->getConfigurationValidatorRules(),
            $this->getDefinitionValidatorRules(),
        );
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Business\Validator\Rules\ValidatorRuleInterface>
     */
    public function getConfigurationValidatorRules(): array
    {
        return [
            $this->createAllowedTablesValidatorRule(),
            $this->createUniqueTableNameAliasValidatorRule(),
            $this->createResourceNameValidatorRule(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Business\Validator\Rules\ValidatorRuleInterface>
     */
    public function getDefinitionValidatorRules(): array
    {
        return [
            $this->createFieldTypeBooleanValidatorRule(),
            $this->createFieldTypeDecimalValidatorRule(),
            $this->createFieldTypeIntegerValidatorRule(),
            $this->createFieldTypeStringValidatorRule(),
            $this->createRequiredFieldsValidatorRule(),
        ];
    }

    public function createAllowedTablesValidatorRule(): ValidatorRuleInterface
    {
        return new AllowedTablesValidatorRule(
            $this->getConfig(),
            $this->createDisallowedTablesReader(),
        );
    }

    public function createRequiredFieldsValidatorRule(): ValidatorRuleInterface
    {
        return new RequiredFieldsValidatorRule();
    }

    public function createUniqueTableNameAliasValidatorRule(): ValidatorRuleInterface
    {
        return new UniqueTableNameAliasValidatorRule(
            $this->getRepository(),
        );
    }

    public function createDynamicEntityMapper(): DynamicEntityMapperInterface
    {
        return new DynamicEntityMapper(
            $this->createPostEditRequestExpander(),
        );
    }

    public function createFieldTypeBooleanValidatorRule(): ValidatorRuleInterface
    {
        return new FieldTypeBooleanValidatorRule();
    }

    public function createFieldTypeDecimalValidatorRule(): ValidatorRuleInterface
    {
        return new FieldTypeDecimalValidatorRule();
    }

    public function createFieldTypeIntegerValidatorRule(): ValidatorRuleInterface
    {
        return new FieldTypeIntegerValidatorRule();
    }

    public function createFieldTypeStringValidatorRule(): ValidatorRuleInterface
    {
        return new FieldTypeStringValidatorRule();
    }

    public function createResourceNameValidatorRule(): ValidatorRuleInterface
    {
        return new ResourceNameValidatorRule();
    }

    public function getConnection(): DynamicEntityToConnectionInterface
    {
        return $this->getProvidedDependency(DynamicEntityDependencyProvider::CONNECTION);
    }

    public function createDisallowedTablesReader(): DisallowedTablesReaderInterface
    {
        return new DisallowedTablesReader(
            $this->getConfig(),
        );
    }

    public function createDynamicEntityRelationConfigurationTreeBuilder(): DynamicEntityRelationConfigurationTreeBuilderInterface
    {
        return new DynamicEntityRelationConfigurationTreeBuilder();
    }

    public function createDynamicEntityConfigurationTreeValidator(): DynamicEntityConfigurationTreeValidatorInterface
    {
        return new DynamicEntityConfigurationTreeValidator(
            $this->createDynamicEntityMapper(),
        );
    }

    public function createDynamicEntityIndexer(): DynamicEntityIndexerInterface
    {
        return new DynamicEntityIndexer();
    }

    public function createPostEditRequestExpander(): DynamicEntityPostEditRequestExpanderInterface
    {
        return new DynamicEntityPostEditRequestExpander(
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createDynamicEntityErrorPathResolver(): DynamicEntityErrorPathResolverInterface
    {
        return new DynamicEntityErrorPathResolver();
    }

    public function createDynamicEntityDeleter(): DynamicEntityDeleterInterface
    {
        return new DynamicEntityDeleter(
            $this->getEntityManager(),
            $this->createDynamicEntityMapper(),
            $this->createDynamicEntityReader(),
        );
    }

    public function createCreationFieldImmutableValidator(): DynamicEntityValidatorInterface
    {
        return new CreationFieldImmutabilityValidator(
            $this->createDynamicEntityErrorPathResolver(),
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createUpdateFieldImmutableValidator(): DynamicEntityValidatorInterface
    {
        return new UpdateFieldImmutabilityValidator(
            $this->createDynamicEntityErrorPathResolver(),
            $this->createDynamicEntityIndexer(),
        );
    }

    public function createDynamicEntityFieldCreationFilter(): DynamicEntityFilterInterface
    {
        return new DynamicEntityFieldCreationFilter();
    }

    public function createDynamicEntityFieldUpdateFilter(): DynamicEntityFilterInterface
    {
        return new DynamicEntityFieldUpdateFilter();
    }

    public function createTransactionProcessor(): TransactionProcessorInterface
    {
        return new TransactionProcessor(
            $this->getConnection(),
        );
    }

    public function createUrlConstraint(): ConstraintInterface
    {
        return new UrlConstraint();
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\Constraint\ConstraintInterface>
     */
    public function getFieldsValidationConstraints(): array
    {
        return [
            $this->createUrlConstraint(),
        ];
    }
}
