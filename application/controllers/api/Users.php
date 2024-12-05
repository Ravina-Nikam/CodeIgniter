<?php

require_once(APPPATH . 'libraries/REST_Controller.php');

class Users extends REST_Controller {

    public function __construct() {
        parent::__construct();
        // Load any models you need here
        // $this->load->model('Item_model');
        $this->load->database();
        
        header('Access-Control-Allow-Origin:*');
        // header('Access-Control-Allow-Origin: http://localhost:3001');  // Allow from React app
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Allow methods
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); // Allow headers

    }

    // Fetch all items
    function getDefaultResponseBody_get(){
		$response = array();

		$response['success'] 	= false;
		$response['error'] 		= true;
		$response['message'] 	= 'Bad request';
		$response['code'] 		= 400;
		$response['data'] 		= array();

		return $response;  
	}
    function setErrorMessageAndStatusCode(&$response, $errorMsg, $statusCode = 200){

		if(is_array($errorMsg)){
			$errorMsg = ucwords(implode('|',$errorMsg));
		}

		$response['message'] = $errorMsg;
		$response['error'] = true;
		$response['success'] = false; 
		$response['code'] = $statusCode;
	}

	function setResponseBody_get(&$response, $actualData, $message = 'Data found'){
		if(isset($response['data'])){
			$response['data'] = $actualData;
			$response['success'] = true;
			$response['error'] = false;
			$response['message'] = $message;
			$response['code'] = 200;
		}	
	}

    // Create a new item (POST request)
    public function create_acc_post() {
        $response = $this->getDefaultResponseBody_get();
        $data = $this->post();
        if(empty($data)){
            $this->setErrorMessageAndStatusCode($response, "Sorry User Data is empty",$this->lang->line('amount_required'));
            return $this->response($response,200);
            // return $data;setResponseBody
        }

        $name  = $data['name'];
        $email = $data['email'];
        $dob   = $data['dob'];
        $data =  array("name"=>$name,"email"=>$email,"dob"=>$dob);
        $this->db->insert("user",$data);


        $last_insert_id = $this->db->insert_id();
        if( $this->db->affected_rows() > 0){     
            $this->setResponseBody_get($response,array("name"=>$name),"!! User Created Successfully. !!");
            return $this->response($response,200);   
        }else{
            $this->setErrorMessageAndStatusCode($response, "User is not added please check again...",$this->lang->line('amount_required'));
            return $this->response($response,200);

        }
    }
    //get user
    public function index_get() {
        $response = $this->getDefaultResponseBody_get();
        // You can load data from a model or database here
        $getData = $this->db->query("select id,name,email,dob from user")->result_array();
        if(!empty($getData)){
            $this->setResponseBody_get($response,$getData,"!! Users Details Fetch Successfully. !!");
            return $this->response($response,200);  
        }else{
            $this->setErrorMessageAndStatusCode($response, "Sorry No user Present",$this->lang->line('amount_required'));
            return $this->response($response,200);
        }

        // Send response with status 200 (OK)
        // $this->response($data, REST_Controller::HTTP_OK);
    }

    public function delete_acc_delete() {
        $response = $this->getDefaultResponseBody_get();
        $data = json_decode(file_get_contents('php://input'), true); // Parse JSON data

        // Check if data is empty
        if(empty($data) || !isset($data['user_id'])) {
            $this->setErrorMessageAndStatusCode($response, "User ID is required to delete", $this->lang->line('amount_required'));
            return $this->response($response, 200);
        }

        // Get the user_id from the request data
        $user_id = $data['user_id'];
        // Delete the user from the database
        $this->db->where('id', $user_id);
        $this->db->delete('user');

        // Check if the deletion was successful
        if($this->db->affected_rows() > 0){
            $this->setResponseBody_get($response, array("user_id" => $user_id), "!! User Deleted Successfully. !!");
            return $this->response($response, 200);
        } else {
            $this->setErrorMessageAndStatusCode($response, "User not found or already deleted.", $this->lang->line('amount_required'));
            return $this->response($response, 200);
        }
    }

    public function update_acc_put() {
        $response = $this->getDefaultResponseBody_get();
        
        // Retrieve raw input data from the PUT request body
        $data = json_decode(file_get_contents('php://input'), true); // Parse JSON data
        
        // echo "<pre>";
        // print_r($data);
        // exit();

        // Check if the necessary data is provided
        if (empty($data) || !isset($data['id']) || !isset($data['name']) || !isset($data['email']) || !isset($data['dob'])) {
            $this->setErrorMessageAndStatusCode($response, "User ID, name, email, and dob are required for update.", $this->lang->line('amount_required'));
            return $this->response($response, 200);
        }
    
        // Get the data from the request
        $user_id = $data['id'];
        $name = $data['name'];
        $email = $data['email'];
        $dob = $data['dob'];
    
        // Prepare data for updating the user
        $update_data = array(
            "name" => $name,
            "email" => $email,
            "dob" => $dob,
            "update_at" => date("Y-m-d H:i:s") 
        );
    


        // Debugging: Print the received data (for testing purposes)
        // echo "<pre>";
        // print_r($update_data);
        // exit(); // Remove this line after debugging
    
        // Update the user in the database
        $this->db->where('id', $user_id);
        $this->db->update('user', $update_data);
    
        // Check if the update was successful
        if ($this->db->affected_rows() > 0) {
            // If the update was successful, return success response
            $this->setResponseBody_get($response, array("user_id" => $user_id), "!! User Updated Successfully. !!");
            return $this->response($response, 200);
        } else {
            // If the update failed, return failure response
            $this->setErrorMessageAndStatusCode($response, "No changes made or user not found.", $this->lang->line('amount_required'));
            return $this->response($response, 200);
        }
    }
    



}
