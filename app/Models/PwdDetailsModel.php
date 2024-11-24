<?php

namespace App\Models;

use CodeIgniter\Model;

class PwdDetailsModel extends Model
{
    protected $table = 'pwd_details';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        '', 'disability_type', 'disability_cause', 'disability_name',
        'physician_name', 'physician_license_number', ''
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'resident_id' => 'required|integer|is_unique[pwd_details.resident_id,id,{id}]',
        'disability_type' => 'required',
        'disability_cause' => 'required',
        'disability_name' => 'required',
        'physician_name' => 'required',
        'physician_license_number' => 'required',
        'pwd_id_number' => 'required|is_unique[pwd_details.pwd_id_number,id,{id}]'
    ];

    protected $validationMessages = [
        'resident_id' => [
            'is_unique' => 'PWD details for this resident already exist.'
        ],
        'pwd_id_number' => [
            'is_unique' => 'This PWD ID Number is already taken.'
        ]
    ];

    protected $skipValidation = false;
}