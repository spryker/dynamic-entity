<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Resolver;

interface DynamicEntityErrorPathResolverInterface
{
    public function getErrorPath(
        int $index,
        string $tableAlias,
        ?string $parentErrorPath = null
    ): string;
}
