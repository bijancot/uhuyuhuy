<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Model{
    function __construct(){
        parent::__construct();
    }

    public function getAll(){
        $res = $this->db->query("
            SELECT * FROM USERS ORDER BY ISACTIVE_USERS DESC, TS_USERS  DESC
        ")->result();
        return $res;
    }
    public function get($param){
        $filter = !empty($param['filter'])? $param['filter'] : '';
        $res    = $this->db->get_where('USERS', $filter)->result();
        return $res;
    }
    public function getDataTable($param){
        if($param['search'] != ""){
            $filter                 = array('NAMA_USERS' => $param['search'], 'USER_USERS' => $param['search'], 'ROLE_USERS' => $param['search'], 'DEPT_USERS' => $param['search']);
            $records                = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->order_by("ISACTIVE_USERS DESC, TS_USERS  DESC")->get('USERS')->result();
            $totalDisplayRecords    = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->from('USERS')->count_all_results();
        }else{
            $records                = $this->db->limit($param['limit'], $param['offset'])->order_by("ISACTIVE_USERS DESC, TS_USERS  DESC")->get('USERS')->result();
            $totalDisplayRecords    = $this->db->limit($param['limit'], $param['offset'])->from('USERS')->count_all_results();
        }
        $totalRecords           = $this->db->count_all('USERS');

        return ['records' => $records, 'totalDisplayRecords' => $totalDisplayRecords, 'totalRecords' => $totalRecords];
    }
    public function insert($param){
        $this->db->insert('USERS', $param);
        return $this->db->insert_id();
    }
    public function update($param){
        $this->db->where('ID_USERS', $param['ID_USERS'])->update('USERS', $param);
        return true;
    }
    public function delete($param){
        $this->db->where($param)->delete('USERS');
        return true;
    }
}