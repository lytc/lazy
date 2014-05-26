<?php

namespace SlothsTest\Pagination;

use SlothsTest\Db\Model\Stub\User;
use Sloths\Db\Model\Model;
use Sloths\Db\Sql\Select;
use Sloths\Pagination\Paginator;
use SlothsTest\TestCase;

/**
 * @covers \Sloths\Pagination\Paginator
 */
class PaginatorTest extends TestCase
{
    public function testDataAdapter()
    {
        $paginator = new Paginator([]);
        $this->assertInstanceOf('Sloths\Pagination\DataAdapter\ArrayAdapter', $paginator->getDataAdapter());

        $paginator = new Paginator(new Select(), Model::getConnection());
        $this->assertInstanceOf('Sloths\Pagination\DataAdapter\DbSelect', $paginator->getDataAdapter());

        $paginator = new Paginator(User::all());
        $this->assertInstanceOf('Sloths\Pagination\DataAdapter\ModelCollection', $paginator->getDataAdapter());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidDataAdapterShouldThrowException()
    {
        new Paginator(new \stdClass());
    }

    public function testArrayAdapter()
    {
        $totalRows = 31;
        $itemsCountPerPage = 2;
        $pageRange = 10;
        $rows = range(1, $totalRows);

        $paginator = new Paginator($rows);
        $paginator->setItemsCountPerPage($itemsCountPerPage)->setPageRange($pageRange);

        $this->assertSame(1, $paginator->getFirstPageInRange());
        $this->assertSame($pageRange, $paginator->getLastPageInRange());
        $this->assertFalse($paginator->getPrevPageNumber());
        $this->assertSame(2, $paginator->getNextPageNumber());
        $this->assertSame(0, $paginator->getFromIndex());
        $this->assertSame(1, $paginator->getToIndex());

        $paginator->setCurrentPage(6);
        $this->assertSame(1, $paginator->getFirstPageInRange());
        $this->assertSame($pageRange, $paginator->getLastPageInRange());
        $this->assertSame(5, $paginator->getPrevPageNumber());
        $this->assertSame(7, $paginator->getNextPageNumber());
        $this->assertSame(10, $paginator->getFromIndex());
        $this->assertSame(11, $paginator->getToIndex());

        $paginator->setCurrentPage(7);
        $this->assertSame(2, $paginator->getFirstPageInRange());
        $this->assertSame(11, $paginator->getLastPageInRange());
        $this->assertSame(12, $paginator->getFromIndex());
        $this->assertSame(13, $paginator->getToIndex());

        $paginator->setCurrentPage(13);
        $this->assertSame(7, $paginator->getFirstPageInRange());
        $this->assertSame(16, $paginator->getLastPageInRange());
        $this->assertSame(24, $paginator->getFromIndex());
        $this->assertSame(25, $paginator->getToIndex());

        $paginator->setCurrentPage(16);
        $this->assertFalse($paginator->getNextPageNumber());
    }

    public function testWithFewRows()
    {
        $totalRows = 31;
        $itemsCountPerPage = 10;
        $pageRange = 10;
        $rows = range(1, $totalRows);

        $paginator = new Paginator($rows);
        $paginator->setItemsCountPerPage($itemsCountPerPage)->setPageRange($pageRange);
        $this->assertSame(4, $paginator->getTotalPages());
        $this->assertSame(1, $paginator->getFirstPageInRange());
        $this->assertSame(4, $paginator->getLastPageInRange());
        $this->assertFalse($paginator->getPrevPageNumber());
    }

    public function testWithOutOfRange()
    {
        $totalRows = 31;
        $itemsCountPerPage = 10;
        $pageRange = 10;
        $rows = range(1, $totalRows);

        $paginator = new Paginator($rows);
        $paginator->setItemsCountPerPage($itemsCountPerPage)->setPageRange($pageRange);

        $paginator->setCurrentPage(-100);
        $this->assertSame(1, $paginator->getCurrentPage());

        $paginator->setCurrentPage(100);
        $this->assertSame(4, $paginator->getCurrentPage());
    }

    public function testModelCollectionAdapter()
    {
        $collection = $this->getMock('Sloths\Db\Model\Collection', ['calcFoundRows', 'foundRows', 'limit'], [], '', false);
        $collection->expects($this->once())->method('calcFoundRows')->willReturnSelf();
        $collection->expects($this->once())->method('foundRows');
        $collection->expects($this->once())->method('limit');
        $paginator = new Paginator($collection);

        $this->assertSame($collection, $paginator->getIterator());
    }

    public function estWithDbSelectAdapter()
    {
        $select = new Select('users');
        $connection = AbstractModel::getDefaultConnection();

        $paginator = new Paginator($select, $connection);
        $paginator->setItemsCountPerPage(2);

        $this->assertCount(2, $paginator->getItems());
        $this->assertSame(4, $paginator->getTotalItemsCount());
        $this->assertSame(2, $paginator->getTotalPages());
        $this->assertSame(1, $paginator->getFromIndex());
        $this->assertSame(2, $paginator->getToIndex());
        $this->assertFalse($paginator->getPrevPageNumber());
        $this->assertSame(2, $paginator->getNextPageNumber());

        $paginator->setCurrentPage(2);
        $this->assertSame(3, $paginator->getFromIndex());
        $this->assertSame(4, $paginator->getToIndex());
        $this->assertSame(1, $paginator->getPrevPageNumber());
        $this->assertFalse($paginator->getNextPageNumber());
    }
    
    public function testGetInfo()
    {
        $expected = [
            'pages'             => 'getTotalPages',
            'currentPage'       => 'getCurrentPage',
            'itemsCountPerPage' => 'getItemsCountPerPage',
            'fromIndex'         => 'getFromIndex',
            'toIndex'           => 'getToIndex',
            'prev'              => 'getPrevPageNumber',
            'next'              => 'getNextPageNumber',
            'firstPageInRange'  => 'getFirstPageInRange',
            'lastPageInRange'   => 'getLastPageInRange'
        ];

        $paginator = $this->getMock('Sloths\Pagination\Paginator',
            [
                'getTotalPages',
                'getCurrentPage',
                'getItemsCountPerPage',
                'getFromIndex',
                'getToIndex',
                'getPrevPageNumber',
                'getNextPageNumber',
                'getFirstPageInRange',
                'getLastPageInRange'
            ],
            [[]]);

        $paginator->expects($this->once())->method('getTotalPages')->willReturn('getTotalPages');
        $paginator->expects($this->once())->method('getCurrentPage')->willReturn('getCurrentPage');
        $paginator->expects($this->once())->method('getItemsCountPerPage')->willReturn('getItemsCountPerPage');
        $paginator->expects($this->once())->method('getFromIndex')->willReturn('getFromIndex');
        $paginator->expects($this->once())->method('getToIndex')->willReturn('getToIndex');
        $paginator->expects($this->once())->method('getPrevPageNumber')->willReturn('getPrevPageNumber');
        $paginator->expects($this->once())->method('getNextPageNumber')->willReturn('getNextPageNumber');
        $paginator->expects($this->once())->method('getFirstPageInRange')->willReturn('getFirstPageInRange');
        $paginator->expects($this->once())->method('getLastPageInRange')->willReturn('getLastPageInRange');

        $this->assertSame($expected, $paginator->getInfo());
    }

    public function testGetItems()
    {
        $paginator = new Paginator([]);
        $this->assertInstanceOf('ArrayIterator', $paginator->getItems());
    }

    public function testCount()
    {
        $paginator = $this->getMock('Sloths\Pagination\Paginator', ['getTotalPages'], [[]]);
        $paginator->expects($this->exactly(2))->method('getTotalPages')->willReturn(10);
        $this->assertSame(10, $paginator->count());
        $this->assertSame(10, count($paginator));
    }

    public function testDefaultItemCountPerPage()
    {
        Paginator::setDefaultItemsCountPerPage(10);
        $this->assertSame(10, Paginator::getDefaultItemsCountPerPage());
    }

    public function testDefaultPageRange()
    {
        Paginator::setDefaultPageRange(10);
        $this->assertSame(10, Paginator::getDefaultPageRange());
    }
}