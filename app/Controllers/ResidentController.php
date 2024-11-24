<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ResidentModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResidentController extends ResourceController
{
    use ResponseTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new ResidentModel();
    }

    public function index()
    {
        $category = $this->request->getGet('category');
        $search = $this->request->getGet('search');
        $barangay = $this->request->getGet('barangay');
        $page = $this->request->getGet('page', FILTER_VALIDATE_INT) ?? 1;
        $limit = $this->request->getGet('limit', FILTER_VALIDATE_INT) ?? 10;

        $query = $this->model;

        if ($category) {
            $query = $query->where('category', $category);
        }

        if ($barangay) {
            $query = $query->like('address', $barangay);
        }

        if ($search) {
            $query = $query->groupStart()
                           ->like('first_name', $search)
                           ->orLike('last_name', $search)
                           ->orLike('id_number', $search)
                           ->groupEnd();
        }

        $total = $query->countAllResults(false);
        $residents = $query->paginate($limit, '', $page);

        return $this->respond([
            'residents' => $residents,
            'pager' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    public function show($id = null)
    {
        $resident = $this->model->find($id);
        if ($resident) {
            return $this->respond($resident);
        }
        return $this->failNotFound('Resident not found');
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        $residentData = [
            'category' => $data['category'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'email' => $data['email'] ?? null,
        ];

        $details = $data['details'] ?? [];
        $residentData['details'] = json_encode($details);

        if ($this->model->insert($residentData)) {
            return $this->respondCreated(['message' => 'Resident created successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        if ($id === null) {
            return $this->fail('No ID provided', 400);
        }

        $data = $this->request->getJSON(true);

        $residentData = [
            'category' => $data['category'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'email' => $data['email'] ?? null,
        ];

        $details = $data['details'] ?? [];
        $residentData['details'] = json_encode($details);

        if ($this->model->update($id, $residentData)) {
            $updatedResident = $this->model->find($id);
            return $this->respond([
                'status' => 200,
                'message' => 'Resident updated successfully',
                'resident' => $updatedResident
            ]);
        }

        return $this->fail($this->model->errors() ?: 'Failed to update the resident', 500);
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Resident deleted successfully']);
        }
        return $this->fail('Failed to delete the resident');
    }

    public function exportExcel()
    {
        $residents = $this->model->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID Number', 'Name', 'Category', 'Date of Birth', 'Gender', 'Address', 'Contact Number', 'Email'];
        $sheet->fromArray([$headers], NULL, 'A1');

        $row = 2;
        foreach ($residents as $resident) {
            $sheet->fromArray([
                $resident['id_number'],
                $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name'],
                $resident['category'],
                $resident['date_of_birth'],
                $resident['gender'],
                $resident['address'],
                $resident['contact_number'],
                $resident['email']
            ], NULL, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'residents_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function generateReport()
    {
        $category = $this->request->getGet('category');
        $barangay = $this->request->getGet('barangay');

        $query = $this->model;

        if ($category) {
            $query = $query->where('category', $category);
        }

        if ($barangay) {
            $query = $query->like('address', $barangay);
        }

        $residents = $query->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID Number', 'Name', 'Category', 'Date of Birth', 'Gender', 'Address', 'Contact Number', 'Email'];
        $sheet->fromArray([$headers], NULL, 'A1');

        $row = 2;
        foreach ($residents as $resident) {
            $sheet->fromArray([
                $resident['id_number'],
                $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name'],
                $resident['category'],
                $resident['date_of_birth'],
                $resident['gender'],
                $resident['address'],
                $resident['contact_number'],
                $resident['email']
            ], NULL, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'residents_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getBarangays()
    {
        $barangays = $this->model->select('address')->distinct()->findAll();
        $uniqueBarangays = array_unique(array_column($barangays, 'address'));
        return $this->respond(array_values($uniqueBarangays));
    }

    public function getDashboardStats()
    {
        $stats = [
            'total_residents' => $this->model->countAll(),
            'pwd_count' => $this->model->where('category', 'PWD')->countAllResults(),
            'senior_citizen_count' => $this->model->where('category', 'Senior Citizen')->countAllResults(),
            'solo_parent_count' => $this->model->where('category', 'Solo Parent')->countAllResults(),
        ];

        return $this->respond($stats);
    }
}