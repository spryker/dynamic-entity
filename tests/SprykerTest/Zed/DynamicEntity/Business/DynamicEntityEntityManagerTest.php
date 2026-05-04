<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DynamicEntity\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DynamicEntityCollectionTransfer;
use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityDefinitionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfiguration;
use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfigurationRelation;
use Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManager;
use SprykerTest\Zed\DynamicEntity\DynamicEntityBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DynamicEntity
 * @group Business
 * @group DynamicEntityEntityManagerTest
 * Add your own group annotations below this line
 */
class DynamicEntityEntityManagerTest extends Unit
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_DELETE_FOREIGN_KEY_CONSTRAINT_FAILS = 'dynamic_entity.validation.delete_foreign_key_constraint_fails';

    /**
     * @var string
     */
    protected const TABLE_NAME = 'spy_dynamic_entity_configuration';

    /**
     * @var string
     */
    protected const TABLE_ALIAS = 'dynamic-entity-configurations';

    /**
     * @var string
     */
    protected const IDENTIFIER = 'id_dynamic_entity_configuration';

    /**
     * @var \SprykerTest\Zed\DynamicEntity\DynamicEntityBusinessTester
     */
    protected DynamicEntityBusinessTester $tester;

    /**
     * Bug: deleteDynamicEntity() returns early (after first exception) and only produces one error
     * even when multiple entities fail deletion with a foreign key constraint violation.
     *
     * Expected: one error per entity that fails.
     */
    public function testDeleteDynamicEntityCollectsOneErrorPerEntityWhenForeignKeyConstraintFails(): void
    {
        //Arrange
        $parentId1 = $this->createConfigurationEntityWithChildRelation();
        $parentId2 = $this->createConfigurationEntityWithChildRelation();

        $dynamicEntityConfigurationTransfer = $this->createDynamicEntityConfigurationTransfer();

        $dynamicEntityCollectionTransfer = (new DynamicEntityCollectionTransfer())
            ->addDynamicEntity((new DynamicEntityTransfer())->setIdentifier((string)$parentId1))
            ->addDynamicEntity((new DynamicEntityTransfer())->setIdentifier((string)$parentId2));

        $entityManager = new DynamicEntityEntityManager();

        //Act
        $response = $entityManager->deleteDynamicEntity($dynamicEntityCollectionTransfer, $dynamicEntityConfigurationTransfer);

        //Assert
        $this->assertCount(2, $response->getErrors());
        foreach ($response->getErrors() as $error) {
            $this->assertSame(static::GLOSSARY_KEY_DELETE_FOREIGN_KEY_CONSTRAINT_FAILS, $error->getMessage());
        }
    }

    protected function createConfigurationEntityWithChildRelation(): int
    {
        $parent = (new SpyDynamicEntityConfiguration())
            ->setIsActive(true)
            ->setTableName(sprintf('spy_test_%s', uniqid()))
            ->setTableAlias(sprintf('test_%s', uniqid()))
            ->setDefinition('{}');

        $parent->save();

        (new SpyDynamicEntityConfigurationRelation())
            ->setFkParentDynamicEntityConfiguration($parent->getIdDynamicEntityConfiguration())
            ->setFkChildDynamicEntityConfiguration($parent->getIdDynamicEntityConfiguration())
            ->setName(sprintf('rel_%s', uniqid()))
            ->setIsEditable(true)
            ->save();

        return $parent->getIdDynamicEntityConfiguration();
    }

    protected function createDynamicEntityConfigurationTransfer(): DynamicEntityConfigurationTransfer
    {
        return (new DynamicEntityConfigurationTransfer())
            ->setTableName(static::TABLE_NAME)
            ->setTableAlias(static::TABLE_ALIAS)
            ->setDynamicEntityDefinition(
                (new DynamicEntityDefinitionTransfer())
                    ->setIdentifier(static::IDENTIFIER)
                    ->setIsDeletable(true),
            );
    }
}
