<?php

class DebitNoteCronJobController extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model('DebitNote');
    }
    public function updateProgress(){
        $date = date('Y-m-d');
        $debitNotes = $this->DebitNote->getAll(['STAT_DEBITNOTE' => '4']);

        if ($debitNotes != null) {
            foreach ($debitNotes as $item) {
                $dnDateEnd = date_create($item->TGLJATUH_DEBITNOTE);
                $dnDateEnd = date_format($dnDateEnd, 'Y-m-d');

                if ($date > $dnDateEnd) {
                    $this->DebitNote->update(['ID_DEBITNOTE' => $item->ID_DEBITNOTE, 'STAT_DEBITNOTE' => '5']);
                }
            }
        }
    }

    public function updateReport($tipeDebitnote){
        $res        = $this->DebitNote->getReport($tipeDebitnote);
        $reports    = array();
        foreach ($res['years'] as $item) {
            $reports[$item]['TAHUN_REPORTINGYEARLY']        = $item;
            $reports[$item]['TIPE_REPORTINGYEARLY']         = $tipeDebitnote;
            $reports[$item]['TARGETTOTAL_REPORTINGYEARLY']  = $this->DebitNote->getTargetTotalDebitNote(['tipeDebitNote' => $tipeDebitnote, 'tahun' => $item])->TOTAL;
            $reports[$item]['TARGET_REPORTINGYEARLY']       = 0;
            $reports[$item]['TOTAL_REPORTINGYEARLY']        = 0;
            $reports[$item]['TAHUNBAYAR_REPORTINGYEARLY']   = date('Y');
            $reports[$item]['TGLSYNC_REPORTINGYEARLY']      = date('Y-m-d H:i:s');
            foreach ($res['debitnotes'][$item] as $item2) {
                $reports[$item]['TARGET_REPORTINGYEARLY'] += $item2->GRANDTOTAL_DEBITNOTE;
                if($item2->TGLBAYAR_DEBITNOTE != null){
                    $reports[$item]['TOTAL_REPORTINGYEARLY'] += $item2->GRANDTOTAL_DEBITNOTE;
                }
            }
        }

        foreach ($reports as $item) {
            $filter = array(
                'TAHUN_REPORTINGYEARLY'         => $item['TAHUN_REPORTINGYEARLY'],
                'TAHUNBAYAR_REPORTINGYEARLY'    => $item['TAHUNBAYAR_REPORTINGYEARLY'],
                'TIPE_REPORTINGYEARLY'          => $item['TIPE_REPORTINGYEARLY']
            );
            $reportSumm = $this->DebitNote->getReportSummary($filter);
            if($reportSumm == null){
                $this->DebitNote->insertReportSummary($reports[$item['TAHUN_REPORTINGYEARLY']]);
            }else{
                $reports[$item['TAHUN_REPORTINGYEARLY']]['ID_REPORTINGYEARLY'] = $reportSumm->ID_REPORTINGYEARLY;
                $this->DebitNote->updateReportSummary($reports[$item['TAHUN_REPORTINGYEARLY']]);
            }
        }
        print_r($reports);
    }

    public function updateYearActive(){
        $year = $this->DebitNote->getYearSummary();
        if($year != null){  
            if($year->YEAR_REYEACTIVE != date('Y')){
                $this->DebitNote->insertYearSummary(); 
            }
        }else{
            $this->DebitNote->insertYearSummary(); 
        }

        $reportSum = $this->DebitNote->getSumReportSummaryActive();
        foreach ($reportSum as $item) {
            if($item->TARGET_REPORTINGYEARLY == $item->TOTAL_REPORTINGYEARLY){
                $this->DebitNote->updateYearSummary(['YEAR_REYEACTIVE' => $item->TAHUN_REPORTINGYEARLY, 'ISACTIVE_REYEACTIVE' => 0]);
            }
        }
    }
}