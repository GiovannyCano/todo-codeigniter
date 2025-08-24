<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TaskModel;

class Tasks extends ResourceController
{
    protected $modelName = TaskModel::class;
    protected $format    = 'json';

    public function index()
    {
        $tasks = $this->model->orderBy('id', 'desc')->findAll();
        return $this->respond($tasks);
    }

    public function show($id = null)
    {
        $task = $this->model->find($id);
        if (!$task) {
            return $this->failNotFound('Task no encontrada.');
        }
        return $this->respond($task);
    }

    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (!$this->model->insert($data, false)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $id   = $this->model->getInsertID();
        $task = $this->model->find($id);

        return $this->respondCreated($task);
    }

    public function update($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Task no encontrada.');
        }

        // Admite PUT/PATCH con JSON o x-www-form-urlencoded
        $payload = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if (!$this->model->update($id, $payload)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond($this->model->find($id));
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Task no encontrada.');
        }

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Task eliminada.']);
    }
}
