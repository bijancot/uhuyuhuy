<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Form extends CI_Model{
    function __construct(){
        parent::__construct();
    }

    public function getAll(){
        $res = $this->db->get('MAPPING')->result();
        return $res;
    }
    public function get($param){
        $filter = !empty($param['filter'])? $param['filter'] : '';
        $res    = $this->db->get_where('MAPPING', $filter)->result();
        return $res;
    }
    public function getTables(){
        $res = $this->db->query("SELECT table_name FROM information_schema.tables WHERE table_type = 'base table' AND table_schema='ut_app' AND table_name REGEXP 'form.*'")->result();
        return $res;
    }
    public function getDataTable($param){
        if($param['search'] != ""){
            $filter                 = array('NAMA_TABEL' => $param['search'], 'NO_DOC' => $param['search'], 'NAMA_FORM' => $param['search'], 'SECTION_FORM' => $param['search']);
            $records                = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->get('MAPPING')->result();
            $totalDisplayRecords    = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->from('MAPPING')->count_all_results();
        }else{
            $records                = $this->db->limit($param['limit'], $param['offset'])->get('MAPPING')->result();
            $totalDisplayRecords    = $this->db->limit($param['limit'], $param['offset'])->from('MAPPING')->count_all_results();
        }
        $totalRecords           = $this->db->count_all('MAPPING');

        return ['records' => $records, 'totalDisplayRecords' => $totalDisplayRecords, 'totalRecords' => $totalRecords];
    }
    public function insert($param){
        $this->db->insert('MAPPING', $param);
        $this->db->insert('FLOW', ['ID_MAPPING' => $param['ID_MAPPING']]);
        return $this->db->insert_id();
    }
    public function update($param){
        $this->db->where('ID_MAPPING', $param['ID_MAPPING'])->update('MAPPING', $param);
        return true;
    }
    public function delete($param){
        $this->db->where($param)->delete('FLOW');
        $this->db->where($param)->delete('MAPPING');
        return true;
    }
    public function getFlowAll(){
        $res = $this->db->get('V_FLOW')->result();
        return $res;
    }
    public function getFlow($param){
        $filter = !empty($param['filter'])? $param['filter'] : '';
        $res    = $this->db->get_where('V_FLOW', $filter)->result();
        return $res;
    }
    public function insertFlow($param){
        $this->db->insert('FLOW', $param);
        return $this->db->insert_id();
    }
    public function updateFlow($param){
        $this->db->where('ID_FLOW', $param['ID_FLOW'])->update('FLOW', $param);
        return true;
    }
    public function deleteFlow($param){        
        $this->db->set($param['APP'], null);
        $this->db->where('ID_FLOW', $param['ID_FLOW']);
        $this->db->update('FLOW');

        return true;
    }
    public function editFlow($param){        
        $this->db->set($param['APP'], $param['ROLE']);
        $this->db->where('ID_FLOW', $param['ID_FLOW']);
        $this->db->update('FLOW');

        return true;
    }
    public function getJmlForm(){
        $sql = "SELECT COUNT(ID_MAPPING) as JML_FORM FROM MAPPING";
        $result = $this->db->query($sql);
        return $result->row()->JML_FORM;
    }
    public function getJmlUser(){
        $sql = "SELECT COUNT(ID_USERS) as JML_PENGGUNA FROM USERS";
        $result = $this->db->query($sql);
        return $result->row()->JML_PENGGUNA;
    }
    public function getTransDone(){
        $sql = "SELECT COUNT(ID_TRANS) as TRANS_DONE FROM TRANSACTION WHERE STAT_TRANS = '2'";
        $result = $this->db->query($sql);
        return $result->row()->TRANS_DONE;
    }
    public function getTransNot(){
        $sql = "SELECT COUNT(ID_TRANS) as TRANS_NOT FROM TRANSACTION WHERE STAT_TRANS = '1'";
        $result = $this->db->query($sql);
        return $result->row()->TRANS_NOT;
    }
}
