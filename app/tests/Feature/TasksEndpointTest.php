<?php

use App\Models\TaskModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class TasksEndpointTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $DBGroup   = 'default';
    protected $namespace = 'App';
    protected $migrate   = true;

    public function testIndexReturns200AndArray(): void
    {
        $model = new TaskModel();
        $model->insert(['title' => 'A', 'completed' => 0]);
        $model->insert(['title' => 'B', 'completed' => 1]);

        $result = $this->get('tasks');
        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);

        $this->assertIsArray($json);
        $this->assertGreaterThanOrEqual(2, count($json));
    }

    public function testCreateShowUpdateDeleteFlow(): void
    {
        $create = $this->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ])
                    ->withBody(json_encode(['title' => 'Nueva', 'completed' => 0], JSON_UNESCAPED_UNICODE))
                    ->post('tasks');

        $create->assertStatus(201);
        $created = json_decode($create->getJSON(), true);
        $this->assertArrayHasKey('id', $created);
        $id = $created['id'];

        $show = $this->get("tasks/{$id}");
        $show->assertStatus(200);
        $task = json_decode($show->getJSON(), true);
        $this->assertSame('Nueva', $task['title']);

        $update = $this->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ])
                    ->withBody(json_encode(['title' => 'Actualizada', 'completed' => 1], JSON_UNESCAPED_UNICODE))
                    ->put("tasks/{$id}");

        $update->assertStatus(200);
        $updated = json_decode($update->getJSON(), true);
        $this->assertSame('Actualizada', $updated['title']);
        $this->assertSame('1', (string) $updated['completed']);

        $del = $this->delete("tasks/{$id}");
        $del->assertStatus(200); // respondDeleted -> 200 OK con body

        $after = $this->get("tasks/{$id}");
        $after->assertStatus(404);
    }
}
