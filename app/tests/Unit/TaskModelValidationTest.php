<?php

use App\Models\TaskModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class TaskModelValidationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = 'App';
    protected $DBGroup   = 'default';

    public function testInsertRejectsTooLongTitle(): void
    {
        $model = new TaskModel();
        $tooLong = str_repeat('x', 256);

        $ok = $model->insert(['title' => $tooLong, 'completed' => 0]);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('title', $model->errors());
    }

    public function testUpdateRejectsInvalidCompleted(): void
    {
        $model = new TaskModel();
        $id = $model->insert(['title' => 'valida', 'completed' => 0], true);
        $this->assertIsInt($id);

        $ok = $model->update($id, ['completed' => 2]);
        $this->assertFalse($ok);
        $this->assertArrayHasKey('completed', $model->errors());
    }

}
