<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('common');
//        $this->load->model('itemmodel');
    }

    function upload_image() {
        $image_type = $this->input->post('image_type');
        $res = array();
        if ($image_type == 'news_slider-image') {
            $res = $this->common->upload_image(
                    'news_slider-image', NEWS_SLIDER_MINWIDTH, NEWS_SLIDER_MINHEIGHT, NEWS_SLIDER_MAXWIDTH, NEWS_SLIDER_MAXHEIGHT, NEWS_SLIDER_MAXSIZE, NEWS_SLIDER_ALLOWEDTYPES, NEWS_SLIDER_UPLOAD_PATH, TRUE);
        } else if ($image_type == 'school-news-image') {
            $res = $this->common->upload_image(
                    'school-news-image', SCHOOL_NEWS_MINWIDTH, SCHOOL_NEWS_MINHEIGHT, SCHOOL_NEWS_MAXWIDTH, SCHOOL_NEWS_MAXHEIGHT, SCHOOL_NEWS_MAXSIZE, SCHOOL_NEWS_ALLOWEDTYPES, SCHOOL_NEWS_UPLOAD_PATH, TRUE);
        } else if ($image_type == 'event_file-image') {
            $res = $this->common->upload_image(
                    'event_file-image', EVENT_FILE_MINWIDTH, EVENT_FILE_MINHEIGHT, EVENT_FILE_MAXWIDTH, EVENT_FILE_MAXHEIGHT, EVENT_FILE_MAXSIZE, EVENT_FILE_ALLOWEDTYPES, EVENT_FILE_UPLOAD_PATH, TRUE);
        } else if ($image_type == 'member-image') {
            $res = $this->common->upload_image(
                    'member-image', MEMBER_IMAGE_MINWIDTH, MEMBER_IMAGE_MINHEIGHT, MEMBER_IMAGE_MAXWIDTH, MEMBER_IMAGE_MAXHEIGHT, MEMBER_IMAGE_MAXSIZE, MEMBER_IMAGE_ALLOWEDTYPES, MEMBER_IMAGE_UPLOAD_PATH, TRUE);
        } else if ($image_type == 'agenda-image') {
            $res = $this->common->file_upload('agenda-image', MEETING_IMAGE_UPLOAD_PATH, MEETING_ALLOWEDTYPES, MEETING_MAXSIZE);
        } else if ($image_type == 'minute-image') {
            $res = $this->common->file_upload('minute-image', MEETING_IMAGE_UPLOAD_PATH, MEETING_ALLOWEDTYPES, MEETING_MAXSIZE);
        } else if ($image_type == 'accounts-image') {
            $res = $this->common->file_upload('accounts-image', MEETING_IMAGE_UPLOAD_PATH, MEETING_ALLOWEDTYPES, MEETING_MAXSIZE);
        }

        if ($res['error_no'] == 0) {
            Ajax::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'Image Upload', "File Name: " . $res['upload_data']['file_name']);
        }
        header('Content-Type: application/json');
        echo json_encode($res);
    }

    function get_table_data($entity) {
        $allowed_entities = array('brand');
        $results = array();
        if (in_array($entity, $allowed_entities)) {
            $data = $this->itemmodel->get_all_brands(TRUE);
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    function toggle_enable() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $table_name = $this->input->post('table');
            $id = $this->input->post('id');
            if ($table_name != null && strlen(trim($table_name)) > 0 && strlen(trim($table_name)) < 25) {
                if ($id != null && strlen(trim($id)) > 0 && strlen(trim($id)) < 4) {
                    if (!$this->common->toggle_enable($table_name, $id, 1)) {
                        $error_no = 41;
                    }
                }
            }
        } else {
            $error_no = 1;
        }
        if ($error_no != 0) {
            $error = $this->config->item($error_no, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function password_reset_request() {
        $error_no = 0;
        $error = '';
        $username = $this->input->post('forgot-username');
        $code = '';
        if ($username != NULL) {
            $username = trim($username);
            $length = strlen($username);
            if ($length > 0 && $length < 100) {
                //get user details
                $this->load->model('Sed_core_user');
                $user_obj = Sed_core_user::get_user_by_username_recovery_mail($username);
                if ($user_obj && isset($user_obj['username']) && isset($user_obj['id'])) {
                    if ($user_obj['recovery_email'] != NULL && strlen($user_obj['recovery_email']) > 0) {
                        //create new password reset code
                        $code = $this->common->get_unique_code(8, 'sed_user_code', 'code');
                        //$unread_messages = $this->get_unread_messages_by_member($user_obj['id']);
                        //$msg_count = count($unread_messages);
                        $_SESSION['member'] = $user_obj;
                        //$_SESSION['member']['unread_messages'] = $msg_count;
                        //create reset password record and send email
                        if (!$this->Sed_core_user->reset_password_by_email($user_obj['id'], $user_obj['recovery_email'], $code)) {
                            $error_no = 160;
                        } else {
                            Ajax::AddAuditTrailEntry(AUDITTRAIL_INFORMATION, 'Password Reset Reqest', "Username : " . $user_obj['username']);
                        }
                    } else {
                        $error_no = 159;
                    }
                } else {
                    $error_no = 158;
                }
            } else {
                $error_no = 157;
            }
        } else {
            $error_no = 157;
        }
        if ($error_no != 0) {
            $error = $this->config->item($error_no, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error, 'code' => $code));
    }

}
