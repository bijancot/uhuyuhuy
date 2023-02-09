<?php

class Area extends CI_Model{
    public function getAll(){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get('master_area')->result();
    }
    public function getById($id){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get_where('master_area', ['area_code' => $id])->row();
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

        return $DB2->get_where('master_area', $param)->result();
    }
    public function insert($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_area', $param);
    }
    public function insertTransKendaraan($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_area', $param);
    }
    public function update($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->where('area_code', $param['area_code'])->update('master_area', $param);
    }
    public function delete($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->delete('master_area', $param);
    }
}