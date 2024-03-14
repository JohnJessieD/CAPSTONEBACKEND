<?php
namespace App\Controllers;

use App\Models\PWDModel;
use CodeIgniter\API\ResponseTrait;

class PWD extends BaseController
{
    use ResponseTrait;

    public function requestMoney()
    {
        $input = $this->request->getJSON();

        // Perform validation on $input if needed

        // Process the money request and store it in the database
        $model = new PWDModel();
        $model->insert($input);

        return $this->respond(['message' => 'Money request submitted successfully'], 200);
    }
}
