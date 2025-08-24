<?php

use App\Models\TaskModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Database\Exceptions\DataException;

final class TaskModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $DBGroup   = 'default';
    protected $migrate   = true;
    protected $namespace = 'App';
    protected $seed      = 'App\\Database\\Seeds\\TaskSeeder';

    public function testInsertValidTaskCreatesRecord(): void
    {
        $model = new TaskModel();
        $id = $model->insert(['title' => 'Tarea de prueba', 'completed' => 0], true);

        $this->assertIsInt($id);
        $row = $model->find($id);
        $this->assertNotEmpty($row);
        $this->assertSame('Tarea de prueba', $row['title']);
        $this->assertSame('0', (string) $row['completed']);
    }

    public function testValidationRejectsEmptyTitle(): void
    {
        $model = new TaskModel();
        $result = $model->insert(['title' => '', 'completed' => 0]);

        $this->assertFalse($result);
        $errors = $model->errors();
        $this->assertArrayHasKey('title', $errors);
    }

    public function testInsertEmptyTitleFails(): void
    {
        $m = new TaskModel();
        $ok = $m->insert(['title' => '', 'completed' => 0]);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('title', $m->errors());
    }

    public function testInsertNullTitleFails(): void
    {
        $m = new TaskModel();
        $ok = $m->insert(['title' => null, 'completed' => 0]);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('title', $m->errors());
    }

    public function testInsertInvalidCompletedFails(): void
    {
        $m = new TaskModel();
        $ok = $m->insert(['title' => 'X', 'completed' => 2]);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('completed', $m->errors());
    }

    public function testUpdateInvalidCompletedFails(): void
    {
        $m  = new TaskModel();
        $id = $m->insert(['title' => 'Valida', 'completed' => 0], true);
        $this->assertIsInt($id);

        $ok = $m->update($id, ['completed' => 'banana']);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('completed', $m->errors());
    }

    public function testAllowedFieldsIgnoreUnknown(): void
    {
        $m  = new TaskModel();
        $id = $m->insert(['title' => 'Hola', 'completed' => 0, 'hacker' => 'x'], true);
        $this->assertIsInt($id);

        $row = $m->find($id);
        $this->assertArrayHasKey('title', $row);
        $this->assertArrayHasKey('completed', $row);
        $this->assertArrayNotHasKey('hacker', $row);
    }

    public function testTimestampsSetOnCreateAndUpdate(): void
    {
        $m  = new TaskModel();
        $id = $m->insert(['title' => 'Ts', 'completed' => 0], true);
        $row = $m->find($id);

        $this->assertNotEmpty($row['created_at']);
        $ok = $m->update($id, ['title' => 'Ts2']);
        $this->assertTrue($ok);

        $row2 = $m->find($id);
        $this->assertNotEmpty($row2['updated_at']);
    }

    public function testUpdateWithEmptyPayloadThrows(): void
    {
        $m  = new TaskModel();
        $id = $m->insert(['title' => 'Algo', 'completed' => 0], true);
        $this->assertIsInt($id);

        $this->expectException(DataException::class);
        $m->update($id, []);
    }
}
