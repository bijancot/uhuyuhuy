<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Transaction extends CI_Model{
    function __construct(){
        parent::__construct();
    }

    public function getAll(){
        $res = $this->db->order_by('TS_TRANS', 'DESC')->get('V_TRANSACTION')->result();
        return $res;
    }
    public function getDataTable($param){
        $this->db->order_by('TS_TRANS', 'DESC');
        if($param['search'] != ""){
            $filter                 = array('NAMA_USERS' => $param['search'], 'NAMA_FORM' => $param['search'], 'KETERANGAN_TRANS' => $param['search']);
            $records                = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->get('V_TRANSACTION')->result();
            $totalDisplayRecords    = $this->db->or_like($filter)->limit($param['limit'], $param['offset'])->from('V_TRANSACTION')->count_all_results();
        }else{
            $records                = $this->db->limit($param['limit'], $param['offset'])->get('V_TRANSACTION')->result();
            $totalDisplayRecords    = $this->db->limit($param['limit'], $param['offset'])->from('V_TRANSACTION')->count_all_results();
        }
        $totalRecords           = $this->db->count_all('V_TRANSACTION');

        return ['records' => $records, 'totalDisplayRecords' => $totalDisplayRecords, 'totalRecords' => $totalRecords];
    }
    public function get($param){
        $filter = !empty($param['filter'])? $param['filter'] : '';
        $res    = $this->db->get_where('V_TRANSACTION', $filter)->result();
        return $res;
    }
    public function insert($param){
        $this->db->insert('TRANSACTION', $param);
        return $this->db->insert_id();
    }
    public function update($param){
        $this->db->where('ID_TRANS', $param['ID_TRANS'])->update('TRANSACTION', $param);
        return true;
    }
    public function delete($param){
        $this->db->where($param)->delete('TRANSACTION');
        return true;
    }
}