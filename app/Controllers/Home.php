<?php

namespace App\Controllers;

use App\Models\PWDModel;
use App\Models\MembershipModel;
use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    use ResponseTrait;

    protected $notificationModel;
    protected $scheduleModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->scheduleModel = new ScheduleModel();
    }

    public function index(): string
    {
        return view('welcome_message');
    }

    public function requestMoney()
    {
        $input = $this->request->getJSON();

        // Process the money request and store it in the database
        $model = new PWDModel();
        $insertId = $model->insert($input);

        if ($insertId) {
            // Create a notification for the new assistance request
            $this->createNotification('assistance', 'New assistance request received');
        }

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

        $model = new PWDModel();

        // Get the request ID from the JSON input
        $requestId = $input->requestId;

        // Find the request in the database by ID
        $request = $model->find($requestId);

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
        $insertId = $membershipModel->insert($data);

        if ($insertId) {
            // Create a notification for the new membership application
            $this->createNotification('membership', 'New membership application received');
        }

        // Prepare response
        return $this->respondCreated([
            'status'  => 'success',
            'message' => 'Membership application submitted successfully.',
            'data'    => $data,
        ]);
    }

    protected function createNotification($type, $message)
    {
        $this->notificationModel->insert([
            'type' => $type,
            'message' => $message,
            'is_read' => 0,
        ]);
    }

    public function getNotifications()
    {
        $notifications = $this->notificationModel->orderBy('created_at', 'DESC')->findAll();
        return $this->respond($notifications, 200);
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = $this->notificationModel->find($notificationId);

        if (!$notification) {
            return $this->failNotFound('Notification not found');
        }

        $this->notificationModel->update($notificationId, ['is_read' => 1]);

        return $this->respond(['message' => 'Notification marked as read'], 200);
    }

    // New methods for schedule management

    public function getSchedules()
    {
        $schedules = $this->scheduleModel->findAll();
        return $this->respond($schedules, 200);
    }

    public function addSchedule()
    {
        $input = $this->request->getJSON();

        $data = [
            'user' => $input->user,
            'date' => $input->date,
            'description' => $input->description,
        ];

        $insertId = $this->scheduleModel->insert($data);

        if ($insertId) {
            return $this->respondCreated(['message' => 'Schedule added successfully', 'id' => $insertId]);
        } else {
            return $this->fail('Failed to add schedule', 400);
        }
    }

    public function updateSchedule($id)
    {
        $input = $this->request->getJSON();

        $data = [
            'user' => $input->user,
            'date' => $input->date,
            'description' => $input->description,
        ];

        $updated = $this->scheduleModel->update($id, $data);

        if ($updated) {
            return $this->respond(['message' => 'Schedule updated successfully']);
        } else {
            return $this->fail('Failed to update schedule', 400);
        }
    }

    public function deleteSchedule($id)
    {
        $deleted = $this->scheduleModel->delete($id);

        if ($deleted) {
            return $this->respond(['message' => 'Schedule deleted successfully']);
        } else {
            return $this->fail('Failed to delete schedule', 400);
        }
    }
}