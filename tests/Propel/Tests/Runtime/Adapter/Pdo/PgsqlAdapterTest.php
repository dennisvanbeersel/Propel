<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Adapter\Pdo;

use Propel\Runtime\Adapter\Pdo\PgsqlAdapter;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Tests\Bookstore\BookQuery;
use Propel\Tests\Bookstore\Map\BookTableMap;
use Propel\Tests\TestCaseFixtures;

/**
 * Tests the Pgsql adapter
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PgsqlAdapterTest extends TestCaseFixtures
{
    /**
     * @return string
     */
    protected function getDriver()
    {
        return 'pgsql';
    }

    /**
     * @return void
     */
    public function testGetExplainPlanQuery()
    {
        $db = new PgsqlAdapter();
        $query = 'SELECT B.* FROM (SELECT A.*, rownum AS PROPEL_ROWNUM FROM (SELECT book.ID AS ORA_COL_ALIAS_0, book.TITLE AS ORA_COL_ALIAS_1, book.ISBN AS ORA_COL_ALIAS_2, book.PRICE AS ORA_COL_ALIAS_3, book.PUBLISHER_ID AS ORA_COL_ALIAS_4, book.AUTHOR_ID AS ORA_COL_ALIAS_5, author.ID AS ORA_COL_ALIAS_6, author.FIRST_NAME AS ORA_COL_ALIAS_7, author.LAST_NAME AS ORA_COL_ALIAS_8, author.EMAIL AS ORA_COL_ALIAS_9, author.AGE AS ORA_COL_ALIAS_10, book.PRICE AS BOOK_PRICE FROM book, author) A ) B WHERE  B.PROPEL_ROWNUM <= 1';
        $expected = 'EXPLAIN SELECT B.* FROM (SELECT A.*, rownum AS PROPEL_ROWNUM FROM (SELECT book.ID AS ORA_COL_ALIAS_0, book.TITLE AS ORA_COL_ALIAS_1, book.ISBN AS ORA_COL_ALIAS_2, book.PRICE AS ORA_COL_ALIAS_3, book.PUBLISHER_ID AS ORA_COL_ALIAS_4, book.AUTHOR_ID AS ORA_COL_ALIAS_5, author.ID AS ORA_COL_ALIAS_6, author.FIRST_NAME AS ORA_COL_ALIAS_7, author.LAST_NAME AS ORA_COL_ALIAS_8, author.EMAIL AS ORA_COL_ALIAS_9, author.AGE AS ORA_COL_ALIAS_10, book.PRICE AS BOOK_PRICE FROM book, author) A ) B WHERE  B.PROPEL_ROWNUM <= 1';

        $this->assertEquals($expected, $db->getExplainPlanQuery($query), 'getExplainPlanQuery() returns a SQL Explain query');
    }

    /**
     * Test `applyLock`
     *
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\Group('pgsql')]
    public function testSimpleLock(): void
    {
        $c = new BookQuery();
        $c->addSelectColumn(BookTableMap::COL_ID);
        $c->lockForShare();

        $params = [];
        $result = $c->createSelectSql($params);

        $expected = 'SELECT book.id FROM book FOR SHARE';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test `applyLock`
     *
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\Group('pgsql')]
    public function testComplexLock(): void
    {
        $c = new BookQuery();
        $c->addSelectColumn(BookTableMap::COL_ID);
        $c->lockForUpdate([BookTableMap::TABLE_NAME], true);

        $params = [];
        $result = $c->createSelectSql($params);

        $expected = 'SELECT book.id FROM book FOR UPDATE OF "book" NOWAIT';

        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\Group('pgsql')]
    public function testSubQueryWithSharedLock()
    {
        $subCriteria = new BookQuery();
        $subCriteria->addSelectColumn(BookTableMap::COL_ID);
        $subCriteria->lockForShare([BookTableMap::TABLE_NAME]);

        $c = new BookQuery();
        $c->addSelectColumn(BookTableMap::COL_ID);
        $c->addSelectQuery($subCriteria, 'subCriteriaAlias', false);
        $c->lockForShare([BookTableMap::TABLE_NAME], true);

        $expected ='SELECT subCriteriaAlias.id FROM (SELECT book.id FROM book FOR SHARE of "book") AS subCriteriaAlias FOR SHARE OF "book" NOWAIT';

        $params = [];
        $this->assertSame($expected, $c->createSelectSql($params), 'Subquery contains shared read lock');
    }

    /**
     * Regression: getId() previously called \$con->quote(\$name) which produces
     * a string-literal-quoted form ('Foo'). PostgreSQL folds unquoted identifiers
     * to lowercase, so a sequence created with CREATE SEQUENCE "MyMixedCase_seq"
     * would not be found via nextval('MyMixedCase_seq'). Identifier-quoting via
     * quoteIdentifierTable() preserves case and supports schema qualification.
     *
     * @return void
     */
    public function testGetIdQuotesSequenceAsIdentifier(): void
    {
        $con = $this->createMock(ConnectionInterface::class);
        $con->method('quote')->willReturnCallback(static fn (string $value): string => "'" . str_replace("'", "''", $value) . "'");

        $fetcher = $this->createMock(DataFetcherInterface::class);
        $fetcher->method('fetchColumn')->willReturn(1);

        $capturedSql = '';
        $con->expects($this->once())
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $fetcher) {
                $capturedSql = $sql;

                return $fetcher;
            });

        $adapter = new PgsqlAdapter();
        $adapter->getId($con, 'MySchema.MyMixedCase_seq');

        $this->assertStringContainsString('"MySchema"."MyMixedCase_seq"', $capturedSql, 'sequence name must be identifier-quoted, not string-quoted');
    }
}
