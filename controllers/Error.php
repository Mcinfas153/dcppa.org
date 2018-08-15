<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Error extends CI_Controller {

    public function index() {
        //show error modal
        if (!isset($_SESSION)) {
            session_start();
        }
        $message = '';
        if (isset($_SESSION['error_handler_message'])) {
            $message = $_SESSION['error_handler_message'];
            unset($_SESSION['error_handler_message']);
        } else {
            $message = "No error to display";
        }
        $data['message'] = $message;
        $this->load->view('error_modal.php', $data);
    }
    
    public function page($error_id){
        $data['error_code'] = $error_id;
        $this->load->view('error_page', $data);
    }

}
