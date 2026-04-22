<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business\Builder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityConditionsTransfer;
use Generated\Shared\Transfer\DynamicEntityCriteriaTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldConditionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Map\DatabaseMap;
use Spryker\Zed\DynamicEntity\Persistence\Builder\DynamicEntityQueryBuilder;
use Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\DefaultFilterStrategy;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group Builder
 * @group DynamicEntityQueryBuilderTest
 * Add your own group annotations below this line
 */
class DynamicEntityQueryBuilderTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\DynamicEntity\DynamicEntityBusinessTester
     */
    protected $tester;

    public function testBuildQueryWithFieldConditionsResolvesVisibleNameToActualColumnName(): void
    {
        // Arrange — field has visible name alias 'test' but the real DB column is 'sku'
        $definitionTransfer = $this->createDefinitionWithAlias('sku', 'test');
        $criteriaTransfer = $this->createCriteriaWithCondition('test', '002');

        $queryMock = $this->createMock(ModelCriteria::class);
        $queryMock->expects($this->once())
            ->method('filterBy')
            ->with('Sku', '002', Criteria::EQUAL)
            ->willReturn($queryMock);

        // Act & Assert
        $this->createQueryBuilder()->buildQueryWithFieldConditions($queryMock, $criteriaTransfer, $definitionTransfer);
    }

    public function testBuildQueryWithFieldConditionsPassesColumnNameDirectlyWhenNoAliasIsSet(): void
    {
        // Arrange — fieldVisibleName equals fieldName (no alias configured)
        $definitionTransfer = $this->createDefinitionWithAlias('sku', 'sku');
        $criteriaTransfer = $this->createCriteriaWithCondition('sku', '002');

        $queryMock = $this->createMock(ModelCriteria::class);
        $queryMock->expects($this->once())
            ->method('filterBy')
            ->with('Sku', '002', Criteria::EQUAL)
            ->willReturn($queryMock);

        // Act & Assert
        $this->createQueryBuilder()->buildQueryWithFieldConditions($queryMock, $criteriaTransfer, $definitionTransfer);
    }

    public function testBuildQueryWithFieldConditionsSkipsConditionForUnknownVisibleName(): void
    {
        // Arrange — condition uses a name not present in field definitions
        $definitionTransfer = $this->createDefinitionWithAlias('sku', 'test');
        $criteriaTransfer = $this->createCriteriaWithCondition('unknown_field', '002');

        $queryMock = $this->createMock(ModelCriteria::class);
        $queryMock->expects($this->never())->method('filterBy');

        // Act & Assert
        $this->createQueryBuilder()->buildQueryWithFieldConditions($queryMock, $criteriaTransfer, $definitionTransfer);
    }

    protected function createDefinitionWithAlias(string $fieldName, string $fieldVisibleName): DynamicEntityDefinitionTransfer
    {
        $fieldDefinition = (new DynamicEntityFieldDefinitionTransfer())
            ->setFieldName($fieldName)
            ->setFieldVisibleName($fieldVisibleName)
            ->setType('string');

        return (new DynamicEntityDefinitionTransfer())
            ->setIdentifier($fieldName)
            ->addFieldDefinition($fieldDefinition);
    }

    protected function createCriteriaWithCondition(string $conditionName, string $value): DynamicEntityCriteriaTransfer
    {
        $conditionsTransfer = (new DynamicEntityConditionsTransfer())
            ->addFieldCondition(
                (new DynamicEntityFieldConditionTransfer())
                    ->setName($conditionName)
                    ->setValue($value),
            );

        return (new DynamicEntityCriteriaTransfer())
            ->setDynamicEntityConditions($conditionsTransfer);
    }

    protected function createQueryBuilder(): DynamicEntityQueryBuilder
    {
        return new DynamicEntityQueryBuilder(
            $this->createMock(DatabaseMap::class),
            [new DefaultFilterStrategy()],
        );
    }
}
