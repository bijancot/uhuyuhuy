<?php
defined('BASEPATH') or exit('No direct script access allowed');

class NudgeController extends CI_Controller
{
    function __construct(){
        parent::__construct();
        $this->load->model('User');
        $this->load->library(array('upload', 'notification'));
    }
    public function dnd(){
        $DB2 = $this->load->database('gaSys2', true);
        $nudges = $this->getQueryNudgeDND();

        foreach ($nudges as $nudge) {
            $DB2->where("project_no", $nudge->project_no)->update('master_project', ['project_nudge_dnd' => 1]);
        }
    }
    public function tender(){
        $DB2 = $this->load->database('gaSys2', true);
        $nudges = $this->getQueryNudgeTender();

        foreach ($nudges as $nudge) {
            $DB2->where("project_no", $nudge->project_no)->update('master_project', ['project_nudge_tender' => 1]);
        }
        print_r($nudges);
    }
    public function getQueryNudgeDND(){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT
                mp.project_no ,
                mp.proposed_date ,
                IF(ttd.document_no IS NULL, 0, 1) as file_tor,
                IF(td.tender_no IS NULL, 0, 1) as form_tender,
                mp.project_nudge_dnd 
            FROM master_project mp 
                LEFT JOIN transaction_tor_document ttd 
                    ON mp.project_no = ttd.project_no 
                LEFT JOIN transaction_tender td
                    ON mp.project_no = td.project_no
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no
            WHERE 
                mp.project_stat  = '1'
                AND mp.project_nudge_dnd = 0
                AND DATE(mp.proposed_date + INTERVAL 3 day) < NOW() 
                AND (ttd.document_no IS NULL OR td.tender_no IS NULL)
            GROUP BY mp.project_no 
            ORDER BY mp.proposed_date DESC
        ")->result();
    }

    public function getQueryNudgeTender(){
        $DB2 = $this->load->database('gaSys2', true);

        $totalMandatory = $DB2->query("
            SELECT COUNT(*) as total_mandatory
            FROM master_tender_document mtd 
            WHERE mtd.tender_document_mandatory = '1'
        ")->row();

        return $DB2->query("
            SELECT
                mp.project_no,
                IF(c.contract_no IS NULL, 0, 1) as form_contract,
                (
                    SELECT COUNT(*) OVER() as TOTAL
                    FROM transaction_tender_document ttd, master_tender_document mtd 
                    WHERE 
                        ttd.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
                        AND ttd.tender_document_no IN (
                            SELECT mtd.tender_document_no
                            FROM master_tender_document mtd 
                            WHERE mtd.tender_document_mandatory = '1'
                        )
                    GROUP BY ttd.tender_document_no
                    LIMIT 1
                ) as total_doc_mandatory,
                mp.approved_date 
            FROM master_project mp 
                LEFT JOIN transaction_tender_document ttd 
                    ON mp.project_no = ttd.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN transaction_contract c
                    ON mp.project_no = c.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no 
            WHERE 
                mp.project_stat_app  = '1'
                AND mp.project_nudge_tender = 0
                AND DATE(mp.approved_date  + INTERVAL 3 day) < NOW()
                AND (
                    c.contract_no IS NULL
                    OR (
                        SELECT COUNT(*) OVER() as TOTAL
                        FROM transaction_tender_document ttd, master_tender_document mtd 
                        WHERE 
                            ttd.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
                            AND ttd.tender_document_no IN (
                                SELECT mtd.tender_document_no
                                FROM master_tender_document mtd 
                                WHERE mtd.tender_document_mandatory = '1'
                            )
                        GROUP BY ttd.tender_document_no
                        LIMIT 1
                    ) < ".$totalMandatory->total_mandatory."
                )
            GROUP BY mp.project_no 
            ORDER BY mp.approved_date DESC
        ")->result();
    }
    public function queryGetDocTender($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        $docs['fsv'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '1'
        ")->row();
        $docs['gambar'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '3'
        ")->row();
        $docs['bastl'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '4'
        ")->row();
        $docs['sCurve'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '2'
        ")->row();

        return $docs;
    }
}