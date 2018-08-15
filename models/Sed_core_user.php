<?php

class Sed_core_user extends MY_Model {

    private $id;
    private $name;
    private $user_name;
    private $password;
    private $salt;
    private $type;
    private $enabled;
    private $created_on;
    private $created_by;
    private $lastupdated_on;
    private $lastupdated_by;

    public function __construct() {
        parent::__construct();
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getUser_name() {
        return $this->user_name;
    }

    function getPassword() {
        return $this->password;
    }

    function getSalt() {
        return $this->salt;
    }

    function getType() {
        return $this->type;
    }

    function getEnabled() {
        return $this->enabled;
    }

    function getCreated_on() {
        return $this->created_on;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    function getLastupdated_on() {
        return $this->lastupdated_on;
    }

    function getLastupdated_by() {
        return $this->lastupdated_by;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setUser_name($user_name) {
        $this->user_name = $user_name;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setSalt($salt) {
        $this->salt = $salt;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    function setCreated_on($created_on) {
        $this->created_on = $created_on;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function setLastupdated_on($lastupdated_on) {
        $this->lastupdated_on = $lastupdated_on;
    }

    function setLastupdated_by($lastupdated_by) {
        $this->lastupdated_by = $lastupdated_by;
    }

    public static function make_user_with_data($id, $name, $user_name, $password, $type, $enabled, $created_by, $created_on, $lastupdated_by, $lastupdated_on) {
        $user = new Sed_user();
        $user->setId($id);
        $user->setName($name);
        $user->setPassword($password);
        $user->setType($type);
        $user->setEnabled($enabled);
        $user->setCreated_by($created_by);
        $user->setCreated_on($created_on);
        $user->setLastupdated_by($lastupdated_by);
        $user->setLastupdated_on($lastupdated_on);
        return $user;
    }

    public static function add_user($name, $user_name, $password, $type, $recovery_email, $recovery_phone, $created_by, $league) {
        $CI = & get_instance();
        $password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 13));
        $created_on = date('Y-m-d H:i:s');
        return $CI->db->insert('sed_sys_user', array(
                    'name' => $name,
                    'username' => $user_name,
                    'password' => $password,
                    'sed_sys_user_type' => $type,
                    'league_id' => $league,
                    'recovery_email' => $recovery_email,
                    'recovery_phone' => $recovery_phone,
                    'enabled' => 1,
                    'created_by' => $created_by,
                    'created_on' => $created_on,
                    'lastupdated_by' => $created_by,
                    'lastupdated_on' => $created_on,
        ));
    }

    public static function get_user_by_id($user_id) {
        $CI = & get_instance();
        $obj = NULL;
        if ($user_id != NULL) {
            $user_id = trim($user_id);
            if (strlen($user_id) > 0 && strlen($user_id) < 5) {
                $id = intval($user_id);
                $res = $CI->db->get_where('sed_user', array('id' => $id));
                if (count($res) == 1) {
                    $res = $res[0];
                    $user = new Sed_user();
                    $user->setId($res['id']);
                    $user->setName($res['name']);
                    $user->setType($res['type']);
                    $user->setEnabled($res['enabled']);
                    $user->setCreated_by($res['created_by']);
                    $user->setCreated_on($res['created_on']);
                    $user->setLastupdated_by($res['lastupdated_by']);
                    $user->setLastupdated_on($res['lastupdated_on']);
                    $obj = $user;
                }
            }
        }
        return $obj;
    }

    public static function login_sed_user($user_name, $password) {
        if ($user_name != NULL && $password != NULL) {
            $user_name = trim($user_name);
            $password = trim($password);
            if (strlen($user_name) > 0 && strlen($user_name) < 50 && strlen($password) > 0 && strlen($password) < 50) {
                $user_obj = Sed_core_user::get_user_by_username_recovery_mail($user_name);
                if ($user_obj != FALSE && count($user_obj) > 0) {
                    if (password_verify($password, $user_obj['password'])) {
                        return array(
                            'id' => $user_obj['id'],
                            'name' => $user_obj['name'],
                            'username' => $user_obj['username'],
                            'enabled' => $user_obj['enabled'],
                            'type' => $user_obj['type_name']
                        );
                    }
                }
            }
        }
        return FALSE;
    }

    public static function get_user_by_username_recovery_mail($input) {
        $CI = & get_instance();
        $sql = "SELECT 
                    us.*, ut.type_name
                FROM
                    sed_sys_user us
                        INNER JOIN
                    sed_sys_user_type ut ON (us.sed_sys_user_type = ut.id)
                WHERE
                    username LIKE ?
                    or recovery_email LIKE ?";
        $res = $CI->db->query($sql, array($input, $input))->result_array();
        if (count($res) > 0) {
            return $res[0];
        }
        return FALSE;
    }

    public static function get_all_users() {
        $CI = & get_instance();
        $query = "  SELECT 
                        us.id,
                        us.name,
                        us.username,
                        us.sed_sys_user_type,
                        us.recovery_phone,
                        us.recovery_email,
                        us.league_id,
                        t.type_name,
                        us.enabled,
                        us2.name AS created_user,
                        us.created_on,
                        us3.name AS updated_user,
                        us.lastupdated_on
                    FROM
                        sed_sys_user us
                            INNER JOIN
                        sed_sys_user_type t ON (us.sed_sys_user_type = t.id)
                            INNER JOIN
                        sed_sys_user us2 ON (us.created_by = us2.id)
                            INNER JOIN
                        sed_sys_user us3 ON (us.created_by = us3.id)";
        return $CI->db->query($query)->result_array();
    }

    public static function get_all_user_types() {
        $CI = & get_instance();
        return $CI->db->get_where('sed_sys_user_type')->result_array();
    }

    public function edit_user($info) {
        $date = date('Y-m-d H:i:s');
        $id = $info['id'];
        $data = array(
            'name' => $info['name'],
            'username' => $info['username'],
            'sed_sys_user_type' => $info['user_type'],
            'recovery_email' => $info['recovery_mail'],
            'league_id' => $info['league'],
            'recovery_phone' => $info['recovery_phone'],
            'lastupdated_by' => $_SESSION['admin']['id'],
            'lastupdated_on' => $date,
        );

        $this->db->where('id', $id);
        $res = $this->db->update('sed_sys_user', $data);
        return $res;
    }

    public function create_password_reset_code($user_id) {
        $date = date('Y-m-d H:i:s');
        $data = array(
            'user_id' => $user_id,
            'code_type' => 'PASSWORD_RESET',
            'sed_sys_user_type' => $info['user_type'],
            'recovery_email' => $info['recovery_mail'],
            'recovery_phone' => $info['recovery_phone'],
            'lastupdated_by' => $_SESSION['admin']['id'],
            'lastupdated_on' => $date,
        );

        $this->db->where('id', $id);
        $res = $this->db->update('sed_sys_user', $data);
        return $res;
    }

    public function reset_password_by_email($user_id, $recovery_mail, $code) {
        //add reset password record
        $res = $this->common->get_template_by_id(1);
        if ($res != FALSE) {
            $email_content = $res['content'];
            $link = base_url('admin/reset_password/' . $code);
            $email_info = array(
                'RESET_LINK' => $link,
            );
            if ($this->common->easy_mail($recovery_mail, 'DCPPA Web Portal - Administrator Password Reset', $email_info, $email_content)) {
                $time = date('Y-m-d H:i:s');
                if ($this->db->insert('sed_user_code', array(
                            'user_id' => $user_id,
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

    public function is_valid_code($code_str) {
        $status = 0;
        $sql = "SELECT *
                FROM
                    sed_user_code uc
                WHERE
                    uc.user_id IN (SELECT 
                            uc2.user_id
                        FROM
                            sed_user_code uc2
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

    public function get_user_by_password_reset_code($code) {
        $sql = "SELECT * FROM sed_sys_user us WHERE id = (SELECT user_id FROM sed_user_code WHERE code LIKE ?)";
        $res = $this->db->query($sql, array($code))->result_array();
        if (count($res) > 0) {
            return $res[0];
        }
        return FALSE;
    }

    public function update_password($user_id, $password) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 13));
        $this->db->where('id', $user_id);
        $res = $this->db->update('sed_sys_user', array('password' => $password_hash));
        return $res;
    }

    public static function invalidate_code($code) {
        $CI = & get_instance();
        $CI->db->like('code', $code);
        $res = $CI->db->update('sed_user_code', array('is_open' => 0));
        return $res;
    }

}
