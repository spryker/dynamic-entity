<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence\Resetter;

use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

interface DynamicEntityResetterInterface
{
    public function resetNotProvidedFields(
        ActiveRecordInterface $activeRecord,
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): ActiveRecordInterface;
}
