<?php
    class TenantDisplay extends CI_Model{
        public function getAll($param = null, $stat = ['4', '5']){
            $this->db->where_in('STAT_DEBITNOTE', $stat);
            $res = $this->db->order_by('TGLPESANAN_DEBITNOTE', 'DESC')->get_where('V_DEBITNOTE_TENANT', $param)->result();
            return $res;
        }
        public function get($param){
            $res    = $this->db->get_where('V_DEBITNOTE_TENANT', $param)->row();
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
?>