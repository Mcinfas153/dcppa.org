<?php

class Event_model extends MY_Model {

    public function get_all_events() {
        $this->db->select('events.*,league.short_name as lshort_name,user.name as creator');
        $this->db->from('events');
        $this->db->join('league', 'league.id = events.league_id');
        $this->db->join('sed_sys_user user', 'user.id = events.created_by');
        $this->db->where('events.enabled', 1);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function add_event($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'description' => $info['description'],
            'league_id' => $info['league'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('events', $data);
        return $res;
    }

    public function add_event_file($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'name' => $info['image'],
            'description' => $info['description'],
            'feature' => $info['feature'],
            'entity' => ENTITY_EVENT,
            'row_id' => $info['event'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('files', $data);
        return $res;
    }

    public function get_all_files_by_event_id($id) {
        $this->db->select('files.*,events.title as event_name,user.name as creator');
        $this->db->from('files');
        $this->db->join('events', 'events.id = files.row_id');
        $this->db->join('sed_sys_user user', 'user.id = events.created_by');
        $this->db->where(array('files.row_id' => $id));
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_photos_by_event() {
        $x = 1;
        $q = $this->db->query('SELECT 
    events.*, league.name AS lname, sed_sys_user.name AS creator
FROM
    events
        INNER JOIN
    files ON files.row_id = events.id
        INNER JOIN
    league ON league.id = events.league_id
        INNER JOIN
    sed_sys_user ON sed_sys_user.id = events.created_by
WHERE
    events.enabled = 1
GROUP BY events.id ORDER BY events.created_on desc LIMIT 5');
        $y = $this->db->last_query();
        $images = array();
        $events = $q->result_array();
        for ($x = 0; $x < count($events); $x++) {
            $q2 = $this->db->query('SELECT 
    files.name as file_name,files.feature
FROM
    files
WHERE
    files.entity = 1 AND files.enabled = 1
        AND files.row_id = ' . $events[$x]['id'] . '
ORDER BY files.feature DESC');
            $y2 = $this->db->last_query();
            $images = $q2->result_array();
            $events[$x]['images'] = $images;
        }
        $results = $events;
        return $results;
    }

    public function get_events_details_by_id($eid) {
        $images = array();
        $q = $this->db->query('SELECT * FROM events where id = ' . $eid);
        $r = $q->result_array();
        $q2 = $this->db->query('SELECT name as img_name FROM files where row_id = ' . $eid);
        $images = $q2->result_array();
        if (isset($r[0])) {
            $r[0]['images'] = $images;
        }
        return $r[0];
    }

}
