<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_action extends MY_Controller {

    public function __construct() {
        parent::__construct();
//        $this->load->model('itemmodel');
        $this->load->model('Sed_core_user');
        $this->load->model('League_model');
        $this->load->model('Newsmodel');
        $this->load->model('Event_model');
        $this->load->model('Meeting_model');
        $this->load->model('Common');
        $this->load->model('Member_model');
    }

    public function add_news() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $user_id = $_SESSION['admin']['id'];
            $news_title = $this->input->post('news-title');
            $news_content = $this->input->post('news-content');
            $news_expiry = date('Y-m-d', strtotime($this->input->post('news-expiry')));
            $news_image = $this->input->post('news-image');
            if ($news_title != null && strlen(trim($news_title)) > 0) {
                $this->load->model('newsmodel');
                if (!$this->newsmodel->add_news($news_title, $news_content, $news_expiry, $news_image, $user_id)) {
                    $error_no = 201;
                } else {
                    Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'News Item', "ID: " . $this->db->insert_id());
                }
            }
        } else {
            $error_no = 1;
            $error = $this->config->item(1, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_user() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $user_id = $_SESSION['admin']['id'];
            $name = $this->input->post('name');
            $username = $this->input->post('user-name');
            $password = $this->input->post('password');
            $type = $this->input->post('type');
            $recovery_email = $this->input->post('recovery-email');
            $recovery_phone = $this->input->post('recovery-phone');
            $league = $this->input->post('league');
            if (!Sed_core_user::add_user($name, $username, $password, $type, $recovery_email, $recovery_phone, $user_id, $league)) {
                $error_no = 151;
            } else {
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'User', "ID: " . $this->db->insert_id());
            }
        } else {
            $error_no = 1;
            $error = $this->config->item(1, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_member() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            
            $data['member_code'] = $this->input->post('member_code');
            $data['title'] = $this->input->post('title');
            $data['first_name'] = $this->input->post('first_name');
            $data['last_name'] = $this->input->post('last_name');
            $data['my_self'] = $this->input->post('my_self');
            $data['email'] = $this->input->post('email');
            $data['mobile_phone'] = $this->input->post('mobile');
            $data['home_address_no'] = $this->input->post('home_address_no');
            $data['home_address_street'] = $this->input->post('home_address_street');
            $data['home_address_city'] = $this->input->post('home_address_city');
            $data['home_phone'] = $this->input->post('home_telephone');
            $data['office_name'] = $this->input->post('office_name');
            $data['designation'] = $this->input->post('designation');
            $data['office_address_no'] = $this->input->post('office_address_no');
            $data['office_address_street'] = $this->input->post('office_address_street');
            $data['office_address_city'] = $this->input->post('office_address_city');
            $data['office_website'] = $this->input->post('office_website');
            $data['office_email'] = $this->input->post('office_email');
            $data['office_phone'] = $this->input->post('office_phone_edit');
            $data['member_type'] = $this->input->post('member_type');
            $data['member_league'] = $this->input->post('member_league');
            $data['join_date'] = $this->input->post('join_date');
            $data['is_exco_member'] = $this->input->post('exco_member');

            if (!$this->Member_model->add_new_member($data)) {
                $error_no = 706;
            } else {
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'User', "ID: " . $this->db->insert_id());
            }
        } else {
            $error_no = 1;
            $error = $this->config->item(1, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_news() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $user_id = $_SESSION['admin']['id'];
            $news_id = $this->input->post('news-id');
            $news_title = $this->input->post('news-title');
            $news_content = $this->input->post('news-content');
            $news_expiry = $this->input->post('news-expiry');
            $news_image = $this->input->post('news-image');
            $this->load->model('newsmodel');
            $res = $this->newsmodel->edit_news($news_id, $news_title, $news_content, $news_image, $news_expiry, $user_id);
            if (!$res) {
                $error_no = 203;
            } else {
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'News', "ID: " . $news_id);
            }
        } else {
            $error_no = 1;
        }
        if ($error_no > 0) {
            $error = $this->config->item(1, 'msg');
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_user() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $user_id = $this->input->post('hidden_id');
            $user_name = $this->input->post('name');
            $user_type = $this->input->post('user_type');
            $user_username = $this->input->post('user_name');
            $recovery_email = $this->input->post('recovery_email');
            $recovery_phone = $this->input->post('recovery_phone');
            $league = $this->input->post('league');
            $info = array(
                'id' => $user_id,
                'name' => $user_name,
                'user_type' => $user_type,
                'username' => $user_username,
                'recovery_mail' => $recovery_email,
                'recovery_phone' => $recovery_phone,
                'league' => $league,
            );

            $res = $this->Sed_core_user->edit_user($info);
            if ($res) {
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'User', $user_name);
            } else {
                $error_no = 156;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_WARNING, 'Upadate User', $user_name);
            }
        } else {
            redirect(base_url('admin/login_view'));
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }
    
    public function edit_member_admin() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $data['id'] = $this->input->post('hidden_id');
            $data['member_code'] = $this->input->post('member_code');
            $data['title'] = $this->input->post('title');
            $data['first_name'] = $this->input->post('first_name');
            $data['last_name'] = $this->input->post('last_name');
            $data['my_self'] = $this->input->post('my_self');
            $data['email'] = $this->input->post('email');
            $data['mobile_phone'] = $this->input->post('mobile');
            $data['home_address_no'] = $this->input->post('home_address_no');
            $data['home_address_street'] = $this->input->post('home_address_street');
            $data['home_address_city'] = $this->input->post('home_address_city');
            $data['home_phone'] = $this->input->post('home_telephone');
            $data['office_name'] = $this->input->post('office_name');
            $data['designation'] = $this->input->post('designation');
            $data['office_address_no'] = $this->input->post('office_address_no');
            $data['office_address_street'] = $this->input->post('office_address_street');
            $data['office_address_city'] = $this->input->post('office_address_city');
            $data['office_website'] = $this->input->post('office_website');
            $data['office_email'] = $this->input->post('office_email');
            $data['office_phone'] = $this->input->post('office_phone_edit');
            $data['member_type'] = $this->input->post('member_type');
            $data['member_league'] = $this->input->post('member_league');
            $data['is_exco_member'] = $this->input->post('exco_member');

            $res = $this->Member_model->edit_member_admin($data);
            if ($res) {
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'Member ID', $data['id']);
            } else {
                $error_no = 156;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_WARNING, 'Upadate Member', $data['id']);
            }
        } else {
            redirect(base_url('admin/login_view'));
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
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
                    if (!$this->Sed_core_user->update_password($user_id, $password)) {
                        $error_no = 167;
                    } else {
                        if (Sed_core_user::invalidate_code($code)) {
                            $this->db->trans_complete();
                            unset($_SESSION['reset_code']);
                            unset($_SESSION['user_id']);
                            Admin_action::AddAuditTrailEntry(AUDITTRAIL_INFORMATION, 'System User Password Update', 'User ID:' . $user_id);
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

    public function add_league() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['name'] = $this->input->post('name');
            $info['short_name'] = $this->input->post('short_name');
            $info['color'] = $this->input->post('color');
            $info['description'] = $this->input->post('description');
            $res = $this->League_model->add_league($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'League', $info['name']);
            } else {
                $error_no = 451;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_league() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['id'] = $this->input->post('id');
            $info['name'] = $this->input->post('name');
            $info['short_name'] = $this->input->post('short_name');
            $info['color'] = $this->input->post('color');
            $info['description'] = $this->input->post('description');
            $res = $this->League_model->edit_league($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'League', $info['name']);
            } else {
                $error_no = 453;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_newstag() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['name'] = $this->input->post('name');
            $info['color'] = $this->input->post('color');
            $info['description'] = $this->input->post('description');
            $res = $this->Newsmodel->add_newstag($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'News Tag', $info['name']);
            } else {
                $error_no = 501;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_newstag() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['id'] = $this->input->post('id');
            $info['name'] = $this->input->post('name');
            $info['color'] = $this->input->post('color');
            $info['description'] = $this->input->post('description');
            $res = $this->Newsmodel->edit_newstag($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'League', $info['name']);
            } else {
                $error_no = 503;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_news_slider() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['image'] = $this->input->post('image');
            $info['league'] = $this->input->post('league');
            $res = $this->Newsmodel->add_news_slider($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'News Slider', $info['title']);
            } else {
                $error_no = 551;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_school_news() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['image'] = $this->input->post('image');
            $info['description'] = $this->input->post('description');
            $info['link'] = $this->input->post('link');
            $info['league'] = $this->input->post('league');
            $res = $this->Newsmodel->add_school_news($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'School News', $info['title']);
            } else {
                $error_no = 601;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_event() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['league'] = $this->input->post('league');
            $info['description'] = $this->input->post('description');
            $res = $this->Event_model->add_event($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'Events', $info['title']);
            } else {
                $error_no = 620;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_event_file() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['description'] = $this->input->post('description');
            $info['image'] = $this->input->post('image');
            $info['event'] = $this->input->post('event');
            $info['feature'] = $this->input->post('feature');
            $res = $this->Event_model->add_event_file($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'Event File', $info['event']);
            } else {
                $error_no = 551;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_news_ticker() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['content'] = $this->input->post('content');
            $info['expire_date'] = $this->input->post('expire_date');
            $res = $this->Newsmodel->add_news_ticker($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'News Ticker', $info['title']);
            } else {
                $error_no = 661;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_news_ticker() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['id'] = $this->input->post('id');
            $info['title'] = $this->input->post('title');
            $info['content'] = $this->input->post('content');
            $info['expire_date'] = $this->input->post('expire_date');
            $res = $this->Newsmodel->edit_news_ticker($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'News Ticker', $info['title']);
            } else {
                $error_no = 663;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_announcement() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['content'] = $this->input->post('content');
            $res = $this->Newsmodel->add_announcement($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'Announcement', $info['title']);
            } else {
                $error_no = 681;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_announcement() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['id'] = $this->input->post('id');
            $info['title'] = $this->input->post('title');
            $info['content'] = $this->input->post('content');
            $res = $this->Newsmodel->edit_announcement($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'Announcement', $info['title']);
            } else {
                $error_no = 683;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_meeting() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['title'] = $this->input->post('title');
            $info['date'] = $this->input->post('date');
            $info['time'] = $this->input->post('time');
            $info['league'] = $this->input->post('league');
            $info['address'] = $this->input->post('address');
            $info['content'] = $this->input->post('content');
            $info['latitude'] = $this->input->post('latitude');
            $info['longitude'] = $this->input->post('longitude');
            $info['map_address'] = $this->input->post('map_address');
            $info['agenda_file_name'] = $this->input->post('agenda_file_name');
            $info['minute_file_name'] = $this->input->post('minute_file_name');
            $info['accounts_file_name'] = $this->input->post('accounts_file_name');

            $res = $this->Meeting_model->add_meeting($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_ADDNEW, 'Meeting', $info['title']);
            } else {
                $error_no = 721;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function edit_meeting() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['admin'])) {
            $info['id'] = $this->input->post('id');
            $info['title'] = $this->input->post('title');
            $info['date'] = $this->input->post('date');
            $info['time'] = $this->input->post('time');
            $info['league'] = $this->input->post('league');
            $info['address'] = $this->input->post('address');
            $info['content'] = $this->input->post('content');
            $info['latitude'] = $this->input->post('latitude');
            $info['longitude'] = $this->input->post('longitude');
            $info['map_address'] = $this->input->post('map_address');
            $info['agenda_file_name'] = $this->input->post('agenda_file_name');
            $info['minute_file_name'] = $this->input->post('minute_file_name');
            $info['accounts_file_name'] = $this->input->post('accounts_file_name');

            $res = $this->Meeting_model->edit_meeting($info);
            if ($res) {
                $error_no = 0;
                Admin_action::AddAuditTrailEntry(AUDITTRAIL_UPDATE, 'Meetings', $info['title']);
            } else {
                $error_no = 723;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function notify_meeting_info() {
        $error_no = 0;
        $error = '';
        $msg_no = null;
        if (isset($_SESSION['admin'])) {
            $meeting_id = $this->input->post('meeting_id');
            if (isset($meeting_id)) {
                $meeting_info = $this->Meeting_model->get_meeting_info_by_id($meeting_id);
                if (!empty($meeting_info)) {
                    $template = $this->Common->get_template_by_id(2);
                    $email_content = $template['content'];
                    if ($this->Common->easy_mail(EX_CO_MAILING_LIST_ADDRESS, 'DCPPA Web Portal - Ex-Co Meeting Alert', $meeting_info, $email_content)) {
                        $msg_no = 726;
                    } else {
                        //mail not send
                        $msg_no = 727;
                        $error_no = 727;
                    }
                } else {
                    //meeting info empty
                    $msg_no = 728;
                    $error_no = 728;
                }
            } else {
                //meeting id not found
                $msg_no = 729;
                $error_no = 729;
            }
        }
        header('Content-Type:application/json');
        echo json_encode(array('msg_no' => $msg_no, 'error_no' => $error_no));
    }

}
