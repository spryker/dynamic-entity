<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business\Mapper;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityRelationFieldMappingTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Spryker\Zed\DynamicEntity\Business\Expander\DynamicEntityPostEditRequestExpanderInterface;
use Spryker\Zed\DynamicEntity\Business\Mapper\DynamicEntityMapper;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group Mapper
 * @group DynamicEntityMapperTest
 * Add your own group annotations below this line
 */
class DynamicEntityMapperTest extends Unit
{
    /**
     * @var string
     */
    protected const RELATION_NAME = 'salesOrderShippingAddress';

    /**
     * @var string
     */
    protected const PARENT_FIELD_NAME = 'fk_sales_order_address_shipping';

    /**
     * @var string
     */
    protected const CHILD_FIELD_NAME = 'id_sales_order_address';

    public function testMapChildDynamicEntityCollectionTransferToDynamicEntityCollectionTransferSkipsRelationWhenParentForeignKeyIsNull(): void
    {
        // Arrange
        $dynamicEntityMapper = new DynamicEntityMapper(
            $this->createMock(DynamicEntityPostEditRequestExpanderInterface::class),
        );

        $dynamicEntityWithAddressTransfer = (new DynamicEntityTransfer())->setFields([
            'id_sales_order' => 1,
            static::PARENT_FIELD_NAME => 5,
        ]);
        $dynamicEntityWithoutAddressTransfer = (new DynamicEntityTransfer())->setFields([
            'id_sales_order' => 2,
            static::PARENT_FIELD_NAME => null,
        ]);

        $dynamicEntityCollectionTransfer = (new DynamicEntityCollectionTransfer())
            ->addDynamicEntity($dynamicEntityWithAddressTransfer)
            ->addDynamicEntity($dynamicEntityWithoutAddressTransfer);

        $childDynamicEntityTransfer = (new DynamicEntityTransfer())->setFields([
            static::CHILD_FIELD_NAME => 5,
        ]);
        $childDynamicEntityCollectionTransfer = (new DynamicEntityCollectionTransfer())
            ->addDynamicEntity($childDynamicEntityTransfer);

        $dynamicEntityConfigurationRelationTransfer = (new DynamicEntityConfigurationRelationTransfer())
            ->setName(static::RELATION_NAME)
            ->addRelationFieldMapping(
                (new DynamicEntityRelationFieldMappingTransfer())
                    ->setParentFieldName(static::PARENT_FIELD_NAME)
                    ->setChildFieldName(static::CHILD_FIELD_NAME),
            );

        // Act
        $mappedDynamicEntityCollectionTransfer = $dynamicEntityMapper->mapChildDynamicEntityCollectionTransferToDynamicEntityCollectionTransfer(
            $dynamicEntityCollectionTransfer,
            $childDynamicEntityCollectionTransfer,
            $dynamicEntityConfigurationRelationTransfer,
        );

        // Assert
        $mappedDynamicEntities = array_values($mappedDynamicEntityCollectionTransfer->getDynamicEntities()->getArrayCopy());

        $this->assertCount(
            1,
            $mappedDynamicEntities[0]->getChildRelations(),
            'Entity with a populated foreign key must keep its relation.',
        );
        $this->assertCount(
            0,
            $mappedDynamicEntities[1]->getChildRelations(),
            'Entity with a NULL foreign key must have the relation omitted instead of raising a TypeError.',
        );
    }

    public function testGetForeignKeysGroupedByChildFileldNameExcludesEntitiesWithNullParentForeignKey(): void
    {
        // Arrange
        $dynamicEntityMapper = new DynamicEntityMapper(
            $this->createMock(DynamicEntityPostEditRequestExpanderInterface::class),
        );

        $dynamicEntityWithAddressTransfer = (new DynamicEntityTransfer())->setFields([
            'id_sales_order' => 1,
            static::PARENT_FIELD_NAME => 5,
        ]);
        $dynamicEntityWithoutAddressTransfer = (new DynamicEntityTransfer())->setFields([
            'id_sales_order' => 2,
            static::PARENT_FIELD_NAME => null,
        ]);

        $dynamicEntityCollectionTransfer = (new DynamicEntityCollectionTransfer())
            ->addDynamicEntity($dynamicEntityWithAddressTransfer)
            ->addDynamicEntity($dynamicEntityWithoutAddressTransfer);

        $dynamicEntityConfigurationRelationTransfer = (new DynamicEntityConfigurationRelationTransfer())
            ->setName(static::RELATION_NAME)
            ->addRelationFieldMapping(
                (new DynamicEntityRelationFieldMappingTransfer())
                    ->setParentFieldName(static::PARENT_FIELD_NAME)
                    ->setChildFieldName(static::CHILD_FIELD_NAME),
            );

        // Act
        $foreignKeyFieldMappingArray = $dynamicEntityMapper->getForeignKeysGroupedByChildFileldName(
            $dynamicEntityConfigurationRelationTransfer,
            $dynamicEntityCollectionTransfer,
        );

        // Assert
        $this->assertSame(
            [5],
            $foreignKeyFieldMappingArray[static::CHILD_FIELD_NAME],
            'NULL foreign keys must not reach the IN() filter built from this array — they can never match a child row.',
        );
    }

    public function testGetForeignKeysGroupedByChildFileldNameKeepsChildFieldKeyWithEmptyArrayWhenEveryParentForeignKeyIsNull(): void
    {
        // Arrange
        $dynamicEntityMapper = new DynamicEntityMapper(
            $this->createMock(DynamicEntityPostEditRequestExpanderInterface::class),
        );

        $dynamicEntityWithoutAddressTransfer = (new DynamicEntityTransfer())->setFields([
            'id_sales_order' => 2,
            static::PARENT_FIELD_NAME => null,
        ]);

        $dynamicEntityCollectionTransfer = (new DynamicEntityCollectionTransfer())
            ->addDynamicEntity($dynamicEntityWithoutAddressTransfer);

        $dynamicEntityConfigurationRelationTransfer = (new DynamicEntityConfigurationRelationTransfer())
            ->setName(static::RELATION_NAME)
            ->addRelationFieldMapping(
                (new DynamicEntityRelationFieldMappingTransfer())
                    ->setParentFieldName(static::PARENT_FIELD_NAME)
                    ->setChildFieldName(static::CHILD_FIELD_NAME),
            );

        // Act
        $foreignKeyFieldMappingArray = $dynamicEntityMapper->getForeignKeysGroupedByChildFileldName(
            $dynamicEntityConfigurationRelationTransfer,
            $dynamicEntityCollectionTransfer,
        );

        // Assert
        $this->assertSame(
            [static::CHILD_FIELD_NAME => []],
            $foreignKeyFieldMappingArray,
            'The child field key must stay present with an empty array so the persistence layer still filters to "match nothing" instead of skipping the IN() filter entirely.',
        );
    }
}
