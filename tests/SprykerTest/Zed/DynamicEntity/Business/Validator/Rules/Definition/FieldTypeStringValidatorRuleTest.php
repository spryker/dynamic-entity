<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business\Validator\Rules\Definition;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldValidationTransfer;
use Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition\FieldTypeStringValidatorRule;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group Validator
 * @group Rules
 * @group Definition
 * @group FieldTypeStringValidatorRuleTest
 * Add your own group annotations below this line
 */
class FieldTypeStringValidatorRuleTest extends Unit
{
    /**
     * @var string
     */
    protected const FOO_FIELD_NAME = 'foo';

    /**
     * @var \SprykerTest\Zed\DynamicEntity\DynamicEntityBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testValidateEmptyCollectionWillReturnWithoutErrors(): void
    {
        // Arrange
        $validatorRule = new FieldTypeStringValidatorRule();
        $dynamicEntityConfigurationTransfers = new ArrayObject();

        // Act
        $errorCollectionTransfer = $validatorRule->validate($dynamicEntityConfigurationTransfers);

        // Assert
        $this->assertCount(0, $errorCollectionTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testValidateCollectionWillReturnWithoutErrors(): void
    {
        // Arrange
        $validatorRule = new FieldTypeStringValidatorRule();
        $dynamicEntityConfigurationCollectionRequestTransfer = $this->tester->createDynamicEntityConfigurationCollectionRequestTransfer();

        // Act
        $errorCollectionTransfer = $validatorRule->validate($dynamicEntityConfigurationCollectionRequestTransfer->getDynamicEntityConfigurations());

        // Assert
        $this->assertCount(0, $errorCollectionTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testValidateCollectionWithOneFieldWillReturnWithoutErrors(): void
    {
        // Arrange
        $validatorRule = new FieldTypeStringValidatorRule();
        $dynamicEntityFieldDefinitionTransfer = (new DynamicEntityFieldDefinitionTransfer())
            ->setFieldName(static::FOO_FIELD_NAME)
            ->setFieldVisibleName(static::FOO_FIELD_NAME)
            ->setType('string')
            ->setIsCreatable(true)
            ->setIsEditable(true)
            ->setValidation(
                (new DynamicEntityFieldValidationTransfer())
                    ->setIsRequired(true)
                    ->setMinLength(10)
                    ->setMaxLength(100),
            );

        $dynamicEntityConfigurationCollectionRequestTransfer = $this->tester->createDynamicEntityConfigurationCollectionRequestTransferByDynamicEntityFieldDefinitionTransfer($dynamicEntityFieldDefinitionTransfer);

        // Act
        $errorCollectionTransfer = $validatorRule->validate($dynamicEntityConfigurationCollectionRequestTransfer->getDynamicEntityConfigurations());

        // Assert
        $this->assertCount(0, $errorCollectionTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testValidateCollectionWithOneFieldWillReturnWithIsRequiredError(): void
    {
        // Arrange
        $validatorRule = new FieldTypeStringValidatorRule();
        $dynamicEntityFieldDefinitionTransfer = (new DynamicEntityFieldDefinitionTransfer())
            ->setFieldName(static::FOO_FIELD_NAME)
            ->setFieldVisibleName(static::FOO_FIELD_NAME)
            ->setType('string')
            ->setIsCreatable(true)
            ->setIsEditable(true)
            ->setValidation(
                (new DynamicEntityFieldValidationTransfer())
                    ->setMin(100)
                    ->setMax(20),
            );

        $dynamicEntityConfigurationCollectionRequestTransfer = $this->tester->createDynamicEntityConfigurationCollectionRequestTransferByDynamicEntityFieldDefinitionTransfer($dynamicEntityFieldDefinitionTransfer);

        // Act
        $errorCollectionTransfer = $validatorRule->validate($dynamicEntityConfigurationCollectionRequestTransfer->getDynamicEntityConfigurations());

        // Assert
        $this->assertCount(1, $errorCollectionTransfer->getErrors());
        $this->assertSame('Validation rule(s): `min,max` is not allowed for field type `string`.', $errorCollectionTransfer->getErrors()[0]->getMessage());
        $this->assertSame(static::FOO_FIELD_NAME, $errorCollectionTransfer->getErrors()[0]->getEntityIdentifier());
    }

    /**
     * @return void
     */
    public function testValidateCollectionWithOneFieldWillReturnWithCompareError(): void
    {
        // Arrange
        $validatorRule = new FieldTypeStringValidatorRule();
        $dynamicEntityFieldDefinitionTransfer = (new DynamicEntityFieldDefinitionTransfer())
            ->setFieldName(static::FOO_FIELD_NAME)
            ->setFieldVisibleName(static::FOO_FIELD_NAME)
            ->setType('string')
            ->setIsCreatable(true)
            ->setIsEditable(true)
            ->setValidation(
                (new DynamicEntityFieldValidationTransfer())
                    ->setIsRequired(true)
                    ->setMinLength(100)
                    ->setMaxLength(20),
            );

        $dynamicEntityConfigurationCollectionRequestTransfer = $this->tester->createDynamicEntityConfigurationCollectionRequestTransferByDynamicEntityFieldDefinitionTransfer($dynamicEntityFieldDefinitionTransfer);

        // Act
        $errorCollectionTransfer = $validatorRule->validate($dynamicEntityConfigurationCollectionRequestTransfer->getDynamicEntityConfigurations());

        // Assert
        $this->assertCount(1, $errorCollectionTransfer->getErrors());
        $this->assertSame('Validation setting `minLength` must be less than `maxLength` for `string` field type.', $errorCollectionTransfer->getErrors()[0]->getMessage());
        $this->assertSame(static::FOO_FIELD_NAME, $errorCollectionTransfer->getErrors()[0]->getEntityIdentifier());
    }
}
