<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Validator\Field\Completeness\Constraint;

use Generated\Shared\Transfer\DynamicEntityFieldDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;

interface ConstraintInterface
{
    public function isApplicable(string $constraintName): bool;

    public function isValid(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityFieldDefinitionTransfer $fieldDefinitionTransfer
    ): bool;

    public function getErrorMessage(): string;
}
