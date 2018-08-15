<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Member_model');
        $this->load->model('common');
    }

    public function check_member_login() {
        $error_no = 0;
        $error_message = NULL;
        $email = $this->input->post('email');
        $a = strlen(trim($email));
        if ($email != NULL && ($a > 0 && $a < 80)) {
            $password = $this->input->post('password');
            $b = strlen(trim($password));
            if ($password != NULL && ($b > 0 && $b < 50)) {
                $login = FALSE;
                $res = $this->Member_model->login_member($email, $password);
                if ($res != FALSE && count($res) > 0) {
                    if ($res['enabled'] == 1) {
                        $_SESSION['member'] = $res;
                        $login = TRUE;
                        Member::AddAuditTrailEntry(AUDITTRAIL_LOGIN, 'Login Success', "Username: " . $email . "|IP: " . $this->input->ip_address());
                    } else {
                        $error_no = 702;
                    }
                } else {
                    $error_no = 2;
                }
            } else {
                $error_no = 700; //invalid credentials
            }
        } else {
            $error_no = 700; //invalid credentials
        }
        if ($error_no > 0) {
            $error_message = $this->config->item($error_no, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error_msg' => $error_message));
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
                $user_obj = $this->Member_model->get_member_by_mail($username);
                if ($user_obj && isset($user_obj['id'])) {
                    if ($user_obj['email'] != NULL && strlen($user_obj['email']) > 0) {
                        //create new password reset code
                        $code = $this->common->get_unique_code(8, 'member_code', 'code');
                        //create reset password record and send email
                        if (!$this->Member_model->reset_password_by_email($user_obj['id'], $user_obj['email'], $code)) {
                            $error_no = 160;
                        } else {
                            Member::AddAuditTrailEntry(AUDITTRAIL_INFORMATION, 'Password Reset Reqest', "Username : " . $user_obj['email']);
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

    public function reset_password($code) {
        $error_id = 0;
        if ($code != NULL) {
            $code = trim($code);
            if (strlen($code) > 0 && strlen($code) < 100) {
                //check if code is valid
                $status = $this->Member_model->is_valid_code($code);
                if ($status == '0') { //valid code
                    $user = $this->Member_model->get_user_by_password_reset_code($code);
                    $unread_messages = $this->Member_model->get_unread_messages_by_member($user['id']);
                    $msg_count = count($unread_messages);
                    $user['unread_messages'] = $msg_count;
                    $data['name'] = $user['first_name'];
                    $data['code'] = $code;
                    $data['common_modals'] = $this->load->view('site/includes/common_modals', '', TRUE);
                    $_SESSION['reset_code'] = $code;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['member'] = TRUE;
                    $_SESSION['member'] = $user;
                    $this->load->view('site/reset_password', $data);
                } else {
                    switch ($status) {
                        case 1://invalid code
                            $error_id = 161;
                            break;
                        case 2://code has been used
                            $error_id = 162;
                            break;
                        case 3://code expired
                            $error_id = 163;
                            break;
                        case 4://code expired
                            $error_id = 164;
                    }
                }
            } else {
                $error_id = 161;
            }
        } else {
            $error_id = 161;
        }
        if ($error_id > 0) {
            redirect(base_url('error/page/' . $error_id));
        }
    }

    public function update_password() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['reset_code'])) {
            $reset_code = $_SESSION['reset_code'];
            $code = trim($this->input->post('reset-code'));
            if (strlen($code) > 0 && strlen($code) < 100 && $code == $reset_code) {
                $password = trim($this->input->post('password'));
                if (strlen($password) >= 8 && strlen($password) < 20) {
                    //update passsword
                    $user_id = $_SESSION['user_id'];
                    $this->db->trans_start();
                    if (!$this->Member_model->update_password($user_id, $password)) {
                        $error_no = 167;
                    } else {
                        //mark code as used
                        if ($this->Member_model->invalidate_code($code)) {
                            $this->db->trans_complete();
                            unset($_SESSION['reset_code']);
                            unset($_SESSION['user_id']);
                            Member::AddAuditTrailEntry(AUDITTRAIL_INFORMATION, 'Member Password Update', 'User ID:' . $user_id);
                        }
                    }
                } else {
                    $error_no = 166;
                }
            } else {
                $error_no = 165;
            }
        }
        if ($error_no > 0) {
            $this->db->trans_rollback();
            $error = $this->config->item($error_no, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function logout() {
        if (isset($_SESSION['member'])) {
            Member::AddAuditTrailEntry(AUDITTRAIL_LOGOUT, 'Logout', "Username: " . $_SESSION['member']['first_name'] . "|IP: " . $this->input->ip_address());
            unset($_SESSION['member']);
        }
        redirect('site');
    }

    public function send_mail_to_member() {
        $status = 1;
        $msg = '';
        if ($_POST['sender_id'] && $_POST['sender_id'] > 0 && isset($_POST['sender_id'])) {
            $data['receiver_id'] = $this->input->post('receiver_id');
            $data['sender_id'] = $this->input->post('sender_id');
            $data['color'] = $this->input->post('color');
            $data['subject'] = $this->input->post('subject');
            $data['message_body'] = $this->input->post('message_body');
            $res = $this->Member_model->send_mail_to_member($data);
            if ($res) {
                $get_sender_details = $this->Member_model->get_member_details_by_id($data['sender_id']);
                $get_receiver_details = $this->Member_model->get_member_details_by_id($data['receiver_id']);
                if ($get_receiver_details['email'] != '' || $get_receiver_details['email'] != NULL) {
                    $created_date = date('Y-m-d H:i:s');
                    $date = $this->Common->convert_to_business_zone_time($created_date, LOCAL_TIME_ZONE);

                    $email_info = array(
                        'created_on' => $date,
                        'sender_name' => $get_sender_details['first_name'] . $get_sender_details['last_name'],
                        'subject' => $data['subject'],
                        'message_body' => $data['message_body'],
                    );

                    $email_content_deatils = $this->Common->get_template_by_id(MESSAGE_NOTIFICATION_TEMPLATE);
                    if (!empty($email_content_deatils)) {
                        $email_content = $email_content_deatils['content'];

                        if ($email_success = $this->common->easy_mail($get_receiver_details['email'], 'DCPPA Messsanger Service', $email_info, $email_content)) {
                            $msg = 713;
                            $status = 0;
                            //email success
                        } else {
                            $msg = 714;
                            //email failed
                        }
                    } else {
                        //Template not found
                        $msg = 48;
                    }
                } else {
                    //received email not found
                    $msg = 715;
                }
            } else {
                //Insert failed
                $msg = 49;
            }
        } else {
            //Sender id invalid
            $msg = 716;
        }

        header('Content-Type: application/json');
        echo json_encode(array('status' => $status, 'msg' => $msg));
    }

    public function add_member() {
        $error_no = 0;
        $error = '';
        $success = FALSE;
        $data = array();
        $data = $this->input->post('data');


        if (!$this->Member_model->add_new_pending_member($data)) {
            $error_no = 730;
            $error = $this->config->item($error_no, 'msg');
        } else {
            $email_content_deatils = $this->Common->get_template_by_id(MEMBER_REQUEST_NOTIFICATION);
            if (!empty($email_content_deatils)) {
                $email_content = $email_content_deatils['content'];
                $email_success = $this->common->easy_mail(MEMBER_REQUESTED_EMAIL, 'DCPPA Member Request', $data, $email_content);
                if ($email_success == true) {
                    $success = TRUE;
                    $error_no = 719;
                    $error = $this->config->item($error_no, 'msg');
                } else {
                    $error_no = 714;
                    $error = $this->config->item($error_no, 'msg');
                }
            } else {
                //Template not found
                $error_no = 48;
                $error = $this->config->item($error_no, 'msg');
            }

            //Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'User', "ID: " . $this->db->insert_id());
        }

        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'success' => $success, 'error' => $error));
    }

}
