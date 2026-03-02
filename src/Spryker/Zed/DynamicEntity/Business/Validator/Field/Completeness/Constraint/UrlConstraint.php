<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\Constraint;

use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;

class UrlConstraint implements ConstraintInterface
{
    /**
     * @var string
     */
    protected const CONSTRAINT_URL = 'url';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_INVALID_URL = 'dynamic_entity.validation.invalid_url';

    /**
     * @var string
     */
    protected const RELATIVE_URL_PATTERN = '/^\/$|^\/[a-z0-9\-._~%!$&\'()*+,;=@]+(\/[a-zA-Z0-9\-._~%!$&\'()*+,;=:@]+)*/';

    public function isApplicable(string $constraintName): bool
    {
        return $constraintName === static::CONSTRAINT_URL;
    }

    public function isValid(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityFieldDefinitionTransfer $fieldDefinitionTransfer
    ): bool {
        return preg_match(static::RELATIVE_URL_PATTERN, $dynamicEntityTransfer->getFields()[$fieldDefinitionTransfer->getFieldVisibleName()]) === 1;
    }

    public function getErrorMessage(): string
    {
        return static::GLOSSARY_KEY_INVALID_URL;
    }
}
