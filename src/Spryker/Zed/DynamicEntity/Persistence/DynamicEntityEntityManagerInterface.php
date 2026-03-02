<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence;

use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityCollectionResponseTransfer;
use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConditionsTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;

interface DynamicEntityEntityManagerInterface
{
    public function createDynamicEntityConfiguration(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer;

    public function updateDynamicEntityConfiguration(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityConfigurationTransfer;

    public function createDynamicEntity(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        string $errorPath
    ): DynamicEntityCollectionResponseTransfer;

    public function createChildDynamicEntity(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer,
        string $errorPath
    ): DynamicEntityCollectionResponseTransfer;

    public function updateDynamicEntity(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        DynamicEntityConditionsTransfer $dynamicEntityConditionsTransfer,
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        string $errorPath
    ): DynamicEntityCollectionResponseTransfer;

    public function updateChildDynamicEntity(
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationRelationTransfer $dynamicEntityConfigurationRelationTransfer,
        DynamicEntityConditionsTransfer $dynamicEntityConditionsTransfer,
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        string $errorPath
    ): DynamicEntityCollectionResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer $childDynamicEntityConfigurationCollectionTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $parentDynamicEntityConfigurationTransfer
     * @param array<string, array<string, mixed>> $indexedChildRelations
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer
     */
    public function createDynamicEntityConfigurationRelation(
        DynamicEntityConfigurationCollectionTransfer $childDynamicEntityConfigurationCollectionTransfer,
        DynamicEntityConfigurationTransfer $parentDynamicEntityConfigurationTransfer,
        array $indexedChildRelations
    ): DynamicEntityConfigurationCollectionTransfer;

    public function deleteDynamicEntity(
        DynamicEntityCollectionTransfer $dynamicEntityCollectionTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): DynamicEntityCollectionResponseTransfer;
}
