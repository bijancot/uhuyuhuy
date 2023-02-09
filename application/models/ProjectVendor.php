<?php

class ProjectVendor extends CI_Model{
    public function getAll(){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get('master_vendor')->result();
    }
    public function getById($id){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get_where('master_vendor', ['vendor_no' => $id])->row();
    }
    public function get($param){
        $DB2 = $this->load->database('gaSys2', true);
        if(!empty($param['orderBy'])){ // order by
            $DB2->order_by($param['orderBy']);
            unset($param['orderBy']);
        }
        if(!empty($param['limit'])){ // limit
            $DB2->limit($param['limit']);
            unset($param['limit']);
        }

        return $DB2->get_where('master_vendor', $param)->result();
    }
    public function insert($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_vendor', $param);
    }
    public function insertTransKendaraan($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_vendor', $param);
    }
    public function update($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->where('vendor_no', $param['vendor_no'])->update('master_vendor', $param);
    }
    public function delete($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->delete('master_vendor', $param);
    }
}