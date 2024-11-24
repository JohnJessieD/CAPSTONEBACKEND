<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\EventModel;

class EventController extends ResourceController
{
    use ResponseTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new EventModel();
    }

    public function index()
    {
        $events = $this->model->findAll();
        return $this->respond($events);
    }

    public function create()
    {
        $data = $this->request->getJSON();

        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Event created successfully']);
        }

        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Event updated successfully']);
        }

        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Event deleted successfully']);
        }

        return $this->fail('Failed to delete the event');
    }
}