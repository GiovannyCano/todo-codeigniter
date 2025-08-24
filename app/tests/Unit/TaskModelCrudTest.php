<?php

use App\Models\TaskModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class TaskModelCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = 'App';
    protected $DBGroup   = 'default';

    public function testInsertAndFind(): void
    {
        $m = new TaskModel();
        $id = $m->insert(['title' => 'Primera', 'completed' => 0], true);

        $this->assertIsInt($id);
        $row = $m->find($id);
        $this->assertNotEmpty($row);
        $this->assertSame('Primera', $row['title']);
        $this->assertSame('0', (string) $row['completed']);
    }

    public function testUpdateTitleAndCompleted(): void
    {
        $m = new TaskModel();
        $id = $m->insert(['title' => 'Original', 'completed' => 0], true);

        $ok = $m->update($id, ['title' => 'Editada', 'completed' => 1]);
        $this->assertTrue($ok);

        $row = $m->find($id);
        $this->assertSame('Editada', $row['title']);
        $this->assertSame('1', (string) $row['completed']);
    }

    public function testDeleteRemovesRow(): void
    {
        $m = new TaskModel();
        $id = $m->insert(['title' => 'Borrar', 'completed' => 0], true);

        $ok = $m->delete($id);
        $this->assertTrue($ok);

        $row = $m->find($id);
        $this->assertNull($row);
    }

    public function testInsertBatchAndCount(): void
    {
        $m = new TaskModel();
        $rows = [
            ['title' => 'A', 'completed' => 0],
            ['title' => 'B', 'completed' => 1],
            ['title' => 'C', 'completed' => 0],
        ];
        $count = $m->insertBatch($rows);
        $this->assertSame(3, $count);

        $all = $m->findAll();
        $this->assertCount(3, $all);
    }
}
