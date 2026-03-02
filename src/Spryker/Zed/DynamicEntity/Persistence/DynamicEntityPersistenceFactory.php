<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence;

use Orm\Zed\DynamicEntity\Persistence\SpyDynamicEntityConfigurationQuery;
use Propel\Runtime\Map\DatabaseMap;
use Propel\Runtime\Propel;
use Spryker\Zed\DynamicEntity\Dependency\Service\DynamicEntityToUtilEncodingServiceInterface;
use Spryker\Zed\DynamicEntity\DynamicEntityDependencyProvider;
use Spryker\Zed\DynamicEntity\Persistence\Builder\DynamicEntityQueryBuilder;
use Spryker\Zed\DynamicEntity\Persistence\Builder\DynamicEntityQueryBuilderInterface;
use Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\DefaultFilterStrategy;
use Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\FilterStrategyInterface;
use Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\InFilterStrategy;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\DatabaseExceptionToErrorMapperInterface;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\ExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\ExceptionToErrorMapperInterface;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\MySql\DeleteParentRowExceptionToErrorMapper as MySqlDeleteParentRowExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\MySql\DuplicateEntryExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\MySql\NotNullableExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\PostgreSql\DeleteParentRowExceptionToErrorMapper as PostgreSqlDeleteParentRowExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\PostgreSql\DuplicateKeyExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Mapper\PostgreSql\NotNullViolationExceptionToErrorMapper;
use Spryker\Zed\DynamicEntity\Persistence\Propel\Mapper\DynamicEntityMapper;
use Spryker\Zed\DynamicEntity\Persistence\Resetter\DynamicEntityResetter;
use Spryker\Zed\DynamicEntity\Persistence\Resetter\DynamicEntityResetterInterface;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityRepositoryInterface getRepository()
 * @method \Spryker\Zed\DynamicEntity\Persistence\DynamicEntityEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\DynamicEntity\DynamicEntityConfig getConfig()
 */
class DynamicEntityPersistenceFactory extends AbstractPersistenceFactory
{
    public function createDynamicEntityConfigurationQuery(): SpyDynamicEntityConfigurationQuery
    {
        return SpyDynamicEntityConfigurationQuery::create();
    }

    public function createDynamicEntityMapper(): DynamicEntityMapper
    {
        return new DynamicEntityMapper($this->getServiceUtilEncoding());
    }

    public function createDynamicEntityQueryBuilder(): DynamicEntityQueryBuilderInterface
    {
        return new DynamicEntityQueryBuilder(
            $this->getPropelDatabaseMap(),
            $this->getFilterStrategies(),
        );
    }

    public function getPropelDatabaseMap(): DatabaseMap
    {
        return Propel::getDatabaseMap();
    }

    public function createExceptionToErrorMapper(): ExceptionToErrorMapperInterface
    {
        return new ExceptionToErrorMapper(
            $this->getDatabaseExceptionToErrorMappers(),
        );
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Persistence\Mapper\DatabaseExceptionToErrorMapperInterface>
     */
    public function getDatabaseExceptionToErrorMappers(): array
    {
        return [
            //MySQL
            $this->createDuplicateEntryExceptionToErrorMapper(),
            $this->createNotNullableExceptionToErrorMapper(),
            $this->createMySqlDeleteParentRowExceptionToErrorMapper(),
            //PostgreSQL
            $this->createDuplicateKeyExceptionToErrorMapper(),
            $this->createNotNullViolationExceptionToErrorMapper(),
            $this->createPostgreSqlDeleteParentRowExceptionToErrorMapper(),
        ];
    }

    public function createDuplicateEntryExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new DuplicateEntryExceptionToErrorMapper();
    }

    public function createDuplicateKeyExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new DuplicateKeyExceptionToErrorMapper();
    }

    public function createNotNullableExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new NotNullableExceptionToErrorMapper();
    }

    public function createNotNullViolationExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new NotNullViolationExceptionToErrorMapper();
    }

    public function createMySqlDeleteParentRowExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new MySqlDeleteParentRowExceptionToErrorMapper();
    }

    public function createPostgreSqlDeleteParentRowExceptionToErrorMapper(): DatabaseExceptionToErrorMapperInterface
    {
        return new PostgreSqlDeleteParentRowExceptionToErrorMapper();
    }

    public function createDynamicEntityResetter(): DynamicEntityResetterInterface
    {
        return new DynamicEntityResetter();
    }

    public function createDefaultFilterStrategy(): FilterStrategyInterface
    {
        return new DefaultFilterStrategy();
    }

    public function createInFilterStrategy(): FilterStrategyInterface
    {
        return new InFilterStrategy($this->getServiceUtilEncoding());
    }

    /**
     * @return array<\Spryker\Zed\DynamicEntity\Persistence\Filter\Strategy\FilterStrategyInterface>
     */
    public function getFilterStrategies(): array
    {
        return [
            $this->createInFilterStrategy(),
            $this->createDefaultFilterStrategy(),
        ];
    }

    public function getServiceUtilEncoding(): DynamicEntityToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(DynamicEntityDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
