<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PublicationModel;

class PublicationController extends ResourceController
{
    use ResponseTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new PublicationModel();
    }

    public function index()
    {
        $publications = $this->model->findAll();
        return $this->respond($publications);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        
        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
        } else {
            return $this->fail($this->model->errors());
        }
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
        
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        } else {
            return $this->fail($this->model->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['id' => $id]);
        } else {
            return $this->fail('Failed to delete the publication');
        }
    }
}