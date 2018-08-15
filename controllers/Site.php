<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Newsmodel');
        $this->load->model('Event_model');
        $this->load->model('Member_model');
        $this->load->model('League_model');
        $this->load->model('Meeting_model');
    }

    public function index() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['events_photos'] = $this->Event_model->get_photos_by_event();
        $data['news_sliders'] = $this->Newsmodel->get_four_news_slider_to_site();
        $data['school_news'] = $this->Newsmodel->get_school_news_to_site();
        $data['leagues'] = $this->League_model->get_all_leagues();
        $data['main_content'] = 'site/main_page';
        $this->load->view('templates/site_template', $data);
    }

    public function product_item() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['main_content'] = 'site/product_item';
        $this->load->view('templates/site_template', $data);
    }

    public function product_list() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['main_content'] = 'site/product_list';
        $this->load->view('templates/site_template', $data);
    }

    public function history() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/history';
        $this->load->view('templates/site_template', $data);
    }

    public function cadets() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['members'] = $this->Member_model->get_all_members();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/cadets';
        $this->load->view('templates/site_template', $data);
    }

    public function about_dcppa_cb() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/about_dcppa_cb';
        $this->load->view('templates/site_template', $data);
    }

    public function member_list() {
        if (isset($_SESSION['member'])) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['member_type'] = $this->Member_model->get_all_member_type();
            $data['announcements'] = $this->Newsmodel->get_all_announcement();
            $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
            $data['members'] = $this->Member_model->get_all_members();
            $data['main_content'] = 'site/member_list';
            $this->load->view('templates/site_template', $data);
        } else {
            redirect(base_url('site'));
        }
    }

    public function trust() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/trust';
        $this->load->view('templates/site_template', $data);
    }
    
    public function awards() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/awards';
        $this->load->view('templates/site_template', $data);
    }
    
    public function contact() {
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['main_content'] = 'site/contact';
        $this->load->view('templates/site_template', $data);
    }

    public function get_announce_details_by_id() {
        $aid = $this->input->post('aid');
        $res = $this->Newsmodel->get_announce_details_by_id($aid);
        header('Content-Type: application/json');
        echo json_encode(array('result' => $res));
    }

    public function events_gallery() {
        $eid = $this->input->get('event_id');
        $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
        $data['announcements'] = $this->Newsmodel->get_all_announcement();
        $data['school_news'] = $this->Newsmodel->get_school_news_to_site();
        $data['events'] = $this->Event_model->get_events_details_by_id($eid);
        $data['main_content'] = 'site/event_gallery';
        $this->load->view('templates/site_template', $data);
    }

    public function edit_member() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['member'])) {
            $info['id'] = $this->input->post('id');
            $info['first_name'] = $this->input->post('first_name');
            $info['last_name'] = $this->input->post('last_name');
            $info['designation'] = $this->input->post('designation');
            $info['member_email'] = $this->input->post('member_email');
            $info['mobile'] = $this->input->post('mobile');
            $info['telephone'] = $this->input->post('telephone');
            $info['home_address_no'] = $this->input->post('home_address_no');
            $info['home_address_street'] = $this->input->post('home_address_street');
            $info['home_address_city'] = $this->input->post('home_address_city');
            $info['office_address_no'] = $this->input->post('office_address_no');
            $info['office_address_street'] = $this->input->post('office_address_street');
            $info['office_address_city'] = $this->input->post('office_address_city');
            $info['image'] = $this->input->post('image');
            $info['office_phone'] = $this->input->post('office_phone');
            $info['password'] = $this->input->post('password');
            $info['office_name'] = $this->input->post('office_name');
            $info['office_email'] = $this->input->post('office_email');
            $info['office_website'] = $this->input->post('office_website');
            $info['my_self'] = $this->input->post('my_self');

            $res = $this->Member_model->edit_member($info);
            if ($res) {
                $error_no = 0;
            } else {
                $error_no = 704;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function add_new_member() {
        $error_no = 0;
        $error = '';
        if (isset($_SESSION['member'])) {
            if ($_SESSION['member']['member_type'] == MODULE_ADMIN) {
                $info['title'] = $this->input->post('title');
                $info['name'] = $this->input->post('name');
                $info['email'] = $this->input->post('email');
                $info['designation'] = $this->input->post('designation');
                $info['member_code'] = $this->input->post('member_code');
                $info['member_type'] = $this->input->post('member_type');
                $info['mobile'] = $this->input->post('mobile');
                $info['home_phone'] = $this->input->post('home_phone');
                $info['home_address'] = $this->input->post('home_address');
                $info['home_address_city'] = $this->input->post('home_address_city');
                $info['office_adress'] = $this->input->post('office_adress');
                $info['office_address_city'] = $this->input->post('office_address_city');
                $info['password'] = $this->input->post('password');
                $info['office_phone'] = $this->input->post('office_phone');
                $info['league'] = $this->input->post('league');

                $res = $this->Member_model->add_new_member($info);
                if ($res) {
                    $error_no = 0;
                } else {
                    $error_no = 706;
                }
            } else {
                //dont have permission to create new member
                $error_no = 707;
            }
        } else {
            //Not login
            $error_no = 2;
        }
        header('Content-Type: application/json');
        echo json_encode(array('error_no' => $error_no, 'error' => $error));
    }

    public function exco_meetings() {
        if (isset($_SESSION['member']) && $_SESSION['member']['is_exco_member'] == 1) {
            $data['leagues'] = $this->League_model->get_all_leagues();
            $data['member_type'] = $this->Member_model->get_all_member_type();
            $data['announcements'] = $this->Newsmodel->get_all_announcement();
            $data['latest_news'] = $this->Newsmodel->get_news_ticker_for_site();
            $data['meetings'] = $this->Meeting_model->get_all_meeting_for_site();
            $data['main_content'] = 'site/exco_meetings';
            $this->load->view('templates/site_template', $data);
        } else {
            redirect(base_url('site'));
        }
    }

}
