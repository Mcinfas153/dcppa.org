<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Sed_core_user');
        $this->load->model('common');
        $this->load->model('League_model');
        $this->load->model('Newsmodel');
        $this->load->model('Event_model');
        $this->load->model('Meeting_model');
        $this->load->model('Member_model');
    }

    public function index() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['title'] = 'Admin | Dashboard';
            $data['main_content'] = 'admin/dashboard';
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function news() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $this->load->model('newsmodel');
            $data['news'] = $this->common->get_all_rows('news');
            $data['main_content'] = 'admin/news';
            $data['title'] = 'Admin | News';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'News', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function login_view() {
        $data['title'] = 'Admin | Login';
        $data['common_modals'] = $this->load->view('admin/includes/common_modals', '', TRUE);
        $this->load->view('admin/login', $data);
    }

    public function login() {
//        session_start();
        $user_name = $this->input->post('username');
        $password = $this->input->post('password');
        $login = FALSE;
        if ($user_name != NULL && $password != NULL) {
            $res = Sed_core_user::login_sed_user($user_name, $password);
            if ($res != FALSE && count($res) > 0) {
                if ($res['enabled'] == 1) {
                    $_SESSION['admin'] = $res;
                    $login = TRUE;
                    Admin::AddAuditTrailEntry(AUDITTRAIL_LOGIN, 'Login Success', "Username: " . $_SESSION['admin']['name'] . "|IP: " . $this->input->ip_address());
                } else {
                    $error_no = 47;
                }
            }
        }
        if (!$login) {
            $_SESSION['message'] = $this->config->item(2, 'msg');
            Admin::AddAuditTrailEntry(AUDITTRAIL_WARNING, 'Login Fail', "Username: " . $user_name . "|IP: " . $this->input->ip_address());
        }
        redirect('admin');
    }

    public function logout() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            Admin::AddAuditTrailEntry(AUDITTRAIL_LOGOUT, 'Logout', "Username: " . $_SESSION['admin']['name'] . "|IP: " . $this->input->ip_address());
            unset($_SESSION['admin']);
        }
        redirect('admin/login_view');
    }

    public function users() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['users'] = Sed_core_user::get_all_users();
            $data['user_types'] = Sed_core_user::get_all_user_types();
            $data['title'] = 'Admin | Users';
            $data['main_content'] = 'admin/users';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Users', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function members() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['members'] = Member_model::get_all_member_for_admin();
            $data['member_type'] = $this->Member_model->get_all_member_type();
            $data['title'] = 'Admin | Members';
            $data['main_content'] = 'admin/members';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Members', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function members_report() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            if (isset($_POST['member_type']) && isset($_POST['member_league']) && isset($_POST['is_exco'])) {
                $member_type = $this->input->post('member_type');
                $member_league = $this->input->post('member_league');
                $is_exco = $this->input->post('is_exco');
                $data['selected_member_type'] = $member_type;
                $data['selected_member_league'] = $member_league;
                $data['selected_exco'] = $is_exco;
                $data['members'] = $this->Member_model->get_all_members_for_report($member_type, $member_league, $is_exco);
            } else {
                $data['selected_member_type'] = 'NULL';
                $data['selected_member_league'] = 'NULL';
                $data['selected_exco'] = '0';
                $data['members'] = Member_model::get_all_member_for_admin();
            }
            $data['member_type'] = $this->Member_model->get_all_member_type();
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['title'] = 'Admin | Members Report';
            $data['main_content'] = 'admin/members_report';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Members', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function reset_password($code) {
        $error_id = 0;
        if ($code != NULL) {
            $code = trim($code);
            if (strlen($code) > 0 && strlen($code) < 100) {
                //check if code is valid
                $status = $this->Sed_core_user->is_valid_code($code);
                if ($status == '0') { //valid code
                    $user = $this->Sed_core_user->get_user_by_password_reset_code($code);
                    $data['name'] = $user['name'];
                    $data['code'] = $code;
                    $data['common_modals'] = $this->load->view('admin/includes/common_modals', '', TRUE);
                    $_SESSION['reset_code'] = $code;
                    $_SESSION['user_id'] = $user['id'];
                    $this->load->view('admin/reset_password', $data);
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

    public function league() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['title'] = 'Admin | League';
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['main_content'] = 'admin/league.php';
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function news_tag() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['title'] = 'Admin | News Tag';
            $data['newstags'] = $this->Newsmodel->get_all_newstag();
            $data['main_content'] = 'admin/newstag';
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function news_slider() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['slider_news'] = $this->Newsmodel->get_all_news_slider();
            $data['main_content'] = 'admin/news_slider';
            $data['title'] = 'Admin | News Slider';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'News', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function school_news() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['school_news'] = $this->Newsmodel->get_all_school_news();
            $data['main_content'] = 'admin/school_news';
            $data['title'] = 'Admin | School News';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'News', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function gallery() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['events'] = $this->Event_model->get_all_events();
            $data['main_content'] = 'admin/events';
            $data['title'] = 'Admin | Manage Events';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Events', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function manage_event_file() {
        if (isset($_SESSION['admin'])) {
            $data['event_id'] = $this->input->get('event');
            $data['event_files'] = $this->Event_model->get_all_files_by_event_id($data['event_id']);
            $data['slider_news'] = $this->Newsmodel->get_all_news_slider();
            $data['main_content'] = 'admin/manage_event_file';
            $data['title'] = 'Admin | Event Files';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Event File', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function news_ticker() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['news_tickers'] = $this->Newsmodel->get_all_news_tickers();
            $data['main_content'] = 'admin/news_ticker';
            $data['title'] = 'Admin | News Ticker';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'News Ticker', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function announcement() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['announcements'] = $this->Newsmodel->get_all_announcements();
            $data['main_content'] = 'admin/announcement';
            $data['title'] = 'Admin | Announcement';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'News Ticker', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function exco_meetings() {
//        session_start();
        if (isset($_SESSION['admin'])) {
            $data['meetings'] = $this->Meeting_model->get_all_meetings();
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['news_tickers'] = $this->Newsmodel->get_all_news_tickers();
            $data['main_content'] = 'admin/exco_meetings';
            $data['title'] = 'Admin | Ex-Co Meetings';
            Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Ex-Co Meetings', '');
            $this->load->view('templates/admin_template', $data);
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

    public function member_card() {
        $error_id = 0;
        if (isset($_SESSION['admin'])) {
            $member_id = $this->uri->segment(3);
            if (isset($member_id)) {
                $member_details = $this->Member_model->get_member_details_by_id($member_id);
                if (!empty($member_details)) {
                    $data['member_details'] = $member_details;
                    $data['main_content'] = 'admin/member_card';
                    $data['title'] = 'Admin | Member Card';
                    Admin::AddAuditTrailEntry(AUDITTRAIL_VIEW, 'Member Card', $member_id);
                    $this->load->view('templates/admin_template', $data);
                } else {
                    //member details not found
                    $error_id = 718;
                }
            } else {
                //Member id not valid
                $error_id = 702;
            }
            if ($error_id > 0) {
                redirect(base_url('error/page/' . $error_id));
            }
        } else {
            redirect(base_url('admin/login_view'));
        }
    }

}
