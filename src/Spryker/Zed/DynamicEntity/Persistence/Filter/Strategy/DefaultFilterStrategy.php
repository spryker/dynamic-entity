<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;

class DefaultFilterStrategy implements FilterStrategyInterface
{
    public function isApplicable(?string $fieldValue): bool
    {
        return true;
    }

    public function applyConditionToQuery(
        ModelCriteria $query,
        string $fieldConditionName,
        ?string $fieldValue
    ): ModelCriteria {
        return $query->filterBy($fieldConditionName, $fieldValue, Criteria::EQUAL);
    }
}
