<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BarangayModel;

class BarangayController extends ResourceController
{
    use ResponseTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new BarangayModel();
    }

    public function index()
    {
        $barangays = $this->model->findAll();
        return $this->respond($barangays);
    }

    public function show($id = null)
    {
        $barangay = $this->model->find($id);
        if ($barangay) {
            return $this->respond($barangay);
        }
        return $this->failNotFound('Barangay not found');
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Barangay created successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        if ($id === null) {
            return $this->fail('No ID provided', 400);
        }

        $data = $this->request->getJSON(true);
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Barangay updated successfully']);
        }
        return $this->fail($this->model->errors() ?: 'Failed to update the barangay', 500);
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Barangay deleted successfully']);
        }
        return $this->fail('Failed to delete the barangay');
    }
}

