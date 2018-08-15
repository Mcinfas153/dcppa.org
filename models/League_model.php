<?php

class League_model extends CI_Model {

    public function get_all_leagues() {
        $query = $this->db->get_where('league');
        $result = $query->result_array();
        return $result;
    }

    public function add_league($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'name' => $info['name'],
            'short_name' => $info['short_name'],
            'description' => $info['description'],
            'color' => $info['color'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('league', $data);
        return $res;
    }

    public function edit_league($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'name' => $info['name'],
            'short_name' => $info['short_name'],
            'color' => $info['color'],
            'description' => $info['description'],
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );
        $this->db->where('id', $info['id']);
        $res = $this->db->update('league', $data);
        return $res;
    }

}
