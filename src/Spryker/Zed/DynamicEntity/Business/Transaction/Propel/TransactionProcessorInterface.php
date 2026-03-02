<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Transaction\Propel;

use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer;

interface TransactionProcessorInterface
{
    public function startPerItemTransaction(DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer): bool;

    public function endPerItemTransaction(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer
    ): bool;

    public function startAtomicTransaction(DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer): bool;

    public function endAtomicTransaction(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        DynamicEntityCollectionResponseTransfer $dynamicEntityCollectionResponseTransfer
    ): bool;
}
