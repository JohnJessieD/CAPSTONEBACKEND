<?php

namespace App\Models;

use CodeIgniter\Model;

class ResidentModel extends Model
{
    protected $table = 'residents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id_number', 'first_name', 'middle_name', 'last_name', 'category',
        'date_of_birth', 'gender', 'address', 'contact_number', 'email', 'details'
    ];

   
}