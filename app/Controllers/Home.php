<?php

namespace App\Controllers;

use App\Models\PWDModel;
use App\Models\MembershipModel;
use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Home extends BaseController
{
    use ResponseTrait;

    protected $notificationModel;
    protected $scheduleModel;
    protected $membershipModel;
  protected $pwdModel;

public function __construct()
{
    $this->notificationModel = new NotificationModel();
    $this->scheduleModel = new ScheduleModel();
    $this->membershipModel = new MembershipModel();
    $this->pwdModel = new PWDModel();
}

    public function index(): string
    {
        return view('welcome_message');
    }
    public function requestAppointment()
    {
        $input = $this->request->getJSON();

        // Validate input
        if (!isset($input->user) || !isset($input->date) || !isset($input->description)) {
            return $this->fail('Missing required fields', 400);
        }

        // Prepare data for insertion
        $data = [
            'user' => $input->user,
            'date' => $input->date,
            'description' => $input->description,
            'status' => 'pending', // Default status for new appointment requests
        ];

        // Insert the new appointment request
        $insertId = $this->scheduleModel->insert($data);

        if ($insertId) {
            // Create a notification for the new appointment request
            $this->createNotification('appointment', 'New appointment request received');

            // Send an email to the admin about the new appointment request
            $this->sendAdminNotificationEmail('New Appointment Request', $data);

            return $this->respondCreated([
                'message' => 'Appointment request submitted successfully. Waiting for admin approval.',
                'id' => $insertId
            ]);
        } else {
            return $this->fail('Failed to submit appointment request', 500);
        }
    }

    public function getPendingAppointments()
    {
        $pendingAppointments = $this->scheduleModel->where('status', 'pending')->findAll();
        return $this->respond($pendingAppointments, 200);
    }

    public function acceptAppointment($id)
    {
        $appointment = $this->scheduleModel->find($id);

        if (!$appointment) {
            return $this->failNotFound('Appointment not found');
        }

        if ($appointment['status'] !== 'pending') {
            return $this->fail('This appointment is not in a pending state', 400);
        }

        $this->scheduleModel->update($id, ['status' => 'Confirmed']);

        // Notify the user about the accepted appointment
        $userEmail = $this->getUserEmail($appointment['user']);
        if ($userEmail) {
            $this->sendAppointmentNotificationEmail($userEmail, $appointment, 'accepted');
        }

        return $this->respond(['message' => 'Appointment accepted successfully'], 200);
    }

  public function rejectAppointment($id)
{
    $appointment = $this->scheduleModel->find($id);

    if (!$appointment) {
        return $this->failNotFound('Appointment not found');
    }

    // Store appointment data before deletion for notification purposes
    $appointmentData = $appointment;

    // Delete the appointment instead of updating its status
    if (!$this->scheduleModel->delete($id)) {
        return $this->fail('Failed to delete the appointment', 500);
    }

    // Notify the user about the rejected (and now deleted) appointment
    $userEmail = $this->getUserEmail($appointmentData['user']);
    if ($userEmail) {
        $this->sendAppointmentNotificationEmail($userEmail, $appointmentData, 'rejected and cancelled');
    }

    return $this->respondDeleted(['message' => 'Appointment rejected and deleted successfully']);
}

    private function sendAppointmentNotificationEmail($to, $appointment, $status)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stmswdapp@gmail.com';
            $mail->Password   = 'kamp eoxb tobq rplv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = 'Appointment ' . ucfirst($status);

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #4CAF50; text-align: center;'>San Teodoro MSWD</h2>
                        <h3 style='color: #333333; text-align: center;'>Appointment " . ucfirst($status) . "</h3>
                        <p>Dear {$appointment['user']},</p>
                        <p>Your appointment request for <strong>{$appointment['date']}</strong> has been <strong>" . ucfirst($status) . "</strong>.</p>
                        <p><strong>Description:</strong> {$appointment['description']}</p>
                        " . ($status === 'rejected' ? "<p>If you need to reschedule, please submit a new appointment request.</p>" : "") . "
                        <p>Thank you for your understanding.</p>
                        <br>
                        <p>Best regards,<br>San Teodoro MSWD Team</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    private function sendAdminNotificationEmail($subject, $appointmentData)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stmswdapp@gmail.com';
            $mail->Password   = 'kamp eoxb tobq rplv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
            $mail->addAddress('admin@example.com'); // Replace with actual admin email

            $mail->isHTML(true);
            $mail->Subject = $subject;

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #4CAF50; text-align: center;'>San Teodoro MSWD</h2>
                        <h3 style='color: #333333; text-align: center;'>New Appointment Request</h3>
                        <p><strong>User:</strong> {$appointmentData['user']}</p>
                        <p><strong>Date:</strong> {$appointmentData['date']}</p>
                        <p><strong>Description:</strong> {$appointmentData['description']}</p>
                        <p>Please log in to the admin panel to review and respond to this request.</p>
                        <br>
                        <p>Best regards,<br>San Teodoro MSWD System</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Admin notification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
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
public function fetchRequestMoney()
{
    // Check if the user is logged in
    if (!$this->session->get('logged_in')) {
        return $this->failUnauthorized('You must be logged in to access this data');
    }

    // Get the logged-in user's name
    $userName = $this->session->get('user_name');

    if (!$userName) {
        return $this->failServerError('User information not found in session');
    }

    // Fetch money requests for the logged-in user
    $model = new PWDModel();
    try {
        $requests = $model->where('fullName', $userName)->findAll();

        if (empty($requests)) {
            return $this->respond(['message' => 'No money requests found for this user'], 200);
        }

        // Sanitize the data before sending it to the client
        $sanitizedRequests = array_map(function($request) {
            return [
                'id' => $request['id'],
                'date' => $request['created_at'],
                'fullName' => $request['fullName'],
                'reason' => $request['reason'],
                'amount' => $request['amount'],
                'status' => $request['status'] ?? 'Pending',
                // Add other non-sensitive fields as needed
            ];
        }, $requests);

        return $this->respond($sanitizedRequests, 200);
    } catch (\Exception $e) {
        log_message('error', 'Error fetching money requests: ' . $e->getMessage());
        return $this->failServerError('An error occurred while fetching money requests');
    }
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
public function notifyUser($scheduleId)
{
    $schedule = $this->scheduleModel->find($scheduleId);

    if (!$schedule) {
        return $this->failNotFound('Schedule not found');
    }

    // Fetch user email from PWD model or Membership model based on the schedule
    $userEmail = $this->getUserEmail($schedule['user']);

    if (!$userEmail) {
        return $this->fail('User email not found', 400);
    }

    // Send reminder email notification
    $emailSent = $this->sendScheduleReminderEmail($userEmail, $schedule);

    if ($emailSent) {
        // Update the schedule to mark as notified
        $result = $this->scheduleModel->update($scheduleId, ['notified' => 1]);

        if ($result) {
            return $this->respond(['message' => 'Reminder sent successfully']);
        } else {
            return $this->fail('Failed to update schedule', 500);
        }
    } else {
        return $this->fail('Failed to send reminder', 500);
    }
}

private function sendScheduleReminderEmail($to, $schedule)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stmswdapp@gmail.com';
        $mail->Password   = 'kamp eoxb tobq rplv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Sender and recipient settings
        $mail->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Reminder';

        // HTML email body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #4CAF50; text-align: center;'>San Teodoro MSWD</h2>
                    <h3 style='color: #333333; text-align: center;'>Appointment Reminder</h3>
                    <p>Dear {$schedule['user']},</p>
                    <p>This is a friendly reminder about your upcoming appointment:</p>
                    <p><strong>Date:</strong> {$schedule['date']}</p>
                    <p><strong>Description:</strong> {$schedule['description']}</p>
                    <p>Please make sure to attend your appointment as scheduled. If you need to reschedule or have any questions, please contact San Teodoro MSWD as soon as possible.</p>
                    <p>We look forward to seeing you.</p>
                    <br>
                    <p>Best regards,<br>San Teodoro MSWD Team</p>
                    <p style='color: #555555; text-align: center; font-size: 14px; margin-top: 20px;'>
                        For any inquiries, please contact San Teodoro MSWD.
                    </p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        log_message('error', "Reminder email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}


    private function getUserEmail($userName)
    {
        // Try to find the user in PWD model
        $pwdUser = $this->pwdModel->where('fullName', $userName)->first();
        if ($pwdUser && isset($pwdUser['email'])) {
            return $pwdUser['email'];
        }

        // If not found, try in Membership model
        $memberUser = $this->membershipModel->where('name', $userName)->first();
        if ($memberUser && isset($memberUser['email'])) {
            return $memberUser['email'];
        }

        return null;
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
    

private function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stmswdapp@gmail.com'; // Your Gmail address
        $mail->Password   = 'kamp eoxb tobq rplv'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Sender and recipient settings
        $mail->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(false); // Set to `true` if HTML email is needed
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        log_message('error', "Email could not be sent. Error: {$mail->ErrorInfo}");
        throw new \RuntimeException('Unable to send email.');
    }
}
public function acceptRequest()
{
    $input = $this->request->getJSON();
    $requestId = $input->requestId;
    $request = $this->pwdModel->find($requestId);

    if (!$request) {
        return $this->failNotFound('Request not found');
    }

    $this->pwdModel->update($requestId, ['status' => 'Accepted']);

    // Add to schedule
    $this->scheduleModel->insert([
        'user' => $request['fullName'],
        'date' => date('Y-m-d'), // You might want to set a specific date
        'description' => 'Accepted PWD request',
        'status' => 'pending',
        'request_id' => $requestId
    ]);

    // Enhanced acceptance email message
    $emailBody = "
        Dear {$request['fullName']},

        We are pleased to inform you that your assistance request has been accepted by the San Teodoro Municipal Social Welfare and Development Office (MSWD). 

        Your request is now being processed, and we will notify you with your specific schedule shortly. Please ensure that your contact information is up-to-date so you can receive further notifications without delay.

        If you have any questions or need further assistance, feel free to reach out to us at stmswdapp@gmail.com.

        Thank you for your patience and trust in our services.

        Best regards,
        San Teodoro MSWD
    ";

    // Send acceptance email
    $this->sendEmail($request['email'], 'Your Assistance Request Has Been Accepted', $emailBody);

    return $this->respond(['message' => 'Request accepted and scheduled successfully'], 200);
}
public function rejectRequest()
{
    $input = $this->request->getJSON();
    $model = new PWDModel();

    $requestId = $input->requestId; // Ensure matches JavaScript payload
    $request = $model->find($requestId);

    if (!$request) {
        return $this->failNotFound('Request not found');
    }

    // Enhanced rejection email message
    $emailBody = "
        Dear {$request['fullName']},

        We regret to inform you that your assistance request submitted to the San Teodoro Municipal Social Welfare and Development Office (MSWD) has not been approved. 

        While we strive to accommodate all requests, there are instances where we are unable to proceed due to specific circumstances. If you would like more information about this decision or wish to submit a new request, please do not hesitate to contact us at stmswdapp@gmail.com.

        We sincerely appreciate your understanding and encourage you to reach out if there is anything further we can assist you with.

        Best regards,
        San Teodoro MSWD
    ";

    // Send rejection email
    $this->sendEmail($request['email'], 'Your Assistance Request Status', $emailBody);

    // Delete the request from the database
    $model->delete($requestId);

    return $this->respond(['message' => 'Request rejected and deleted successfully'], 200);
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
        'category'   => 'required|in_list[Senior,PWD,Solo Parent]',
        'address'    => 'required|min_length[5]|max_length[255]',
        'certificate' => [
            'uploaded[certificate]',
            'mime_in[certificate,image/jpg,image/jpeg,image/png,image/gif]',
            'max_size[certificate,2048]', // Max 2MB
        ],
        'idnumber'   => 'if_exist|regex_match[/^PWD-[A-Z0-9]{9}$/]',
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

    // Generate a random ID number if not provided
    $idnumber = $this->request->getPost('idnumber') ?? $this->generateRandomId();

    // Prepare data for insertion
    $data = [
        'name'       => $this->request->getPost('name'),
        'dob'        => $this->request->getPost('dob'),
        'sickness'   => $this->request->getPost('sickness'),
        'category'   => $this->request->getPost('category'),
        'address'    => $this->request->getPost('address'),
        'certificate'=> 'uploads/certificates/' . $newName,
        'idnumber'   => $idnumber,
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

private function generateRandomId()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = 'PWD-';
    for ($i = 0; $i < 9; $i++) {
        $id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $id;
}

    public function Members()
    {
        // Get all data from the MembershipModel
        $model = new MembershipModel();
        $data = $model->findAll();

        return $this->respond($data, 200);
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

    // Basic validation
    if (!isset($input->user) || !isset($input->date) || !isset($input->description)) {
        return $this->fail('Missing required fields', 400);
    }

    $data = [
        'user' => $input->user,
        'date' => $input->date,
        'description' => $input->description,
        'status' => $input->status ?? 'pending',
        'request_id' => $input->request_id ?? null
    ];

    try {
        $updated = $this->scheduleModel->update($id, $data);

        if ($updated) {
            return $this->respond(['message' => 'Schedule updated successfully']);
        } else {
            return $this->fail('Failed to update schedule', 400);
        }
    } catch (\Exception $e) {
        log_message('error', 'Error updating schedule: ' . $e->getMessage());
        return $this->fail('An error occurred while updating the schedule', 500);
    }
}

    public function editMembership($id = null)
    {
        if ($id === null) {
            return $this->failNotFound('No membership ID provided');
        }

        $membership = $this->membershipModel->find($id);

        if ($membership === null) {
            return $this->failNotFound('Membership not found');
        }

        // Validation rules
        $rules = [
            'name'     => 'required|min_length[3]|max_length[255]',
            'dob'      => 'required|valid_date',
            'sickness' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Senior,PWD,Solo Parent]',
            'address'  => 'required|min_length[5]|max_length[255]',
            'certificate' => 'if_exist|uploaded[certificate]|max_size[certificate,2048]|mime_in[certificate,image/jpg,image/jpeg,image/png,image/gif]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }

        // Prepare data for update
        $data = [
            'name'     => $this->request->getPost('name'),
            'dob'      => $this->request->getPost('dob'),
            'sickness' => $this->request->getPost('sickness'),
            'category' => $this->request->getPost('category'),
            'address'  => $this->request->getPost('address'),
        ];

        // Handle file upload if a new certificate is provided
        $certificate = $this->request->getFile('certificate');
        if ($certificate && $certificate->isValid()) {
            $newName = $certificate->getRandomName();
            $certificate->move(WRITEPATH . 'uploads/certificates', $newName);
            $data['certificate'] = 'uploads/certificates/' . $newName;

            // Delete the old certificate file if it exists
            if (!empty($membership['certificate'])) {
                $oldCertificatePath = WRITEPATH . $membership['certificate'];
                if (file_exists($oldCertificatePath)) {
                    unlink($oldCertificatePath);
                }
            }
        }

        // Update the membership
        if ($this->membershipModel->update($id, $data)) {
            // Create a notification for the membership update
            $this->createNotification('membership', 'Membership information updated');

            return $this->respond([
                'status'  => 'success',
                'message' => 'Membership updated successfully.',
                'data'    => $this->membershipModel->find($id),
            ]);
        } else {
            return $this->fail('Failed to update membership', 500);
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