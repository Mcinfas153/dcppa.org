<?php

/**
 * User: Hashan Alwis
 * Date: 11/5/15    Time: 3:00 PM
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Common extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getMsg($id, $p1 = '', $p2 = '', $p3 = '') {
        $text = $this->config->item($id, 'msg');
        $text = str_replace('%1', $p1, $text);
        $text = str_replace('%2', $p2, $text);
        $text = str_replace('%3', $p3, $text);
        return $text;
    }

    public function getNotifyMsg($id, $p1 = '', $p2 = '', $p3 = '') {

        $this->db->select('message');
        $this->db->from('message');
        $this->db->where('msg_id', $id);
        $user = $this->db->get()->row();
        $text = '';
        if ($user) {
            $text = $user->message;
            $text = str_replace('%1', $p1, $text);
            $text = str_replace('%2', $p2, $text);
            $text = str_replace('%3', $p3, $text);
        }
        return $text;
    }

//    function randomString() {
//        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
//        $pass = array(); //remember to declare $pass as an array
//        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
//        for ($i = 0; $i < 8; $i++) {
//            $n = rand(0, $alphaLength);
//            $pass[] = $alphabet[$n];
//        }
//        return implode($pass); //turn the array into a string
//    }

    function randomString($length = 6, $alpha_only = FALSE, $numeric_only = FALSE, $avoid_ambiguous = FALSE) {
        $str = "";
        $avoid_characters = array('0', '1', 'i', 'I', 'o', 'O', 'l', 'L');
        if ($alpha_only) {
            $characters = array_merge(range('A', 'Z'), range('a', 'z'));
        } else if ($numeric_only) {
            $characters = array_merge(range('0', '9'));
        } else {
            $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        }
        if ($avoid_ambiguous) {
            $characters = array_diff($characters, $avoid_characters);
        }
        $max = count($characters) - 1;
        $i = 0;
        while ($i < $length) {
            $rand = mt_rand(0, $max);
            if (isset($characters[$rand])) {
                $str .= $characters[$rand];
                $i++;
            }
        }
        return $str;
    }

    /**
     * Returns unique string code that does not exist in the given table colunm
     * @param type $length - length required
     * @param type $table - table name to check
     * @param type $column - column name which contails code
     */
    public function get_unique_code($length, $table, $column) {
        $code = NULL;
        do {
            $temp_code = $this->randomString($length, FALSE, FALSE, TRUE);
            $res = $this->db->get_where($table, array($column => $temp_code))->result_array();
            if (count($res) == 0) {
                $code = $temp_code;
            }
        } while ($code == NULL);
        return $code;
    }

    /**
     * Get the tempate row by id
     * @param type $id
     */
    public function get_template_by_id($id) {
        $res = $this->db->get_where('template', array('id' => $id))->result_array();
        if (count($res) > 0) {
            return $res[0];
        }
        return FALSE;
    }

    /**
     * 
     * @param type $expected_user_type
     * @param type $expected_department
     * @return booleanReturns true if either or both user_type (weight) and user_department conditions are met.
     * Should provide at-lest one parameter. Both cannot be null at the same time.
     * 
     */
    public function has_permission($expected_user_type = NULL, $expected_department = NULL) {
        //get user profile
        $profile = $this->session->all_userdata();
        if ($profile != null && count($profile) > 0 && isset($profile['user_type']) && isset($profile['departments'])) {
            if ($expected_user_type != NULL) {
                if ($profile['user_type'] >= $expected_user_type) {
                    if ($expected_department != NULL) {
                        if (count($profile['departments']) > 0) {
                            foreach ($profile['departments'] as $dp) {
                                if ($dp['department_id'] == $expected_department) {
                                    return TRUE;
                                }
                            }
                        } else {
                            return FALSE;
                        }
                    } else {
                        return TRUE;
                    }
                } else {
                    return FALSE;
                }
            } else {
                if ($expected_department != NULL) {
                    if (count($profile['departments']) > 0) {
                        foreach ($profile['departments'] as $dp) {
                            if ($dp['department_id'] == $expected_department) {
                                return TRUE;
                            }
                        }
                    } else {
                        return FALSE;
                    }
                } else {
                    //both arguments null; raise error
                    trigger_error('Both arguments cannot be null');
                }
            }
        } else {
            trigger_error('User data not found');
        }
    }

    /**
     * Created By: Sampath K Abeysinghe
     * @param type $upload_name
     * @param type $actual_width
     * @param type $actual_height
     * @param type $max_filesize
     * @param type $allowed_types
     * @param type $upload_path
     * @param type $random_filename
     * @return type
     */
    public function upload_image(
    $upload_name, $actual_width, $actual_height, $max_width, $max_height, $max_filesize = 1024, $allowed_types = 'gif|jpg|png', $upload_path = PROPERTY_HOTEL_IMAGE_PATH_UPLOAD, $random_filename = TRUE) {
        $error_no = 0;
        $error_data = NULL;
        $upload_data = NULL;
        $this->load->helper('string');
        $upload_id = random_string('alnum', 5);
//        if ($this->session->userdata('user_id') != null) {
        if (isset($upload_name) && isset($actual_width) && isset($actual_height) && isset($allowed_types) && isset($max_filesize) && isset($upload_path)) {
            $upload_name = trim($upload_name);
            if (strlen($upload_name) > 0 && strlen($upload_name) < 255) {
                if (count($_FILES) > 0 && isset($_FILES[$upload_name]["name"])) {
                    $image_name = $_FILES[$upload_name]["name"];
                    list($width, $height) = getimagesize($_FILES[$upload_name]['tmp_name']);
                    if ($width >= floor($actual_width * IMAGE_TOLERANCE) && $height >= floor($actual_height * IMAGE_TOLERANCE)) {
                        $explodedArray = explode(".", $image_name);
                        $extension = end($explodedArray);
                        if (!file_exists($upload_path)) {
                            mkdir($upload_path, 0775);
                        }
                        if ($random_filename) {
                            $image_name = $this->get_random_file_name($upload_path, $extension);
                        } else if (file_exists($upload_path . $image_name)) {
                            $image_name = $this->get_random_file_name($upload_path, $extension);
                        }
                        $config = array();
                        $config['file_name'] = $image_name;
                        $config['allowed_types'] = $allowed_types;
                        $config['min_width'] = floor($actual_width * IMAGE_TOLERANCE);
                        $config['min_height'] = floor($actual_height * IMAGE_TOLERANCE);
                        $config['max_size'] = $max_filesize;
//                            $config['max_width'] = ceil($actual_width * (IMAGE_TOLERANCE + 1));
                        $config['max_width'] = $max_width;
//                            $config['max_height'] = ceil($actual_height * (IMAGE_TOLERANCE + 1));
                        $config['max_height'] = $max_height;
                        $config['encrypt_name'] = TRUE;
                        $config['upload_path'] = $upload_path;
                        $this->load->library('upload', $config);
                        $u = $this->upload;
                        if (!$this->upload->do_upload($upload_name)) {
                            $error_no = 1100; //Error while uploading the image.
                            $error_data = strip_tags($this->upload->display_errors());
                        } else {
                            $upload_data = $this->upload->data();
                            if ($upload_data['image_width'] != $actual_width || $upload_data ['image_height'] != $actual_height) { //image should be resized / cropped or both
                                //resize image
                                $resize_data = $this->resize_image($upload_path, $upload_data['file_name'], $extension, $upload_data['image_width'], $upload_data ['image_height'], $actual_width, $actual_height);
                                if ($resize_data) {
                                    $upload_data['file_name'] = $resize_data['resize_image_name'];
                                    if ($resize_data['ratio'] != 1) {
                                        //crop image
                                        $crop_data = $this->crop_image($upload_path, $resize_data['resize_image_name'], $extension, $resize_data['ratio'], $upload_data['image_width'], $upload_data ['image_height'], $actual_width, $actual_height);
                                        if ($crop_data) {
                                            $upload_data['file_name'] = $crop_data['crop_image_name'];
                                        } else {
                                            $error_no = 1106; //error while cropping image
                                            $error_data = strip_tags($crop_data['error_data']);
                                        }
                                    }
                                } else {
                                    $error_no = 1105; //error while resizing image  
                                    $error_data = strip_tags($resize_data['error_data']);
                                }
                            }
                            $upload_data['image_width'] = $actual_width;
                            $upload_data['image_height'] = $actual_height;
                        }
                    } else {
                        $error_no = 1104; //small image
                    }
                } else {
                    $error_no = 1101; //file not found
                }
            } else {
                $error_no = 1102; //invalid upload name
            }
        } else {
            $error_no = 1103; //required parameters not set
        }
//        } else {
//            $error_no = 119; //not logged in
//        }

        if ($error_no != 0 && strlen(trim($error_data)) == 0) {
            $error_data = $this->config->item($error_no, 'msg');
        }
        return array('error_no' => $error_no, 'upload_data' => $upload_data, 'error_data' => $error_data);
    }

    private function resize_image($upload_path, $image_name, $extension, $image_width, $image_height, $actual_width, $actual_height) {
        $resize_config = array();
        $resize_image_name = $this->get_random_file_name($upload_path, $extension);
        $resize_config['image_library'] = 'gd2';
        $resize_config['source_image'] = $upload_path . '/' . $image_name;
        $resize_config['new_image'] = $upload_path . '/' . $resize_image_name;
        $resize_config['create_thumb'] = FALSE;
        $resize_config['quality'] = '100';
        $resize_config['maintain_ratio'] = TRUE;
        $resize_config['width'] = $actual_width;
        $resize_config['height'] = $actual_height;
        $dim = (intval($image_width) / intval($image_height)) - ($actual_width / $actual_height);
        $resize_config['master_dim'] = ($dim > 0) ? "height" : "width";
        $this->load->library('image_lib', $resize_config);
        if ($this->image_lib->resize()) {
            unlink($upload_path . '/' . $image_name); //delete souce image
            return array('success' => FALSE, 'resize_image_name' => $resize_image_name, 'ratio' => $dim);
        }
        return array('success' => FALSE, 'error_data' => strip_tags($this->image_lib->display_errors()));
    }

    private function crop_image($upload_path, $image_name, $extension, $ratio, $image_width, $image_height, $actual_width, $actual_height) {
        $x_axis = $y_axis = 0;
        if ($ratio > 0) {
            $resize_width = round(($image_width / $image_height), 1, PHP_ROUND_HALF_ODD) * $actual_height;
            $x_axis = ($resize_width - $actual_width) / 2;
            $y_axis = 0;
        } else {
            $resize_height = round(($image_height / $image_width), 1, PHP_ROUND_HALF_ODD) * $actual_width;
            $x_axis = 0;
            $y_axis = ($resize_height - $actual_height) / 2;
        }
        $crop_image_name = $this->get_random_file_name($upload_path, $extension);
        $crop_config = array();
        $crop_config['image_library'] = 'gd2';
        $crop_config['source_image'] = $upload_path . '/' . $image_name;
        $crop_config['new_image'] = $upload_path . '/' . $crop_image_name;
        $crop_config['quality'] = '100';
        $crop_config['overwrite'] = TRUE;
        $crop_config['maintain_ratio'] = FALSE;
        $crop_config['width'] = $actual_width;
        $crop_config['height'] = $actual_height;
        $crop_config['x_axis'] = $x_axis;
        $crop_config['y_axis'] = $y_axis;

        $this->image_lib->initialize($crop_config);
        if ($this->image_lib->crop()) {
            unlink($upload_path . '/' . $image_name); //delete souce image
            return array('success' => TRUE, 'crop_image_name' => $crop_image_name);
        }
        return array('success' => FALSE, 'error_data' => strip_tags($this->image_lib->display_errors()));
    }

    /**
     * 
     * @param type $upload_path
     * @param type $extension
     * @return 15 digit file name with extension 
     */
    private function get_random_file_name($upload_path, $extension) {
        $file_name = '';
        do {
            $file_name = substr(number_format(time() * rand(), 0, '', ''), 0, 15) . '.' . $extension;
        } while (file_exists($upload_path . $file_name));
        return $file_name;
    }

    public function update_progress($total_items, $completed_items, $upload_id, $desc) {
        file_put_contents(
                SERVER_ROOT_DIRECTORY . 'upload_' . $upload_id . '.json', json_encode(array('items_completed' => $completed_items, 'total_items' => $total_items, 'desc' => $desc))
        );
        usleep(1000 * 1000); // 10 seconds
    }

    function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", "'", "{", "}", "$", "@", "#");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "", "", "", "", "", "");
        $result = str_replace($escapers, $replacements, $value);
//        return $result;
        $result = trim(htmlspecialchars($result));
        return $result;
    }

    function reverseEscapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
        $replacements = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $escapers = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    function get_enum_values($table, $field) {
        $type = $this->db->query("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'")->row(0)->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $enum = explode("','", $matches[1]);
        return $enum;
    }

    public function get_encrypted_text($plain_text) {
        $this->load->library('encryption');
        $this->encryption->initialize(
                array(
                    'cipher' => 'aes-128',
                    'mode' => 'cbc',
                    'key' => ENC_SEND_KEY
                )
        );
        return $this->encryption->encrypt($plain_text);
    }

    public function get_decrypted_text($cipher_text) {
        $this->load->library('encryption');
        $this->encryption->initialize(
                array(
                    'cipher' => 'aes-128',
                    'mode' => 'cbc',
                    'key' => ENC_RECEIVE_KEY
                )
        );
        return $this->encryption->decrypt($cipher_text);
    }

    public function easy_mail($to_addr, $subject, $email_info, $email_content) {
        $this->load->library('parser');
        $message = $this->parser->parse_string($email_content, $email_info, TRUE);
//        $email_template = $this->load->view('email_template', $data, TRUE);
        if ($this->send_mail($to_addr, CONTACT_EMAIL, APP_NAME, $subject, $message)) {
            $this->AddAuditTrailEntry('Mail', '', $subject);
            return TRUE;
        }
        return FALSE;
    }

    public function send_mail($to_addr, $from_addr, $from_name, $subject, $message, $cc_addr = NULL, $bcc_addr = NULL) {
        $this->load->library('email');
        $config = array();
        $config['protocol'] = 'sendmail';
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'html';
        $config['wrapchars'] = 150;
        $this->email->initialize($config);
        $this->email->clear();
        $this->email->from($from_addr, $from_name);
        $this->email->to($to_addr);
        if (isset($cc_addr)) {
            $this->email->cc($cc_addr);
        }
        if (isset($bcc_addr)) {
            $this->email->bcc($bcc_addr);
        }
        $this->email->subject($subject);
        $this->email->message($message);
        return $this->email->send();
    }

    public function get_all_rows($table_name, $enabled = NULL) {
        $res = array();
        if ($enabled != NULL && ($enabled == 0 || $enabled == 1)) {
            $this->db->where('enabled', $enabled);
        }
        $res = $this->db->get($table_name)->result_array();
        return $res;
    }

    public function toggle_enable($table_name, $id, $updated_by) {
        if ($table_name != NULL && $id != NULL) {
            //get current status
            $this->db->trans_start();
            $res = $this->db->get_where($table_name, array('id' => $id))->result_array();
            if (count($res) > 0) {
                $res = $res[0];
                $new_status = 1;
                $enable = 'Enable';
                if ($res['enabled'] == "1") {
                    $new_status = 0;
                    $enable = 'Disable';
                }
                $this->db->where('id', $id);
                $status = $this->db->update($table_name, array('enabled' => $new_status, 'lastupdated_on' => date('Y-m-d H:i:s'), 'lastupdated_by' => $updated_by));
                if ($status) {
                    if ($new_status == 0) {
                        Common::AddAuditTrailEntry(AUDITTRAIL_DELETE, $table_name, "ID: " . $id);
                    } else {
                        Common::AddAuditTrailEntry(AUDITTRAIL_UPDATE, $table_name, "ID: " . $id);
                    }
                    $this->db->trans_complete();
                }
                return $status;
            }
            $this->db->trans_rollback();
        }
        return FALSE;
    }

    public function get_messages_script($messages) {
        $script = '' . PHP_EOL;
        $config = $this->config;
        $iter = new RecursiveArrayIterator($messages);
        foreach (new RecursiveIteratorIterator($iter) as $key => $value) {
            $msg = $config->itme($value);
            if ($msg != NULL)
                $script .= "messages[$value] = '" . $msg . "'" . PHP_EOL;
        }
        return $script;
    }

    public function file_upload($file_name, $upload_path, $allowed_types, $max_size) {
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = $max_size;
        $config['max_width'] = 1024;
        $config['max_height'] = 768;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload($file_name)) {
            $result = array('error_data' => $this->upload->display_errors(), 'error_no' => 180);
        } else {
            $result = array('upload_data' => $this->upload->data());
            $result['error_no'] = 0;
        }

        return $result;
    }

    public function get_creation_data() {
        $created_by = $_SESSION['admin']['id'];
        if ($created_by == FALSE) {
            $created_by = 0;
        }
        $created_on = date("Y-m-d H:i:s");
        return array(
            'enabled' => 1,
            'created_by' => $created_by,
            'created_on' => $created_on,
            'lastupdated_by' => $created_by,
            'lastupdated_on' => $created_on
        );
    }
    
    public function get_creation_member_data() {
        $created_by = $_SESSION['member']['id'];
        if ($created_by == FALSE) {
            $created_by = 0;
        }
        $created_on = date("Y-m-d H:i:s");
        return array(
            'enabled' => 1,
            'created_by' => $created_by,
            'created_on' => $created_on,
            'lastupdated_by' => $created_by,
            'lastupdated_on' => $created_on
        );
    }

    public function convert_to_business_zone_time($pass_datetime, $pass_business_time_zone) {
        $datetime = new DateTime($pass_datetime);
        $datetime->format('Y-m-d H:i:s');
        $business_time_zone = new DateTimeZone($pass_business_time_zone);
        $datetime->setTimezone($business_time_zone);
        return $datetime->format('Y-m-d H:i:s');
    }

}

?>
