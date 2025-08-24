<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class TodoViewTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testRootRendersTodoApp(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $this->assertStringContainsString('Todo App', $res->getBody());
    }
}
