<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Spryker\Zed\DynamicEntity\Business\DynamicEntityBusinessFactory;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group Validator
 * @group DynamicEntityConfigurationTreeValidatorTest
 * Add your own group annotations below this line
 */
class DynamicEntityConfigurationTreeValidatorTest extends Unit
{
    /**
     * @var string
     */
    protected const ERROR_MESSAGE_RELATION_NOT_FOUND = 'dynamic_entity.validation.relation_not_found';

    /**
     * @var string
     */
    protected const TABLE_ALIAS = 'test-table';

    /**
     * @var \SprykerTest\Zed\DynamicEntity\DynamicEntityBusinessTester
     */
    protected $tester;

    public function testValidateReturnsInvalidRelationTypeErrorWhenRelationArrayContainsScalar(): void
    {
        // Arrange
        $validator = (new DynamicEntityBusinessFactory())->createDynamicEntityConfigurationTreeValidator();

        $request = (new DynamicEntityCollectionRequestTransfer())
            ->setTableAlias(static::TABLE_ALIAS)
            ->addDynamicEntity(
                (new DynamicEntityTransfer())
                    ->setFields(['name' => 'foo', 'children' => [1233]]),
            );

        // Act
        $error = $validator->validateDynamicEntityCollectionRequestByDynamicEntityConfigurationCollection(
            $request,
            $this->createConfigCollection(),
        );

        // Assert
        $this->assertNotNull($error);
        $this->assertSame(static::ERROR_MESSAGE_RELATION_NOT_FOUND, $error->getMessage());
        $this->assertSame('children', $error->getParameters()['%relationName%']);
    }

    public function testValidateReturnsInvalidRelationTypeErrorWhenRelationValueIsObjectWithScalarValues(): void
    {
        // Arrange
        $validator = (new DynamicEntityBusinessFactory())->createDynamicEntityConfigurationTreeValidator();

        // Simulates JSON {"children": {"wee": 12}} — a plain object instead of an array of child objects
        $request = (new DynamicEntityCollectionRequestTransfer())
            ->setTableAlias(static::TABLE_ALIAS)
            ->addDynamicEntity(
                (new DynamicEntityTransfer())
                    ->setFields(['name' => 'foo', 'children' => ['wee' => 12]]),
            );

        // Act
        $error = $validator->validateDynamicEntityCollectionRequestByDynamicEntityConfigurationCollection(
            $request,
            $this->createConfigCollection(),
        );

        // Assert
        $this->assertNotNull($error);
        $this->assertSame(static::ERROR_MESSAGE_RELATION_NOT_FOUND, $error->getMessage());
        $this->assertSame('children', $error->getParameters()['%relationName%']);
    }

    public function testValidateReturnsNullForRequestWithNoRelationalFields(): void
    {
        // Arrange — entity contains only scalar fields, no relations
        $validator = (new DynamicEntityBusinessFactory())->createDynamicEntityConfigurationTreeValidator();

        $request = (new DynamicEntityCollectionRequestTransfer())
            ->setTableAlias(static::TABLE_ALIAS)
            ->addDynamicEntity(
                (new DynamicEntityTransfer())
                    ->setFields(['name' => 'foo']),
            );

        // Act
        $error = $validator->validateDynamicEntityCollectionRequestByDynamicEntityConfigurationCollection(
            $request,
            $this->createConfigCollection(),
        );

        // Assert
        $this->assertNull($error);
    }

    protected function createConfigCollection(): DynamicEntityConfigurationCollectionTransfer
    {
        $definition = (new DynamicEntityDefinitionTransfer())
            ->setIdentifier('id')
            ->addFieldDefinition(
                (new DynamicEntityFieldDefinitionTransfer())
                    ->setFieldName('name')
                    ->setFieldVisibleName('name'),
            );

        return (new DynamicEntityConfigurationCollectionTransfer())
            ->addDynamicEntityConfiguration(
                (new DynamicEntityConfigurationTransfer())
                    ->setTableAlias(static::TABLE_ALIAS)
                    ->setDynamicEntityDefinition($definition),
            );
    }
}
