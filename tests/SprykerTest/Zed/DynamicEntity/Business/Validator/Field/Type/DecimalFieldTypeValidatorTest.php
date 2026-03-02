<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business\Validator\Field\Type;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldValidationTransfer;
use Spryker\Zed\DynamicEntity\Business\Indexer\DynamicEntityIndexerInterface;
use Spryker\Zed\DynamicEntity\Business\Resolver\DynamicEntityErrorPathResolverInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface;
use Spryker\Zed\DynamicEntity\Business\Validator\Field\Type\DecimalFieldTypeValidator;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group Validator
 * @group Field
 * @group Type
 * @group DecimalFieldTypeValidatorTest
 * Add your own group annotations below this line
 */
class DecimalFieldTypeValidatorTest extends Unit
{
    public function testWillReturnValidatorType(): void
    {
        // Arrange
        $validator = $this->createDecimalFieldTypeValidator();

        // Act & Assert
        $this->assertSame('decimal', $validator->getType());
    }

    public function testValidTypeWillReturnTrue(): void
    {
        // Arrange
        $validator = $this->createDecimalFieldTypeValidator();

        // Act & Assert
        $this->assertTrue($validator->isValidType('0.0'));
        $this->assertTrue($validator->isValidType('-1.1'));
        $this->assertTrue($validator->isValidType('9999999'));
        $this->assertTrue($validator->isValidType(1233));
    }

    public function testValidTypeWillReturnFalse(): void
    {
        // Arrange
        $validator = $this->createDecimalFieldTypeValidator();

        // Act & Assert
        $this->assertFalse($validator->isValidType('string'));
        $this->assertFalse($validator->isValidType(false));
        $this->assertFalse($validator->isValidType('string.9999999'));
    }

    public function testValidValueWithValidationRulesWillReturnTrue(): void
    {
        // Arrange
        $validator = $this->createDecimalFieldTypeValidator();
        $dynamicEntityFieldDefinitionTransfer = (new DynamicEntityFieldDefinitionTransfer())->setValidation(
            (new DynamicEntityFieldValidationTransfer())->setPrecision(5)->setScale(2),
        );

        // Act & Assert
        $this->assertTrue($validator->isValidValue(0, $dynamicEntityFieldDefinitionTransfer));
        $this->assertTrue($validator->isValidValue('1', $dynamicEntityFieldDefinitionTransfer));
        $this->assertTrue($validator->isValidValue('0', $dynamicEntityFieldDefinitionTransfer));
        $this->assertTrue($validator->isValidValue('123.55', $dynamicEntityFieldDefinitionTransfer));
        $this->assertTrue($validator->isValidValue('0.0000', $dynamicEntityFieldDefinitionTransfer));
    }

    protected function createDecimalFieldTypeValidator(): DynamicEntityValidatorInterface
    {
        return new DecimalFieldTypeValidator(
            $this->createDynamicEntityIndexerMock(),
            $this->createDynamicEntityErrorPathResolverMock(),
        );
    }

    protected function createDynamicEntityIndexerMock(): DynamicEntityIndexerInterface
    {
        return $this->getMockBuilder(DynamicEntityIndexerInterface::class)->getMock();
    }

    protected function createDynamicEntityErrorPathResolverMock(): DynamicEntityErrorPathResolverInterface
    {
        return $this->getMockBuilder(DynamicEntityErrorPathResolverInterface::class)->getMock();
    }
}
