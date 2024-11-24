<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationModel extends Model
{
    protected $table = 'publications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'publication_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';


}