<?php

namespace App\Models;

use CodeIgniter\Model;

class SoloParentDetailsModel extends Model
{
    protected $table = 'solo_parent_details';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        '', 'classification', '',
        'reason', 'total_family_income'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'resident_id' => 'required|integer|is_unique[solo_parent_details.resident_id,id,{id}]',
        'classification' => 'required',
        'solo_parent_id_number' => 'required|is_unique[solo_parent_details.solo_parent_id_number,id,{id}]',
        'reason' => 'required',
        'total_family_income' => 'required|numeric'
    ];

    protected $validationMessages = [
        'resident_id' => [
            'is_unique' => 'Solo Parent details for this resident already exist.'
        ],
        'solo_parent_id_number' => [
            'is_unique' => 'This Solo Parent ID Number is already taken.'
        ]
    ];

    protected $skipValidation = false;
}