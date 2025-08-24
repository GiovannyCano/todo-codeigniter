<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['title', 'completed', 'created_at', 'updated_at'];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'title'     => 'required|min_length[1]|max_length[255]',
        'completed' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'El título es obligatorio.',
        ],
    ];
}
