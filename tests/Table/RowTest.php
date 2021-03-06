<?php
namespace Atlas\Orm\Table;

class RowTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithoutPrimary()
    {
        $row = new Row(['id' => null]);
        $this->assertNull($row->id);
    }

    public function testGetMissingCol()
    {
        $row = new Row(['id' => null]);
        $this->setExpectedException('Atlas\Orm\Exception');
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new Row(['id' => null]);
        $this->setExpectedException('Atlas\Orm\Exception');
        $row->no_such_col = 'foo';
    }

    public function testIsset()
    {
        $row = new Row(['id' => null]);
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new Row(['id' => null, 'foo' => 'bar']);
        $this->assertSame('bar', $row->foo);
        unset($row->foo);
        $this->assertNull($row->foo);
    }

    public function testUnsetIdentity()
    {
        $row = new Row(['id' => null, 'foo' => 'bar']);
        $this->assertNull($row->id);
        unset($row->id);
        $this->assertNull($row->id);
    }

    public function testUnsetMissingCol()
    {
        $row = new Row(['id' => null]);
        $this->setExpectedException('Atlas\Orm\Exception');
        unset($row->no_such_col);
    }

    public function testStatus()
    {
        $row = new Row(['id' => '1', 'foo' => 'bar']);
        $this->assertSame($row::FOR_INSERT, $row->getStatus());

        $row->setStatus($row::SELECTED);
        $this->assertSame($row::SELECTED, $row->getStatus());

        $this->assertTrue($row->hasStatus([
            $row::SELECTED,
            $row::MODIFIED,
        ]));

        $this->setExpectedException(
            'UnexpectedValueException',
            "Expected valid row status, got 'No Such Status' instead."
        );
        $row->setStatus('No Such Status');
    }

    public function testCannotModifyAfterDelete()
    {
        $row = new Row(['id' => '1', 'foo' => 'bar']);
        $row->setStatus($row::DELETED);

        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Row::$foo is immutable once deleted.'
        );
        $row->foo = 'zim';
    }
}
