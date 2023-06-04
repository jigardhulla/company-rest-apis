<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employees extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Department_model');
        $this->load->model('Employee_model');
        $this->load->library('form_validation');
        $this->load->library('MY_Form_validation');
    }

    public function index()
    {
        // Get the search keyword from the request
        $keyword = $this->input->get('keyword');
        try {
            // Retrieve search or all Employees
            $employees = $this->Employee_model->getEmployees($keyword);

            if ($employees) {
                // Return the departments as JSON
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'employees' => $employees]));
            } else {
                // Return the empty message
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'No employees found']));
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
            $employeeData = json_decode($inputJSON, true);

            // Access the JSON data
            if ($employeeData !== null) {
                // Validate the input
                $this->form_validation->set_data($employeeData);
                $this->form_validation->set_rules('name', 'Name', 'required|alpha_space');
                $this->form_validation->set_message('alpha_space', 'The {field} field may only contain alphabets and spaces.');
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
                $this->form_validation->set_rules('department_id', 'Department ID', 'required|integer');
                $this->form_validation->set_rules('contact_numbers[]', 'contact Numbers', 'numeric');
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
                        if (!$this->Department_model->getDepartmentById(($employeeData['department_id']))) {
                            // Department does not exist
                            $this->output
                                ->set_content_type('application/json')
                                ->set_status_header(400)
                                ->set_output(json_encode(['success' => false, 'message' => 'Department does not exist']));

                        } elseif ($this->Employee_model->getEmployeeByEmail($employeeData['email'])) {
                            // Already exist Employee
                            $this->output
                                ->set_content_type('application/json')
                                ->set_status_header(400)
                                ->set_output(json_encode(['success' => false, 'message' => 'Employee already exist']));
                        } else {

                            // Create the Employee
                            $data = array();
                            $data['name'] = $employeeData['name'];
                            $data['email'] = $employeeData['email'];
                            $data['department_id'] = $employeeData['department_id'];
                            $employeeId = $this->Employee_model->createEmployee($data);

                            // Add contact numbers
                            if (isset($employeeData['contact_numbers'])) {
                                foreach ($employeeData['contact_numbers'] as $contactNumber) {
                                    $this->Employee_model->addContactNumber($employeeId, $contactNumber);
                                }
                            }

                            // Add addresses
                            if (isset($employeeData['addresses'])) {
                                foreach ($employeeData['addresses'] as $address) {
                                    $this->Employee_model->addAddress($employeeId, $address);
                                }
                            }

                            // Return the created Employee as JSON
                            $employee = $this->Employee_model->getEmployeeById($employeeId);
                            $this->output
                                ->set_status_header(201)
                                ->set_content_type('application/json')
                                ->set_output(json_encode(['success' => true, 'employee' => $employee]));
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

        // Check if the employee exists
        $employee = $this->Employee_model->getEmployeeById($id);
        if (!$employee) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Employee not found'
                ]));
        } else {
            // Delete the employee
            if ($this->Employee_model->deleteEmployee($id)) {
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'success',
                        'message' => 'Employee and associated data deleted successfully'
                    ]));
            } else {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Failed to delete employee'
                    ]));
            }
        }
    }

    public function edit($id)
    {
        // Make sure that the request is sending JSON data
        if ($this->input->server('CONTENT_TYPE') === 'application/json') {
            // Get the raw HTTP request body
            $inputJSON = file_get_contents('php://input');
            
            // Decode the JSON data
            $employeeData = json_decode($inputJSON, true);

            // Access the JSON data
            if ($employeeData !== null) {

                if (!$this->Employee_model->getEmployeeById($id)) {
                    // Employee does not exist
                    $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(400)
                        ->set_output(json_encode(['success' => false, 'message' => 'Employee does not exist']));
                    
                } else {
                    // Validate the input
                    $this->form_validation->set_data($employeeData);
                    $this->form_validation->set_rules('name', 'Name', 'required|alpha_space');
                    $this->form_validation->set_message('alpha_space', 'The {field} field may only contain alphabets and spaces.');
                    $this->form_validation->set_rules('department_id', 'Department ID', 'required|integer');
                    $this->form_validation->set_rules('contact_numbers[]', 'contact Numbers', 'numeric');
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
                            if (!$this->Department_model->getDepartmentById(($employeeData['department_id']))) {
                                // Department does not exist
                                $this->output
                                    ->set_content_type('application/json')
                                    ->set_status_header(400)
                                    ->set_output(json_encode(['success' => false, 'message' => 'Department does not exist']));

                            } else {

                                // Update the Employee
                                if ($this->Employee_model->updateEmployee($id, $employeeData)) {
                                    // Return the created Employee as JSON
                                    $employee = $this->Employee_model->getEmployeeById($id);
                                    $this->output
                                        ->set_status_header(201)
                                        ->set_content_type('application/json')
                                        ->set_output(json_encode(['success' => true, 'employee' => $employee]));
                                } else {
                                    // Failed
                                    $this->output
                                        ->set_content_type('application/json')
                                        ->set_status_header(400)
                                        ->set_output(json_encode(['success' => false, 'message' => 'Failed']));
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Handle the exception
                        $this->output
                            ->set_content_type('application/json')
                            ->set_status_header(500)
                            ->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
                    }
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
}
