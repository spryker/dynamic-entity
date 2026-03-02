<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Business\Builder;

use ArrayObject;
use Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationRelationTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityCriteriaTransfer;

class DynamicEntityRelationConfigurationTreeBuilder implements DynamicEntityRelationConfigurationTreeBuilderInterface
{
    /**
     * @var string
     */
    protected const FIELD_CHILD = 'child';

    /**
     * @var string
     */
    protected const FIELD_RELATION_NAME = 'relationName';

    public function buildDynamicEntityConfigurationTransferTree(
        DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer,
        DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
    ): ?DynamicEntityConfigurationTransfer {
        $dynamicEntityConfigurationTransfer = $this->findDynamicEntityConfigurationEntityByTableAlias(
            $dynamicEntityConfigurationCollectionTransfer,
            $dynamicEntityCriteriaTransfer->getDynamicEntityConditionsOrFail()->getTableAliasOrFail(),
        );

        if ($dynamicEntityConfigurationTransfer === null) {
            return null;
        }

        $relationTrees = $this->buildRelationTrees($dynamicEntityCriteriaTransfer);
        if ($relationTrees === []) {
            return $dynamicEntityConfigurationTransfer;
        }

        foreach ($relationTrees as $relationTreeNode) {
            $dynamicEntityConfigurationTransfer = $this->processRelationTreeNode(
                $relationTreeNode,
                $dynamicEntityConfigurationTransfer,
                $dynamicEntityConfigurationCollectionTransfer,
            );

            if ($dynamicEntityConfigurationTransfer === null) {
                return null;
            }
        }

        return $dynamicEntityConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer
     *
     * @return array<int, array<mixed>>
     */
    protected function buildRelationTrees(DynamicEntityCriteriaTransfer $dynamicEntityCriteriaTransfer): array
    {
        $relationTrees = [];
        foreach ($dynamicEntityCriteriaTransfer->getRelationChains() as $relationChain) {
            $relationNamesFromChain = explode('.', trim($relationChain));

            $relationTrees[] = $this->buildNode($relationNamesFromChain);
        }

        return $relationTrees;
    }

    /**
     * @param array<string> $relationNamesChain
     *
     * @return array<mixed>
     */
    protected function buildNode(array $relationNamesChain): array
    {
        $currentRelationName = array_shift($relationNamesChain);

        $node = [
            static::FIELD_RELATION_NAME => $currentRelationName,
            static::FIELD_CHILD => $relationNamesChain === [] ? [] : $this->buildNode($relationNamesChain),
        ];

        return $node;
    }

    /**
     * @param array<mixed> $node
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer $currentDynamicEntityConfigurationTranfer
     * @param \Generated\Shared\Transfer\DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConfigurationTransfer|null
     */
    protected function processRelationTreeNode(
        array $node,
        DynamicEntityConfigurationTransfer $currentDynamicEntityConfigurationTranfer,
        DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
    ): ?DynamicEntityConfigurationTransfer {
        if ($node[static::FIELD_RELATION_NAME] === null) {
            return $currentDynamicEntityConfigurationTranfer;
        }

        $relationName = $node[static::FIELD_RELATION_NAME];

        $dynamicEntityConfigurationTransfer = $this->findDynamicEntityConfigurationTransferDefinition($currentDynamicEntityConfigurationTranfer, $dynamicEntityConfigurationCollectionTransfer);
        if ($dynamicEntityConfigurationTransfer === null) {
            return null;
        }

        $childRelationTransfer = $this->findDynamicEntityConfigurationRelationTransfer($relationName, $dynamicEntityConfigurationTransfer);
        if ($childRelationTransfer === null) {
            return null;
        }

        if ($node[static::FIELD_CHILD] !== []) {
            $childDynamicEntityConfigurationTransfer = $childRelationTransfer->getChildDynamicEntityConfiguration();
            if ($childDynamicEntityConfigurationTransfer === null) {
                return null;
            }

            $childDynamicEntityConfigurationTransfer = $this->processRelationTreeNode($node[static::FIELD_CHILD], $childDynamicEntityConfigurationTransfer, $dynamicEntityConfigurationCollectionTransfer);
            if ($childDynamicEntityConfigurationTransfer === null) {
                return null;
            }
        }

        $currentDynamicEntityConfigurationTranfer = $this->setChildRelationIfNotExists($currentDynamicEntityConfigurationTranfer, $childRelationTransfer);

        return $currentDynamicEntityConfigurationTranfer;
    }

    protected function findDynamicEntityConfigurationTransferDefinition(
        DynamicEntityConfigurationTransfer $currentDynamicEntityConfigurationTranfer,
        DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollectionTransfer
    ): ?DynamicEntityConfigurationTransfer {
        foreach ($dynamicEntityConfigurationCollectionTransfer->getDynamicEntityConfigurations() as $dynamicEntityConfigurationTransfer) {
            if ($dynamicEntityConfigurationTransfer->getTableAlias() === $currentDynamicEntityConfigurationTranfer->getTableAlias()) {
                return $dynamicEntityConfigurationTransfer;
            }
        }

        return null;
    }

    protected function findDynamicEntityConfigurationRelationTransfer(
        string $relationName,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): ?DynamicEntityConfigurationRelationTransfer {
        foreach ($dynamicEntityConfigurationTransfer->getChildRelations() as $childRelationTransfer) {
            if ($childRelationTransfer->getName() === $relationName) {
                return $childRelationTransfer;
            }
        }

        return null;
    }

    protected function setChildRelationIfNotExists(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer,
        DynamicEntityConfigurationRelationTransfer $childRelationTransfer
    ): DynamicEntityConfigurationTransfer {
        foreach ($dynamicEntityConfigurationTransfer->getChildRelations() as $childRelation) {
            if ($childRelation->getName() === $childRelationTransfer->getName()) {
                return $dynamicEntityConfigurationTransfer;
            }
        }

        $dynamicEntityConfigurationTransfer->addChildRelation($childRelationTransfer);

        return $dynamicEntityConfigurationTransfer;
    }

    protected function findDynamicEntityConfigurationEntityByTableAlias(
        DynamicEntityConfigurationCollectionTransfer $dynamicEntityConfigurationCollection,
        string $tableAlias
    ): ?DynamicEntityConfigurationTransfer {
        foreach ($dynamicEntityConfigurationCollection->getDynamicEntityConfigurations() as $dynamicEntityConfigurationTransfer) {
            if ($dynamicEntityConfigurationTransfer->getTableAlias() === $tableAlias) {
                $dynamicEntityConfigurationTransfer = $this->cloneConfigurationWithoutRelations($dynamicEntityConfigurationTransfer);

                return $dynamicEntityConfigurationTransfer;
            }
        }

        return null;
    }

    protected function cloneConfigurationWithoutRelations(
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTranfer
    ): DynamicEntityConfigurationTransfer {
        $clonedDynamicEntityConfigurationTranfer = clone $dynamicEntityConfigurationTranfer;
        $clonedDynamicEntityConfigurationTranfer->setChildRelations(new ArrayObject());

        return $clonedDynamicEntityConfigurationTranfer;
    }
}
