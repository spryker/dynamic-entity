<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Mapper;

use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;

interface DynamicEntityMapperInterface
{
    /**
     * @param array<string, mixed> $dynamicEntityConfiguration
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer
     */
    public function mapDynamicEntityConfigurationToDynamicEntityConfigurationTransfer(
        array $dynamicEntityConfiguration,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer;

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionTransfer $childDynamicEntityCollectionTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionTransfer
     */
    public function mapChildDynamicEntityCollectionTransferToDynamicEntityCollectionTransfer(
        DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer,
        DynamicEntityCollectionTransfer $childDynamicEntityCollectionTransfer,
        DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer
    ): DynamicEntityCollectionTransfer;

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
     * @param array<string> $childMapping
     *
     * @return array<string>
     */
    public function getDynamicEntityConfigurationRelationMappedFields(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        array $childMapping
    ): array;

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer
     *
     * @return array<string, array<int|string>>
     */
    public function getForeignKeysGroupedByChildFileldName(
        DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer,
        DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer
    ): array;
}
