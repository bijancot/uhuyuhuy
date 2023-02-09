<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TransactionApproval extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_v_trans($role, $limit)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("
                transaction_approval.approval_date as DATE_TRANS,
                transaction_approval.project_no as PROJECT_NO,
                transaction_approval_detail.approval_id as ID_TRANS, 
                master_mapping.mapping_name as NAMA_TRANSAKSI,
                transaction_approval_detail.approval_detail_stat_approve as ISAPPROVE_APP,
                transaction_approval.approval_status as STATUS_APPROVE,
                (case
                    when ((transaction_approval.approval_flag) = '1') then master_mapping.mapping_app_1
                    when ((transaction_approval.approval_flag) = '2') then master_mapping.mapping_app_2
                    when ((transaction_approval.approval_flag) = '3') then master_mapping.mapping_app_3
                    when ((transaction_approval.approval_flag) = '4') then master_mapping.mapping_app_4
                    when ((transaction_approval.approval_flag) = '5') then master_mapping.mapping_app_5
                    when ((transaction_approval.approval_flag) = '6') then master_mapping.mapping_app_6
                    when ((transaction_approval.approval_flag) = '7') then master_mapping.mapping_app_7
                end) AS CONFIRM_STATE_TRANS,
                transaction_approval_detail.approval_detail_role as ROLE
            ")
            ->join('transaction_approval_detail', 'transaction_approval_detail.approval_id = transaction_approval.approval_id', 'LEFT OUTER')
            ->join('master_mapping', 'master_mapping.mapping_id = transaction_approval.mapping_id', 'LEFT OUTER')
            ->where('transaction_approval.approval_stat_propose', 1)
            ->where('transaction_approval_detail.approval_detail_role', $role)
            // ->where('transaction_approval_detail.approval_detail_stat_approve <> '.NULL)
            ->having("CONFIRM_STATE_TRANS = '$role' OR transaction_approval_detail.approval_detail_stat_approve IS NOT NULL")
            ->order_by('transaction_approval.approval_date', 'DESC')
            ->limit(($limit > 0 ? $limit : NULL), 0)
            ->get('transaction_approval')->result();
            
        return $res;
    }
    public function get_detail_trans($id_trans)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("
                transaction_approval.approval_date as DATE_TRANS,
                transaction_approval.project_no as PROJECT_NO,
                transaction_approval.approval_id as ID_TRANS,
                transaction_approval.approval_path as PATH_FILE,
                master_mapping.mapping_name as KATEGORI_FILE
            ")
            ->join('master_mapping', 'master_mapping.mapping_id = transaction_approval.mapping_id', 'LEFT OUTER')
            ->where('transaction_approval.approval_id', $id_trans)
            ->get('transaction_approval')->row();
        return $res;
    }
    public function get_master_project($project_no)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("
                master_project.project_area as AREA,
                master_project.project_name as NAME_PROJ,
                master_project.project_status as STATUS_PROJ,
                master_project.project_type as TYPE_PROJ,
                master_project.project_category as CATEGORY_PROJ,
                master_project.project_totalvalue as PROJECT_VALUE,
                master_project.project_kontrak as SI_VALUE,

                master_vendor.vendor_name as VENDOR,

                master_project.project_stat,
                master_project.project_stat_app,
                master_project.project_stat_submit,
                master_project.project_stat_monitoring,
            ")
            ->join('master_vendor', 'master_vendor.vendor_no = master_project.vendor_no', 'LEFT OUTER')
            ->where('master_project.project_no', $project_no)
            ->get('master_project')->row();
        return $res;
    }
    public function get_status_approve($id_trans, $role, $userid)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("transaction_approval_detail.approval_detail_stat_approve as STATUS_TRANS")
            ->where('transaction_approval_detail.approval_id', $id_trans)
            ->where('transaction_approval_detail.approval_detail_role', $role)
            ->where('transaction_approval_detail.approval_detail_userid', $userid)
            ->get('transaction_approval_detail')->row();
        return $res;
    }
    public function get_tor_file($project_no)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("
                transaction_tor_document.document_name as NAMA_FILES,
                transaction_tor_document.document_link as URL_FILE
            ")
            ->where('transaction_tor_document.project_no', $project_no)
            ->get('transaction_tor_document')->result_array();
        return $res;
    }
    public function get_tender_file($project_no)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->select("
                transaction_tender_document.document_tender_name as NAMA_FILES,
                transaction_tender_document.document_tender_link as URL_FILE
            ")
            ->where('transaction_tender_document.project_no', $project_no)
            ->get('transaction_tender_document')->result_array();
        return $res;
    }
    public function getUser($username)
    {
        $res = $this->db->get_where('USERS', ['USER_USERS' => $username])->row();
        return $res;
    }
    public function getUserById($idUser)
    {
        $res = $this->db->get_where('USERS', ['ID_USERS' => $idUser])->row();
        return $res;
    }
    public function get_mapping($mapping_id)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->get_where('master_mapping', ['mapping_id' => $mapping_id])->row();
        return $res;
    }
    public function get_detail_approval($approval_id)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->order_by('approval_detail_id', 'DESC')->get_where('transaction_approval_detail', ['approval_id' => $approval_id])->result();
        return $res;
    }
    public function get_all_detail_approval($role)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->order_by('approval_detail_id', 'DESC')->get_where('transaction_approval_detail', ['approval_detail_role' => $role])->result();
        return $res;
    }
    public function get_trans_approval($approval_id)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $res = $DB2->get_where('transaction_approval', ['approval_id' => $approval_id])->row();
        return $res;
    }
    public function update_detail_approval($param, $role)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->where('approval_id', $param['approval_id'])
            ->where('approval_detail_role', $role)
            ->update('transaction_approval_detail', $param);
        return true;
    }
    public function update_trans_approval($param)
    {
        $DB2 = $this->load->database('gaSys2', true);
        $DB2->where('approval_id', $param['approval_id'])
            ->where('approval_stat_propose', 1)
            ->update('transaction_approval', $param);
        return true;
    }
}
