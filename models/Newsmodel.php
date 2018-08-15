<?php

class newsmodel extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add_news($news_title, $news_content, $news_expiry, $news_image, $created_by) {
        return $this->db->insert('news', array(
                    'title' => $news_title,
                    'content' => $news_content,
                    'expiry_date' => $news_expiry,
                    'image_name' => $news_image,
                    'enabled' => 1,
                    'created_by' => $created_by,
                    'created_on' => date('Y-m-d H:i:s'),
                    'lastupdated_by' => $created_by,
                    'lastupdated_on' => date('Y-m-d H:i:s'),
        ));
    }

    public function edit_news($news_id, $news_title, $news_content, $news_image, $news_expiry, $lastupdated_by) {
        $this->db->where('id', $news_id);
        $data = array(
            'title' => $news_title,
            'content' => $news_content,
            'expiry_date' => $news_expiry,
            'lastupdated_by' => $lastupdated_by,
            'lastupdated_on' => date('Y-m-d H:i:s'),
        );
        if ($news_image != NULL && strlen(trim($news_image)) > 0) {
            $data['image_name'] = trim($news_image);
        }
        return $this->db->update('news', $data);
    }

    public function get_all_newstag() {
        $this->db->select('id,name,description,color,enabled');
        $this->db->from('news_tag');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function add_newstag($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'name' => $info['name'],
            'color' => $info['color'],
            'description' => $info['description'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('news_tag', $data);
        return $res;
    }

    public function edit_newstag($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'name' => $info['name'],
            'color' => $info['color'],
            'description' => $info['description'],
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );
        $this->db->where('id', $info['id']);
        $res = $this->db->update('news_tag', $data);
        return $res;
    }

    public function get_all_news_slider() {
        $query = $this->db->query('SELECT 
    news.*, u1.name AS creator, u2.name AS updator
FROM
    news
        INNER JOIN
    sed_sys_user AS u1 ON u1.id = news.created_by
        INNER JOIN
    sed_sys_user AS u2 ON u2.id = news.lastupdated_by 
    WHERE news.section = ' . SECTION_NEWS_SLIDER);

        $result = $query->result_array();
        return $result;
    }

    public function get_four_news_slider_to_site() {
        $query = $this->db->query('SELECT 
    news.*,
    u1.name AS creator,
    u2.name AS updator,
    newsslider_leage.league_id,
    league.name AS lname,
    league.short_name as lshort_name,
    league.color as lcolor
FROM
    news
        INNER JOIN
    sed_sys_user AS u1 ON u1.id = news.created_by
        INNER JOIN
    sed_sys_user AS u2 ON u2.id = news.lastupdated_by
        INNER JOIN
    newsslider_leage ON newsslider_leage.news_slider_id = news.id
        INNER JOIN
    league ON league.id = newsslider_leage.league_id
    WHERE news.section = ' . SECTION_NEWS_SLIDER . '
GROUP BY news.id,newsslider_leage.league_id
ORDER BY news.created_on DESC LIMIT 6');

        $result = $query->result_array();
        return $result;
    }

    public function add_news_slider($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'image_name' => $info['image'],
            'section' => SECTION_NEWS_SLIDER,
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('news', $data);
        $last_id = $this->db->insert_id();
        $resp = $this->add_news_slider_league($last_id, $info);
        return $res;
    }

    public function add_news_slider_league($last_id, $info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        foreach ($info['league'] as $league) {
            $data = array(
                'news_slider_id' => $last_id,
                'league_id' => $league,
                'enabled' => 1,
                'created_by' => $user_id,
                'created_on' => $date,
                'lastupdated_by' => $user_id,
                'lastupdated_on' => $date,
            );
            $res1 = $this->db->insert('newsslider_leage', $data);
        }
    }

    public function get_all_school_news() {
        $query = $this->db->query('SELECT 
    news.*, u1.name AS creator, u2.name AS updator
FROM
    news
        INNER JOIN
    sed_sys_user AS u1 ON u1.id = news.created_by
        INNER JOIN
    sed_sys_user AS u2 ON u2.id = news.lastupdated_by 
    WHERE news.section = ' . SECTION_NEWS_SCHOOL);

        $result = $query->result_array();
        return $result;
    }

    public function get_six() {
        $result = array();
        $query = $this->db->query('SELECT 
    news.*, u1.name AS creator, u2.name AS updator
FROM
    news
        INNER JOIN
    sed_sys_user AS u1 ON u1.id = news.created_by
        INNER JOIN
    sed_sys_user AS u2 ON u2.id = news.lastupdated_by
WHERE
    news.section = 1
ORDER BY news.id DESC
LIMIT 6');

        $y = $this->db->last_query();
        while ($row = $query->result_array()) {
            $query2 = $this->db->query('SELECT 
    newsslider_leage.league_id,
    league.name AS lname,
    league.color AS lcolor
FROM
    newsslider_leage
        INNER JOIN
    league ON league.id = newsslider_leage.league_id
WHERE
    newsslider_leage.news_slider_id = ' . $row[0]['id']);
            $row['league'] = $query2->result_array();
        }
        $result = $row;
        return $result;
    }

    public function add_school_news($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'image_name' => $info['image'],
            'section' => SECTION_NEWS_SCHOOL,
            'description' => $info['description'],
            'link' => $info['link'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('news', $data);
        $last_id = $this->db->insert_id();
        $resp = $this->add_news_slider_league($last_id, $info);
        return $res;
    }

    public function get_school_news_to_site() {
        $query = $this->db->query('SELECT 
    news.*,
    u1.name AS creator,
    u2.name AS updator,
    newsslider_leage.league_id,
    league.name AS lname,
    league.short_name as lshort_name,
    league.color as lcolor
FROM
    news
        INNER JOIN
    sed_sys_user AS u1 ON u1.id = news.created_by
        INNER JOIN
    sed_sys_user AS u2 ON u2.id = news.lastupdated_by
        INNER JOIN
    newsslider_leage ON newsslider_leage.news_slider_id = news.id
        INNER JOIN
    league ON league.id = newsslider_leage.league_id
    WHERE news.section = ' . SECTION_NEWS_SCHOOL . '
GROUP BY news.id,newsslider_leage.league_id
ORDER BY news.created_on DESC');

        $result = $query->result_array();
        return $result;
    }

    public function add_news_ticker($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'content' => $info['content'],
            'expiry_date' => $info['expire_date'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('news_ticker', $data);
        return $res;
    }

    public function get_all_news_tickers() {
        $query = $this->db->query('SELECT 
    nt.*, user.name AS creator
FROM
    news_ticker nt
        INNER JOIN
    sed_sys_user user ON user.id = nt.created_by
ORDER BY created_on DESC');
        $result = $query->result_array();
        return $result;
    }

    public function edit_news_ticker($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'content' => $info['content'],
            'expiry_date' => $info['expire_date'],
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );
        $this->db->where('id', $info['id']);
        $res = $this->db->update('news_ticker', $data);
        return $res;
    }

    public function get_news_ticker_for_site() {
        $date = date('Y-m-d', strtotime("-1 days"));
        $this->db->select('*');
        $this->db->from('news_ticker');
        $this->db->where('expiry_date >=', $date);
        $this->db->order_by('expiry_date');

        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_all_announcement() {
        $this->db->select('announcement.id,announcement.title,announcement.description');
        $this->db->from('announcement');
        $this->db->limit(5);
        $this->db->where(array('announcement.enabled' => 1));
        $this->db->order_by('created_on', 'desc');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_announce_details_by_id($aid) {
        $this->db->select('*');
        $this->db->from('announcement');
        $this->db->where(array('id' => $aid));
        $query = $this->db->get();
        $result = $query->result_array();
        return $result[0];
    }

    public function add_announcement($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'description' => $info['content'],
            'enabled' => 1,
            'created_by' => $user_id,
            'created_on' => $date,
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );

        $res = $this->db->insert('announcement', $data);
        return $res;
    }

    public function get_all_announcements() {
        $this->db->select('a.*,user.name as creator');
        $this->db->from('announcement a');
        $this->db->join('sed_sys_user user', 'user.id = a.created_by', 'inner');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function edit_announcement($info) {
        $date = date('Y-m-d H:s:i');
        $user_id = $_SESSION['admin']['id'];
        $data = array(
            'title' => $info['title'],
            'description' => $info['content'],
            'lastupdated_by' => $user_id,
            'lastupdated_on' => $date,
        );
        $this->db->where('id', $info['id']);
        $res = $this->db->update('announcement', $data);
        return $res;
    }

}
