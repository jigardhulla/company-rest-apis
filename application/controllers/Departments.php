<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Departments extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Department_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        try {
            // Retrieve all departments
            $departments = $this->Department_model->getDepartments();

            if ($departments) {
                // Return the departments as JSON
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'departments' => $departments]));
            } else {
                // Return the empty message
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'No Departments found']));
            }
        } catch (Exception $e) {
            // Handle the exception
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    public function create()
    {
        // Make sure that the request is sending JSON data
        if ($this->input->server('CONTENT_TYPE') === 'application/json') {
            // Get the raw HTTP request body
            $inputJSON = file_get_contents('php://input');
            
            // Decode the JSON data
            $departmentData = json_decode($inputJSON, true);

            // Access the JSON data
            if ($departmentData !== null) {
                // Validate the input
                $this->form_validation->set_data($departmentData);
                $this->form_validation->set_rules('name', 'Name', 'required|alpha_space');
                try {
                    if ($this->form_validation->run() === FALSE)
                    {
                        // Validation failed, return error response
                        $this->output
                            ->set_status_header(400)
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['success' => false, 'message' => validation_errors()]));
                    }
                    else
                    {
                        if ($this->Department_model->getDepartmentByName($departmentData['name'])) {
                            // Already exist department
                            $this->output
                                ->set_content_type('application/json')
                                ->set_status_header(400)
                                ->set_output(json_encode(['success' => false, 'message' => 'Department already exist']));
                        } else {

                            // Create the department
                            $data = array();
                            $data['name'] = $departmentData['name'];
                            $data['description'] = $departmentData['description'];
                            $departmentId = $this->Department_model->createDepartment($data);

                            // Return the created department as JSON
                            $department = $this->Department_model->getDepartmentById($departmentId);
                            $this->output
                                ->set_status_header(201)
                                ->set_content_type('application/json')
                                ->set_output(json_encode(['success' => true, 'departments' => $department]));
                        }
                    }
                } catch (Exception $e) {
                    // Handle the exception
                    $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(500)
                        ->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
                }
            } else {
                // Invalid JSON data
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['success' => false, 'message' => 'Invalid JSON data']));
            }
        } else {
            // Invalid request content type
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid request content type']));
        }
    }

    public function delete($id)
    {
        // Check if the department exists
        $department = $this->Department_model->getDepartmentById($id);
        if (!$department) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Department not found'
                ]));
        } else {
            // Delete the department
            if ($this->Department_model->deleteDepartment($id)) {
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'success',
                        'message' => 'Department and associated employees deleted successfully'
                    ]));
            } else {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Failed to delete department'
                    ]));
            }
        }
    }

}
