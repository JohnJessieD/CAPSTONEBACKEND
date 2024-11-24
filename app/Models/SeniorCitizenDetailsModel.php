<?php

namespace App\Models;

use CodeIgniter\Model;

class SeniorCitizenDetailsModel extends Model
{
    protected $table = 'senior_citizen_details';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        '', 'sss_number', 'gsis_number', 'tin_number',
        'philhealth_number', ''
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'resident_id' => 'required|integer|is_unique[senior_citizen_details.resident_id,id,{id}]',
        'sss_number' => 'permit_empty|is_unique[senior_citizen_details.sss_number,id,{id}]',
        'gsis_number' => 'permit_empty|is_unique[senior_citizen_details.gsis_number,id,{id}]',
        'tin_number' => 'permit_empty|is_unique[senior_citizen_details.tin_number,id,{id}]',
        'philhealth_number' => 'permit_empty|is_unique[senior_citizen_details.philhealth_number,id,{id}]',
        'senior_citizen_id_number' => 'required|is_unique[senior_citizen_details.senior_citizen_id_number,id,{id}]'
    ];

    protected $validationMessages = [
        'resident_id' => [
            'is_unique' => 'Senior Citizen details for this resident already exist.'
        ],
        'senior_citizen_id_number' => [
            'is_unique' => 'This Senior Citizen ID Number is already taken.'
        ]
    ];

    protected $skipValidation = false;
}