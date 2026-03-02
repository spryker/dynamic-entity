<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Reader;

use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityCriteriaTransfer;
use Spryker\Zed\DynamicEntity\Business\Configuration\DynamicEntityConfigurationResponseInterface;

interface DynamicEntityReaderInterface
{
    public function getEntityCollection(DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer): DynamicEntityCollectionTransfer;

    public function getDynamicEntityConfigurationTransferTree(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
    ): DynamicEntityConfigurationResponseInterface;
}
