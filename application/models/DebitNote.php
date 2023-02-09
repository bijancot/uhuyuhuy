<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class DebitNote extends CI_Model{
    function __construct(){
        parent::__construct();
        $this->load->library('pdfgenerator');
        $this->load->library('datefunction');
    }

    public function getAll($param){
        $res = $this->db->order_by('TGLOUT_DEBITNOTE', 'DESC')->get_where('DEBITNOTE', $param)->result();
        return $res;
    }
    public function get($param){
        $res    = $this->db->get_where('DEBITNOTE', $param)->row();
        return $res;
    }
    public function getDataTable($param){
        $this->db->order_by('TGLOUT_DEBITNOTE', 'DESC');
        if($param['search'] != ""){
            $filter                 = array('NOFAKTUR_DEBITNOTE' => $param['search'], 'TGLFAKTUR_DEBITNOTE' => $param['search'], 'TGLJATUH_DEBITNOTE' => $param['search'], 'NOFAKTURPAJAK_DEBITNOTE' => $param['search'], 'NAMAPERUSAHAAN_DEBITNOTE' => $param['search'], 'BARANGJASA_DEBITNOTE' => $param['search']);
            $records                = $this->db->query('SELECT * FROM DEBITNOTE WHERE STAT_DEBITNOTE = '.$param['status'].' AND (NOFAKTUR_DEBITNOTE LIKE "%'.$param['search'].'%" OR TGLFAKTUR_DEBITNOTE LIKE "%'.$param['search'].'%" OR TGLJATUH_DEBITNOTE LIKE "%'.$param['search'].'%" OR NOFAKTURPAJAK_DEBITNOTE LIKE "%'.$param['search'].'%" OR NAMAPERUSAHAAN_DEBITNOTE LIKE "%'.$param['search'].'%" OR BARANGJASA_DEBITNOTE LIKE "%'.$param['search'].'%")')->result();
            $totalDisplayRecords    = count($this->db->query('SELECT * FROM DEBITNOTE WHERE STAT_DEBITNOTE = '.$param['status'].' AND (NOFAKTUR_DEBITNOTE LIKE "%'.$param['search'].'%" OR TGLFAKTUR_DEBITNOTE LIKE "%'.$param['search'].'%" OR TGLJATUH_DEBITNOTE LIKE "%'.$param['search'].'%" OR NOFAKTURPAJAK_DEBITNOTE LIKE "%'.$param['search'].'%" OR NAMAPERUSAHAAN_DEBITNOTE LIKE "%'.$param['search'].'%" OR BARANGJASA_DEBITNOTE LIKE "%'.$param['search'].'%")')->result());
        }else{
            $records                = $this->db->where('STAT_DEBITNOTE', $param['status'])->limit($param['limit'], $param['offset'])->get('DEBITNOTE')->result();
            $totalDisplayRecords    = $this->db->where('STAT_DEBITNOTE', $param['status'])->limit($param['limit'], $param['offset'])->from('DEBITNOTE')->count_all_results();
        }
        $this->db->select('ID_DEBITNOTE');
        $this->db->from('DEBITNOTE');
        $this->db->where('STAT_DEBITNOTE', $param['status']);
        $totalRecords = $this->db->count_all_results();

        return ['records' => $records, 'totalDisplayRecords' => $totalDisplayRecords, 'totalRecords' => $totalRecords];
    }
    public function getdn(){
        $sql = "SELECT sum(GRANDTOTAL_DEBITNOTE) as total 
                FROM DEBITNOTE WHERE STAT_DEBITNOTE = 5 
                OR STAT_DEBITNOTE = 6";
        $result = $this->db->query($sql);
        return $result->row()->total;
    }
    public function getovddn(){
        $sql = "SELECT sum(GRANDTOTAL_DEBITNOTE) as total FROM DEBITNOTE WHERE STAT_DEBITNOTE=5";
        $result = $this->db->query($sql);
        return $result->row()->total;
    }    
    public function getrcvdn(){
        $sql = "SELECT sum(GRANDTOTAL_DEBITNOTE) as total FROM DEBITNOTE WHERE STAT_DEBITNOTE=6";
        $result = $this->db->query($sql);
        return $result->row()->total;
    }    
    public function getOvdPassTwoYear(){
        $sql = "SELECT SUM(GRANDTOTAL_DEBITNOTE) AS TOTAL FROM DEBITNOTE WHERE TGLJATUH_DEBITNOTE + INTERVAL 2 YEAR <= NOW() AND STAT_DEBITNOTE = '5'";
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getUtilCharge(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND 
        (TIPE_DEBITNOTE = 'Telefon' OR TIPE_DEBITNOTE = 'Listrik' OR TIPE_DEBITNOTE = 'Air')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getUtilOverdue(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 5 AND 
        (TIPE_DEBITNOTE = 'Telefon' OR TIPE_DEBITNOTE = 'Listrik' OR TIPE_DEBITNOTE = 'Air')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getRentCharge(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND (TIPE_DEBITNOTE = 'Rent' OR TIPE_DEBITNOTE = 'Service')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getRentOverdue(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 5 AND (TIPE_DEBITNOTE = 'Rent' OR TIPE_DEBITNOTE = 'Service')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getOthersCharge(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND (TIPE_DEBITNOTE = 'Others')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getOthersOverdue(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 5 AND (TIPE_DEBITNOTE = 'Others')";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getTahunanDN2020(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL,
        YEAR(TGLFAKTUR_DEBITNOTE) as TAHUN,
        TIPE_DEBITNOTE as TIPE
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND YEAR(TGLFAKTUR_DEBITNOTE) = 2020
        GROUP BY YEAR(TGLFAKTUR_DEBITNOTE), TIPE_DEBITNOTE";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getTahunanDN(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL,
        YEAR(TGLFAKTUR_DEBITNOTE) as TAHUN,
        TIPE_DEBITNOTE as TIPE
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND YEAR(TGLFAKTUR_DEBITNOTE) = 2021
        GROUP BY YEAR(TGLFAKTUR_DEBITNOTE), TIPE_DEBITNOTE";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getPaymentDN($param){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL,
        YEAR(TGLFAKTUR_DEBITNOTE) as TAHUN,
        TIPE_DEBITNOTE as TIPE
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND YEAR(TGLFAKTUR_DEBITNOTE) = '$param'
        GROUP BY YEAR(TGLFAKTUR_DEBITNOTE), TIPE_DEBITNOTE";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function grandTotal(){
        $sql = "SELECT 
        SUM(GRANDTOTAL_DEBITNOTE) as TOTAL,
        TIPE_DEBITNOTE as TIPE
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6
        GROUP BY TIPE_DEBITNOTE";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getmonthlydn($param){
        $sql = "SELECT SUM(GRANDTOTAL_DEBITNOTE) as TOTAL, MONTH(TGLFAKTUR_DEBITNOTE) as BULAN 
        FROM DEBITNOTE WHERE YEAR(TGLFAKTUR_DEBITNOTE) = '$param' AND 
        (STAT_DEBITNOTE = 4 OR STAT_DEBITNOTE = 5 OR STAT_DEBITNOTE = 6)
        GROUP BY MONTH(TGLFAKTUR_DEBITNOTE)";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getBulanFinishDN($param){
        $sql = "SELECT SUM(GRANDTOTAL_DEBITNOTE) as TOTAL, MONTH(TGLBAYAR_DEBITNOTE) as BULAN
        FROM DEBITNOTE 
        WHERE STAT_DEBITNOTE = 6 AND YEAR(TGLFAKTUR_DEBITNOTE) = '".$param."'
        GROUP BY MONTH(TGLBAYAR_DEBITNOTE)
        ORDER BY MONTH(TGLBAYAR_DEBITNOTE) ASC";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getYearDN(){
        $result = $this->db->order_by('YEAR_REYEACTIVE', 'desc')->get('DEBITNOTE_REPORTING_YEARLY_ACTIVE')->result();
        return $result;
    }
    public function getTopTenantsDN(){
        $sql = "SELECT NAMAPERUSAHAAN_DEBITNOTE , SUM(GRANDTOTAL_DEBITNOTE) AS TOTAL 
        FROM DEBITNOTE d WHERE TGLPUBLISHED_DEBITNOTE + INTERVAL 60 DAY >= NOW() 
        AND STAT_DEBITNOTE = '6' 
        GROUP BY NAMAPERUSAHAAN_DEBITNOTE 
        ORDER BY SUM(GRANDTOTAL_DEBITNOTE) DESC";
        $result = $this->db->query($sql)->result();
        return $result;
    }
    public function getAgingTigaPuluh(){
        $sql = "SELECT COUNT(*) AS TOTAL 
        FROM DEBITNOTE WHERE 
        TGLFAKTUR_DEBITNOTE + INTERVAL 30 DAY > NOW() AND
        STAT_DEBITNOTE = 4";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getAgingTigaEnam(){
        $sql = "SELECT COUNT(*) AS TOTAL 
        FROM DEBITNOTE
        WHERE 
        TGLFAKTUR_DEBITNOTE + INTERVAL 30 DAY <= NOW() AND
        TGLFAKTUR_DEBITNOTE + INTERVAL 60 DAY >= NOW() AND
        STAT_DEBITNOTE = 5";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getAgingEnamPuluh(){
        $sql = "SELECT COUNT(*) AS TOTAL 
        FROM DEBITNOTE 
        WHERE 
        TGLFAKTUR_DEBITNOTE + INTERVAL 60 DAY < NOW() AND
        STAT_DEBITNOTE = 5";        
        $result = $this->db->query($sql);
        return $result->row()->TOTAL;
    }
    public function getReminder($param){
        if(!empty($param['whereIn'])){
            $this->db->where_in($param['whereIn']['table'], $param['whereIn']['values']);
            unset($param['whereIn']);
        }
        if(!empty($param['orderBy'])){
            $this->db->order_by($param['orderBy']);
            unset($param['orderBy']);
        }

        $this->db->where($param);
        $res = $this->db->get('DEBITNOTE')->result();
        return $res;
    }
    public function insert($param){
        $this->db->insert('DEBITNOTE', $param);
        return $this->db->insert_id();
    }
    public function insertBatch($param){
        return $this->db->insert_batch('DEBITNOTE', $param);
    }
    public function update($param){
        $this->db->where('ID_DEBITNOTE', $param['ID_DEBITNOTE'])->update('DEBITNOTE', $param);
        return true;
    }
    public function updateStatusMulti($param){
        if(!empty($param['CATATANREVERSE_DEBITNOTE'])){
            $this->db->where_in('ID_DEBITNOTE', $param['ID_DEBITNOTES'])->update('DEBITNOTE', ['STAT_DEBITNOTE' => $param['STATUS'], 'CATATANREVERSE_DEBITNOTE' => $param['CATATANREVERSE_DEBITNOTE']]);
        }else if(!empty($param['TGLBAYAR_DEBITNOTE'])){
            $this->db->where_in('ID_DEBITNOTE', $param['ID_DEBITNOTES'])->update('DEBITNOTE', ['STAT_DEBITNOTE' => $param['STATUS'], 'TGLBAYAR_DEBITNOTE' => $param['TGLBAYAR_DEBITNOTE']]);
        }else{
            $this->db->where_in('ID_DEBITNOTE', $param['ID_DEBITNOTES'])->update('DEBITNOTE', ['STAT_DEBITNOTE' => $param['STATUS']]);
        }
        return true;
    }
    public function delete($param){
        $this->db->where($param)->delete('DEBITNOTE');
        return true;
    }
    public function deleteMulti($param){
        $this->db->where_in('ID_DEBITNOTE', $param)->delete('DEBITNOTE');
        return true;
    }

    public function generate($param){
        $debitnote = $this->db->get_where('DEBITNOTE', ['ID_DEBITNOTE' => $param['ID_DEBITNOTE']])->result();
        $approvals = $this->db->get_where('V_DEBITNOTE_APPROVAL', ['ID_DEBITNOTE' => $param['ID_DEBITNOTE']])->result();
        $data['list']       = $debitnote;
        $data['approvals']  = $approvals;
        $data['getMonth']   = $this->datefunction->getMonth();
		       
        $file_pdf = $debitnote[0]->NOFAKTUR_DEBITNOTE.'_'.$debitnote[0]->NAMAPERUSAHAAN_DEBITNOTE.'_'.time();
        $path_pdf = 'uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE.'/'.$file_pdf.'.pdf';
		
        $paper = 'A4';
        $orientation = 'portrait';
        
		$html = $this->load->view('pdf_template/debit_note', $data, true);	    

        $resPdf = $this->pdfgenerator->generate($html, $file_pdf,$paper,$orientation);
        if(!is_dir('./uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE)){
            mkdir('./uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE, 0777, TRUE);
        }
        file_put_contents($path_pdf, $resPdf);

        $param['PATH_DEBITNOTE'] = base_url($path_pdf);
        $this->update($param);
        
        return true;
    }
    public function generateMulti($param){
        foreach($param as $item){
            $debitnote = $this->db->get_where('DEBITNOTE', ['ID_DEBITNOTE' => $item])->result();
            $approvals = $this->db->get_where('V_DEBITNOTE_APPROVAL', ['ID_DEBITNOTE' => $item])->result();
            $data['list']       = $debitnote;
            $data['approvals']  = $approvals;
            $data['getMonth']   = $this->datefunction->getMonth();
                   
            $file_pdf = $debitnote[0]->NOFAKTUR_DEBITNOTE.'_'.$debitnote[0]->NAMAPERUSAHAAN_DEBITNOTE.'_'.time();
            $path_pdf = 'uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE.'/'.$file_pdf.'.pdf';
            
            $paper = 'A4';
            $orientation = 'portrait';
            
            $html = $this->load->view('pdf_template/debit_note', $data, true);	    
    
            $resPdf = $this->pdfgenerator->generate($html, $file_pdf,$paper,$orientation);
            if(!is_dir('./uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE)){
                mkdir('./uploads/debitnote/generated/'.$debitnote[0]->EMAIL_DEBITNOTE, 0777, TRUE);
            }
            file_put_contents($path_pdf, $resPdf);
            
            $dataUpdate['ID_DEBITNOTE']     = $item;
            $dataUpdate['STAT_DEBITNOTE']   = '1';
            $dataUpdate['PATH_DEBITNOTE']   = base_url($path_pdf);
            $this->update($dataUpdate);
        }
        
        return true;
    }

    public function getReport($tipeDebitnote){
        $years      = $this->getYearSummaryActive();
        $datas      = array();
        $yearsData  = array();
        foreach ($years as $item) {
            array_push($yearsData, $item->YEAR_REYEACTIVE);
            $datas[$item->YEAR_REYEACTIVE] = $this->db->query('SELECT GRANDTOTAL_DEBITNOTE, TGLBAYAR_DEBITNOTE FROM DEBITNOTE WHERE  STAT_DEBITNOTE IN ("4", "5", "6") AND TIPE_DEBITNOTE = "'.$tipeDebitnote.'" AND YEAR(TGLFAKTUR_DEBITNOTE) = "'.$item->YEAR_REYEACTIVE.'" AND (TGLBAYAR_DEBITNOTE IS NULL OR YEAR(TGLBAYAR_DEBITNOTE) = "'.date('Y').'") ')->result();
        }
        return ['debitnotes' => $datas, 'years' => $yearsData];
    }

    public function getReportSummary($param){
        return $this->db->get_where('DEBITNOTE_REPORTING_YEARLY', $param)->row();
    }
    
    public function getAllReportSummary($param){
        if(empty($param['ORDER_DESC'])){
            $res = $this->db->order_by('TAHUNBAYAR_REPORTINGYEARLY', 'asc')->get_where('DEBITNOTE_REPORTING_YEARLY', $param)->result();
        }else{
            unset($param['ORDER_DESC']);
            $res = $this->db->order_by('TAHUNBAYAR_REPORTINGYEARLY', 'desc')->get_where('DEBITNOTE_REPORTING_YEARLY', $param)->result();
        }
        return $res;
    }

    public function getSumReportSummaryActive(){
        return $this->db->query('SELECT TAHUN_REPORTINGYEARLY , SUM(TARGET_REPORTINGYEARLY) AS TARGET_REPORTINGYEARLY, SUM(TOTAL_REPORTINGYEARLY) AS TOTAL_REPORTINGYEARLY
            FROM DEBITNOTE_REPORTING_YEARLY
            WHERE TAHUNBAYAR_REPORTINGYEARLY = "'.((int)date('Y')-1).'"
            GROUP BY TAHUN_REPORTINGYEARLY 
            ORDER BY TAHUN_REPORTINGYEARLY ASC')->result();
    }

    public function getTargetTotalDebitNote($param){
        return $this->db->query("SELECT SUM(GRANDTOTAL_DEBITNOTE) as TOTAL
        FROM DEBITNOTE WHERE YEAR(TGLFAKTUR_DEBITNOTE) = '".$param['tahun']."' AND TIPE_DEBITNOTE = '".$param['tipeDebitNote']."' AND
        (STAT_DEBITNOTE = 4 OR STAT_DEBITNOTE = 5 OR STAT_DEBITNOTE = 6)")->row();
    }
   
    public function insertReportSummary($param){
        $this->db->insert('DEBITNOTE_REPORTING_YEARLY', $param);
    }

    public function updateReportSummary($param){
        $this->db->where('ID_REPORTINGYEARLY', $param['ID_REPORTINGYEARLY'])->update('DEBITNOTE_REPORTING_YEARLY', $param);
    }

    public function getYearSummaryActive(){
        return $this->db->get_where('DEBITNOTE_REPORTING_YEARLY_ACTIVE', ['ISACTIVE_REYEACTIVE' => true])->result();
    }

    public function getYearSummary(){
        return $this->db->order_by('YEAR_REYEACTIVE', 'desc')->get('DEBITNOTE_REPORTING_YEARLY_ACTIVE')->row();
    }
    public function insertYearSummary(){
        $this->db->insert('DEBITNOTE_REPORTING_YEARLY_ACTIVE', ['YEAR_REYEACTIVE' => date('Y')]);
    }
    public function updateYearSummary($param){
        $this->db->where('YEAR_REYEACTIVE', $param['YEAR_REYEACTIVE'])->update('DEBITNOTE_REPORTING_YEARLY_ACTIVE', $param);
    }
}