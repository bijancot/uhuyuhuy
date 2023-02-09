<?php

class ProjectType extends CI_Model{
    public function getAll(){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get('master_project_type')->result();
    }
    public function getById($id){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->get_where('master_project_type', ['type_no' => $id])->row();
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

        return $DB2->get_where('master_project_type', $param)->result();
    }
    public function insert($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_project_type', $param);
    }
    public function insertTransKendaraan($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->insert('master_project_type', $param);
    }
    public function update($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->where('type_no', $param['type_no'])->update('master_project_type', $param);
    }
    public function delete($param){
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->delete('master_project_type', $param);
    }
}