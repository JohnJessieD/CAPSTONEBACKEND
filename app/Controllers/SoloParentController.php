<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Restful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SoloParentModel;

class SoloParentController extends ResourceController
{
    use ResponseTrait;

    public function submitApplication()
    {
        // Get input data from the request
        $input = $this->request->getPost(); // Assuming you're using POST method

        // Validate input data if necessary
        
        // Create a new instance of the model
        $model = new SoloParentModel();

        // Insert the data into the database
        $model->insert($input);

        // Respond with success message
        return $this->respond( ['message' => 'Money request submitted successfully'], 200);
    }
}
