<?php

class Member_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('Common');
    }

    public function login_member($user_name, $password) {
        if ($user_name != NULL && $password != NULL) {
            $user_name = trim($user_name);
            $password = trim($password);
            if (strlen($user_name) > 0 && strlen($user_name) < 50 && strlen($password) > 0 && strlen($password) < 50) {
                $user_obj = $this->get_member_by_mail($user_name);
                if ($user_obj != FALSE && count($user_obj) > 0) {
                    if (password_verify($password, $user_obj['password'])) {
                        $unread_messages = $this->get_unread_messages_by_member($user_obj['id']);
                        $msg_count = count($unread_messages);
                        return array(
                            'id' => $user_obj['id'],
                            'first_name' => $user_obj['first_name'],
                            'email' => $user_obj['email'],
                            'enabled' => $user_obj['enabled'],
                            'member_type' => $user_obj['member_type'],
                            'member_league' => $user_obj['member_league'],
                            'is_exco_member' => $user_obj['is_exco_member'],
                            'unread_messages' => $msg_count,
                        );
                    }
                }
            }
        }
        return FALSE;
    }

    public function reset_password_by_email($user_id, $recovery_mail, $code) {
        //add reset password record
        $res = $this->common->get_template_by_id(1);
        if ($res != FALSE) {
            $email_content = $res['content'];
            $link = base_url('member/reset_password/' . $code);
            $email_info = array(
                'RESET_LINK' => $link,
            );
            if ($this->common->easy_mail($recovery_mail, 'DCPPA Web Portal - Password Reset', $email_info, $email_content)) {
                $time = date('Y-m-d H:i:s');
                if ($this->db->insert('member_code', array(
                            'member_id' => $user_id,
                            'code_type' => 'PASSWORD_RESET',
                            'code' => $code,
                            'ip_address' => $_SERVER['REMOTE_ADDR'],
                            'created_by' => $user_id,
                            'created_on' => $time,
                            'lastupdated_by' => $user_id,
                            'lastupdated_on' => $time,
                        ))) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function get_member_by_mail($input) {
        $sql = "SELECT 
                    us.*
                FROM
                    member us
                WHERE
                    email LIKE ?";
        $res = $this->db->query($sql, array($input))->result_array();
        if (count($res) > 0) {
            return $res[0];
        }
        return FALSE;
    }

    public function get_all_members() {
        $query = $this->db->get_where('member', array('enabled' => 1));
        $result = $query->result_array();
        return $result;
    }

    public static function get_all_member_for_admin() {
        $CI = & get_instance();
        $query = "SELECT 
    m.*, l.name AS league_name
FROM
    member m
        LEFT JOIN
    league l ON l.id = m.member_league";
        return $CI->db->query($query)->result_array();
    }

    public function get_all_members_for_report($member_type = NULL, $member_league = NULL, $is_exco = NULL) {
        $CI = & get_instance();
        $query = "SELECT 
    m.*,
    l.id AS l_id,
    l.name AS league_name,
    mt.id AS mt_id,
    mt.name AS member_type
FROM
    member m
        LEFT JOIN
    league l ON l.id = m.member_league
        INNER JOIN
    member_type mt ON mt.id = m.member_type
WHERE
    m.enabled = 1";

        if ($member_type != NULL && $member_type != 'NULL') {
            $query .= " AND m.member_type = $member_type";
        }
        if ($member_league != NULL && $member_league != 'NULL') {
            $query .= " AND m.member_league = $member_league";
        }
        if ($is_exco != NULL) {
            if ($is_exco != 0) {
                $query .= " AND m.is_exco_member = $is_exco";
            }
        }
        return $CI->db->query($query)->result_array();
    }

    public function get_user_by_password_reset_code($code) {
        $sql = "SELECT * FROM member us WHERE id = (SELECT member_id FROM member_code WHERE code LIKE ?)";
        $res = $this->db->query($sql, array($code))->result_array();
        if (count($res) > 0) {
            return $res[0];
        }
        return FALSE;
    }

    public function update_password($user_id, $password) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 13));
        $this->db->where('id', $user_id);
        $res = $this->db->update('member', array('password' => $password_hash));
        return $res;
    }

    public function invalidate_code($code) {
        $this->db->like('code', $code);
        $res = $this->db->update('member_code', array('is_open' => 0));
        return $res;
    }

    public function is_valid_code($code_str) {
        $status = 0;
        $sql = "SELECT *
                FROM
                    member_code uc
                WHERE
                    uc.member_id IN (SELECT 
                            uc2.member_id
                        FROM
                            member_code uc2
                        WHERE
                            uc2.code_type = 'PASSWORD_RESET'
                                AND uc2.code LIKE ?
                                AND uc2.is_open = 1)
                ORDER BY uc.id DESC LIMIT 1";
        $res = $this->db->query($sql, array($code_str))->result_array();
        if (count($res) > 0) {
            $code = $res[0];
            if ($code['code'] == $code_str) {
                if ($code['is_open'] == '1') {
                    $expiry_time = strtotime($code['created_on'] . ' + 24 hours');
                    if (strtotime("now") > $expiry_time) {
                        $status = 3; // code expired
                    }
                } else {
                    $status = 2; //code used
                }
            } else {
                $status = 4; //There is a newer code
            }
        } else {
            $status = 1; //invalid code
        }
        return $status;
    }

    public function edit_member($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['member']['id'];
        $data = array(
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'designation' => $info['designation'],
            'email' => $info['member_email'],
            'mobile_phone' => $info['mobile'],
            'home_phone' => $info['telephone'],
            'image_file' => $info['image'],
            'office_phone' => $info['office_phone'],
            'password' => password_hash($info['password'], PASSWORD_BCRYPT, array('cost' => 13)),
            'home_address_no' => $info['home_address_no'],
            'home_address_street' => $info['home_address_street'],
            'home_address_city' => $info['home_address_city'],
            'office_address_no' => $info['office_address_no'],
            'office_address_street' => $info['office_address_street'],
            'office_address_city' => $info['office_address_city'],
            'office_name' => $info['office_name'],
            'office_email' => $info['office_email'],
            'office_website' => $info['office_website'],
            'my_self' => $info['my_self'],
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );
        if ($info['image'] == '') {
            unset($data['image_file']);
        }
        if ($info['password'] == '') {
            unset($data['password']);
        }
        $this->db->where('id', $info['id']);
        $res = $this->db->update('member', $data);
        return $res;
    }

    public function get_all_member_type() {
        $result = $this->db->get_where('member_type', array('enabled' => 1))->result_array();
        return $result;
    }

    public function add_new_member($info) {
        $creation_data = $this->Common->get_creation_data();
        $data = array(
            'title' => $info['title'],
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'member_code' => $info['member_code'],
            'email' => $info['email'],
            'designation' => $info['designation'],
            'mobile_phone' => $info['mobile_phone'],
            'home_phone' => $info['home_phone'],
            'office_phone' => $info['office_phone'],
            'home_address_no' => $info['home_address_no'],
            'home_address_street' => $info['home_address_street'],
            'home_address_city' => $info['home_address_city'],
            'office_address_no' => $info['office_address_no'],
            'office_address_street' => $info['office_address_street'],
            'office_address_city' => $info['office_address_city'],
            'member_league' => $info['member_league'],
            'member_type' => $info['member_type'],
            'join_date' => $info['join_date'],
            'office_website' => $info['office_website'],
            'office_email' => $info['office_email'],
            'my_self' => $info['my_self'],
            'office_name' => $info['office_name'],
            'is_exco_member' => $info['is_exco_member'],
        );

        $res = $this->db->insert('member', array_merge($data, $creation_data));
        //$this->send_email_to_member($info);

        return $res;
    }

    public function add_new_pending_member($info) {
        //$creation_data = $this->Common->get_creation_data();
        $data = array(
            'title' => $info['title'],
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'member_code' => $info['member_code'],
            'email' => $info['email'],
            'designation' => $info['designation'],
            'mobile_phone' => $info['mobile_phone'],
            'home_phone' => $info['home_phone'],
            'office_phone' => $info['office_phone'],
            'home_address_no' => $info['home_address_no'],
            'home_address_street' => $info['home_address_street'],
            'home_address_city' => $info['home_address_city'],
            'office_address_no' => $info['office_address_no'],
            'office_address_street' => $info['office_address_street'],
            'office_address_city' => $info['office_address_city'],
            'member_league' => $info['member_league'],
            'member_type' => $info['member_type'],
            'join_date' => $info['join_date'],
            'office_website' => $info['office_website'],
            'office_email' => $info['office_email'],
            'my_self' => $info['my_self'],
            'office_name' => $info['office_name'],
            'is_exco_member' => $info['is_exco_member'],
        );

        $res = $this->db->insert('member', $data);
        //$this->send_email_to_member($info);

        return $res;
    }

    public function edit_member_admin($info) {

        $data = array(
            'title' => $info['title'],
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'member_code' => $info['member_code'],
            'email' => $info['email'],
            'designation' => $info['designation'],
            'mobile_phone' => $info['mobile_phone'],
            'home_phone' => $info['home_phone'],
            'office_phone' => $info['office_phone'],
            'home_address_no' => $info['home_address_no'],
            'home_address_street' => $info['home_address_street'],
            'home_address_city' => $info['home_address_city'],
            'office_address_no' => $info['office_address_no'],
            'office_address_street' => $info['office_address_street'],
            'office_address_city' => $info['office_address_city'],
            'member_league' => $info['member_league'],
            'member_type' => $info['member_type'],
            'office_website' => $info['office_website'],
            'office_email' => $info['office_email'],
            'my_self' => $info['my_self'],
            'is_exco_member' => $info['is_exco_member'],
            'office_name' => $info['office_name'],
            'lastupdated_on' => date('Y-m-d H:i:a'),
            'lastupdated_by' => $_SESSION['admin']['id'],
        );
        $this->db->where('id', $info['id']);
        $res = $this->db->update('member', $data);
        //$this->send_email_to_member($info);

        return $res;
    }

    public function send_email_to_member($info) {
        $this->load->model('common');
        $message = 'You are now member in DCPPA.<br>'
                . 'Your user email :' . $info['email'] . '<br>'
                . 'Your new password : ' . $info['password'];
        $member_password_notify_mail = $this->common->send_mail($info['email'], 'Smart eDesigners', 'Smart eDesigners', 'Password Notify', $message, $cc_addr = NULL, $bcc_addr = NULL);
        return true;
    }

    public function send_mail_to_member($info) {
        $creation_data = $this->Common->get_creation_member_data();
        $data = array(
            'receiver_id' => $info['receiver_id'],
            'sender_id' => $info['sender_id'],
            'color' => $info['color'],
            'subject' => $info['subject'],
            'message_body' => htmlentities($info['message_body']),
        );
        $res = $this->db->insert('messages', array_merge($data, $creation_data));
        return $res;
    }

    public function get_member_details_by_id($id) {
        $this->db->select(' m.*, mt.name AS member_type, l.name AS league_name');
        $this->db->from(' member m');
        $this->db->join('member_type mt', 'mt.id = m.member_type', 'inner');
        $this->db->join('league l', 'l.id = m.member_league', 'inner');
        $this->db->where('m.id', $id);
        $q = $this->db->get();
        $res = $q->result_array();
        if (!empty($res)) {
            return $res[0];
        } else {
            return FALSE;
        }
    }

    function get_unread_messages_by_member($member_id) {
        $q = $this->db->get_where('messages m', array('m.receiver_id' => $member_id, 'm.is_read' => 0));
        $res = $q->result_array();
        return $res;
    }

}
