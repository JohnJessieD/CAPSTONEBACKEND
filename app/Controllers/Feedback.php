<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FeedbackModel;
use CodeIgniter\API\ResponseTrait;

class Feedback extends ResourceController
{
    protected $modelName = 'App\Models\FeedbackModel';


    public function submitFeedback()
    {
        $data = $this->request->getJSON(true);

        // Validate data as needed

        $Feedback = new FeedbackModel();
        $Feedback->insert($data);

        return $this->respondCreated(['message' => 'Feedback created successfully']);
    }
}