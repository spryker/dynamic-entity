<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Dependency\External;

interface DynamicEntityToConnectionInterface
{
    /**
     * @return bool
     */
    public function rollBack(): bool;

    /**
     * @return bool
     */
    public function commit(): bool;

    /**
     * @return bool
     */
    public function beginTransaction(): bool;
}
