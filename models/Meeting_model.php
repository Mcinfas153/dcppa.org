<?php

class Meeting_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    public function add_meeting($data) {

        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $info['name'] = $data['title'];
        $info['date'] = $data['date'];
        $info['time'] = $data['time'];
        $info['league_id'] = $data['league'];
        $info['address'] = $data['address'];
        $info['description'] = $data['content'];
        $info['latitude'] = $data['latitude'];
        $info['longitude'] = $data['longitude'];
        $info['map_address'] = $data['map_address'];
        $info['agenda_filename'] = $data['agenda_file_name'];
        $info['minutes_filename'] = $data['minute_file_name'];
        $info['accounts_filename'] = $data['accounts_file_name'];
        $info['called_by'] = $user_id;
        $info['enabled'] = 1;
        $info['created_by'] = $user_id;
        $info['created_on'] = $date;
        $info['lastupdated_by'] = $user_id;
        $info['lastupdated_on'] = $date;

        $res = $this->db->insert('meetings', $info);
        $q = $this->db->last_query();
        return $res;
    }

    public function get_all_meetings() {
        $this->db->select(' m.*,l.name as league_name,l.id as league_id,user.name as called_by');
        $this->db->from('meetings m');
        $this->db->join('league l', 'l.id = m.league_id', 'inner');
        $this->db->join('sed_sys_user user', 'user.id = m.called_by', 'inner');
        $this->db->order_by('m.date', 'desc');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function edit_meeting($data) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $info['name'] = $data['title'];
        $info['date'] = $data['date'];
        $info['time'] = $data['time'];
        $info['league_id'] = $data['league'];
        $info['address'] = $data['address'];
        $info['description'] = $data['content'];
        $info['latitude'] = $data['latitude'];
        $info['longitude'] = $data['longitude'];
        $info['map_address'] = $data['map_address'];
        $info['agenda_filename'] = $data['agenda_file_name'];
        $info['minutes_filename'] = $data['minute_file_name'];
        $info['accounts_filename'] = $data['accounts_file_name'];
        $info['called_by'] = $user_id;
        $info['lastupdated_by'] = $user_id;
        $info['lastupdated_on'] = $date;
        $this->db->where('id', $data['id']);
        $res = $this->db->update('meetings', $info);
        return $res;
    }

    public function get_all_meeting_for_site() {
        $this->db->select(' m.*,l.name as league_name,l.id as league_id,user.name as called_by');
        $this->db->from('meetings m');
        $this->db->join('league l', 'l.id = m.league_id', 'inner');
        $this->db->join('sed_sys_user user', 'user.id = m.called_by', 'inner');
        $this->db->where('m.enabled', 1);
        $this->db->order_by('m.date', 'desc');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_meeting_info_by_id($meeting_id) {
        $this->db->select('*');
        $this->db->from('meetings');
        $this->db->where('id', $meeting_id);
        $q = $this->db->get();
        $res = $q->result_array();
        if (!empty($res)) {
            return $res[0];
        } else {
            return false;
        }
    }

}
