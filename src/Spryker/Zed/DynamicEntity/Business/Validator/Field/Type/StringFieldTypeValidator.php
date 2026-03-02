<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Validator\Field\Type;

use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Spryker\Zed\DynamicEntity\Business\Validator\DynamicEntityValidatorInterface;

class StringFieldTypeValidator extends AbstractFieldTypeValidator implements DynamicEntityValidatorInterface
{
    /**
     * @var string
     */
    protected const STRING_FIELD_TYPE = 'string';

    public function isValidType(mixed $fieldValue): bool
    {
        return is_string($fieldValue) === true;
    }

    public function isValidValue(mixed $fieldValue, DynamicEntityFieldDefinitionTransfer $dynamicEntityFieldDefinitionTransfer): bool
    {
        if ($dynamicEntityFieldDefinitionTransfer->getValidation() === null) {
            return true;
        }

        $validation = $dynamicEntityFieldDefinitionTransfer->getValidation();

        if ($validation->getMinLength() !== null && strlen($fieldValue) < $validation->getMinLength()) {
            return false;
        }

        if ($validation->getMaxLength() !== null && strlen($fieldValue) > $validation->getMaxLength()) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return static::STRING_FIELD_TYPE;
    }
}
