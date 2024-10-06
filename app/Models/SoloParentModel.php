<?php

namespace App\Models;

use CodeIgniter\Model;

class SoloParentModel extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['applicantName', 'applicantEmail', 'phoneNumber', 'address', 'numberOfChildren'];

    protected $useTimestamps    = false;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'applicantName' => 'required|min_length[2]|max_length[100]',
        'applicantEmail' => 'required|valid_email|max_length[100]',
        'phoneNumber' => 'required|numeric',
        'address' => 'required',
        'numberOfChildren' => 'required|integer|greater_than[0]'
    ];

    // Validation messages
    protected $validationMessages = [
        'applicantName' => [
            'required' => 'The applicant name field is required.',
            'min_length' => 'The applicant name must be at least 2 characters long.',
            'max_length' => 'The applicant name cannot exceed 100 characters.'
        ],
        'applicantEmail' => [
            'required' => 'The email field is required.',
            'valid_email' => 'Please enter a valid email address.',
            'max_length' => 'The email address cannot exceed 100 characters.'
        ],
        'phoneNumber' => [
            'required' => 'The phone number field is required.',
            'numeric' => 'The phone number must be a numeric value.'
        ],
        'address' => [
            'required' => 'The address field is required.'
        ],
        'numberOfChildren' => [
            'required' => 'The number of children field is required.',
            'integer' => 'The number of children must be an integer value.',
            'greater_than' => 'The number of children must be greater than 0.'
        ]
    ];

    // Skip validation
    protected $skipValidation = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
