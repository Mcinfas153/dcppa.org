<?php

/*
 * Author : Mcinfas
 * Date : 2018/01/16
 */

class Category_model extends MY_Model {

    public function get_all_categories() {
        $query = $this->db->get_where('category', array('enabled', 1));
        $result = $query->result_array();
        return $result;
    }

    public function get_parent_categories() {
        $query = $this->db->get_where('category', array('enabled' => 1, 'parent_category_id' => 0));
        $result = $query->result_array();
        return $result;
    }

    public function add_category($info) {
        $date = date('Y-m-d H:i:s');
        $data = array(
            'name' => $info['category_name'],
            'discription' => $info['category_description'],
            'parent_category_id' => $info['main_category_id'],
            'enabled' => 1,
            'created_by' => $_SESSION['admin']['id'],
            'created_on' => $date,
            'lastupdated_by' => $_SESSION['admin']['id'],
            'lastupdated_on' => $date,
        );
        $res = $this->db->insert('category', $data);
        $y = $this->db->last_query();
        return $res;
    }

    public function get_nestable_view() {
        $query = $this->db->get_where('category', array('parent_category_id' => 0));
        $return = array();
        foreach ($query->result() as $category) {
            $return[$category->id] = $category;
            $return[$category->id]->subs = $this->get_sub_categories($category->id); // Get the categories sub categories
        }
        return $return;
    }

    public function get_sub_categories($category_id) {
        $this->db->where('parent_category_id', $category_id);
        $query = $this->db->get('category');
        return $query->result();
    }

    public function edit_category($info) {
        $date = date('Y-m-d H:i:s');
        $data = array(
            'name' => $info['category_name'],
            'discription' => $info['category_description'],
            'lastupdated_on' => $date,
            'lastupdated_by' => $_SESSION['admin']['id'],
        );
        $this->db->where('id', $info['category_id']);
        $res = $this->db->update('category', $data);
        return $res;
    }

}
