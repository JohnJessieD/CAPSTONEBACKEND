<?php

namespace App\Controllers;

use App\Models\PWDModel;
use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;

    public function index(): string
    {
        return view('welcome_message');
    }

    public function requestMoney()
    {
        $input = $this->request->getJSON();

        // Perform validation on $input if needed

        // Process the money request and store it in the database
        $model = new PWDModel();
        $model->insert($input);

        return $this->respond(['message' => 'Money request submitted successfully'], 200);
    }

    public function PWD()
    {
        // Get all data from the PWDModel
        $model = new PWDModel();
        $data = $model->findAll();

        return $this->respond($data, 200);
    }

    public function updateRequest()
    {
        $input = $this->request->getJSON();

        // Validate $input if needed

        $model = new PWDModel();

        // Get the request ID and updated data from the JSON input
        $requestId = $input->requestId;
        $updatedData = $input->updatedData;

        // Find the request in the database by ID
        $request = $model->find($requestId);

        if (!$request) {
            return $this->failNotFound('Request not found');
        }

        // Update the request with the new data
        $model->update($requestId, $updatedData);

        return $this->respond(['message' => 'Request updated successfully'], 200);
    }

    public function deleteRequest($requestId)
    {
        // Validate $requestId if needed
    
        $model = new PWDModel();
    
        // Find the request in the database by ID
        $request = $model->find($requestId);
    
        if (!$request) {
            return $this->failNotFound('Request not found');
        }
    
        // Delete the request from the database
        $model->delete($requestId);
    
        return $this->respond(['message' => 'Request deleted successfully'], 200);
    }
    
}
