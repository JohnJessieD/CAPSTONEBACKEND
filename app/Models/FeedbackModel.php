<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'id';
    protected $allowedFields = ['category', 'message', ];
    protected $createdField = 'created_at';
    protected $updatedField = '';

}