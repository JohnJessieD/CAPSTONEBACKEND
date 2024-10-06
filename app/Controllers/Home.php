<?php
namespace App\Controllers;

use App\Models\PWDModel;
use App\Models\MembershipModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
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
    
    public function acceptRequest()
    {
        $input = $this->request->getJSON();

        // Validate $input if needed

        $model = new PWDModel();

        // Get the request ID from the JSON input
        $money_requestsId = $input->money_requestsId;

        // Find the request in the database by ID
        $request = $model->find($money_requestsId);

        if (!$request) {
            return $this->failNotFound('Request not found');
        }

        // Update the status of the request to 'accepted'
        $model->update($requestId, ['status' => 'accepted']);

        return $this->respond(['message' => 'Request accepted successfully'], 200);
    }

    public function rejectRequest()
    {
        $input = $this->request->getJSON();

        // Validate $input if needed

        $model = new PWDModel();

        // Get the request ID from the JSON input
        $requestId = $input->requestId;

        // Find the request in the database by ID
        $request = $model->find($requestId);

        if (!$request) {
            return $this->failNotFound('Request not found');
        }

        // Update the status of the request to 'rejected'
        $model->update($requestId, ['status' => 'rejected']);

        return $this->respond(['message' => 'Request rejected successfully'], 200);
    }
    public function Membership()
    {
        // Check if the request is a POST request
        if ($this->request->getMethod() !== 'post') {
            return $this->fail('Invalid request method', 405);
        }

        // Validation rules
        $rules = [
            'name'       => 'required|min_length[3]|max_length[255]',
            'dob'        => 'required|valid_date',
            'sickness'   => 'required|min_length[3]|max_length[255]',
            'certificate' => [
                'uploaded[certificate]',
                'mime_in[certificate,image/jpg,image/jpeg,image/png,image/gif]',
                'max_size[certificate,2048]', // Max 2MB
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }

        // Handle the file upload
        $certificate = $this->request->getFile('certificate');

        if (!$certificate->isValid()) {
            return $this->fail($certificate->getErrorString(), 400);
        }

        // Generate a new secure name
        $newName = $certificate->getRandomName();

        // Move the file to the desired directory (e.g., writable/uploads/certificates)
        $certificate->move(WRITEPATH . 'uploads/certificates', $newName);

        // Prepare data for insertion
        $data = [
            'name'       => $this->request->getPost('name'),
            'dob'        => $this->request->getPost('dob'),
            'sickness'   => $this->request->getPost('sickness'),
            'certificate'=> 'uploads/certificates/' . $newName,
        ];

        // Instantiate MembershipModel
        $membershipModel = new MembershipModel();
        $membershipModel->insert($data);

        // Prepare response
        return $this->respondCreated([
            'status'  => 'success',
            'message' => 'Membership application submitted successfully.',
            'data'    => $data,
        ]);
    }

}
