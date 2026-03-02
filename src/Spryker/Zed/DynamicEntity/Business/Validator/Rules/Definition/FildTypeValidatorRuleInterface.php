<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Validator\Rules\Definition;

interface FildTypeValidatorRuleInterface
{
    /**
     * @return array<string>
     */
    public function getAllowedValidationFields(): array;

    public function getFieldType(): string;
}
