<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once(FCPATH . '/vendor/autoload.php');

class Cop extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }
    public function generate_post(){
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['copType'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);

            $detailPayments = $this->queryGetPayment($project[0]->project_no);
            if($param['copType'] == '1'){
                $copType = "COP DP";
            }else if($param['copType'] == '2'){
                if((float)$project[0]->project_prog_weekly < (float)$param['percent']){
                    $this->response(['status' => false, 'message' => 'Generate COP belum memenuhi progress weekly!'], 200);
                }
                $copType = "COP PROGRESS";
            }else if($param['copType'] == '3'){
                if((float) $project[0]->project_prog_weekly < 100){
                    $this->response(['status' => false, 'message' => 'Generate COP belum progress weekly 100% !'], 200);
                }
                $copType = "COP RETENCY";
            }

            $copId = $param['projectNo'].time();
            $COPData['cop_id']                  = $copId; 
            $COPData['project_no']              = $project[0]->project_no;
            $COPData['cop_progress']            = $project[0]->project_prog_weekly;
            $COPData['cop_date']                = date('Y-m-d H:i:s');
            $COPData['cop_type']                = $copType;
            $DB2->insert('transaction_cop', $COPData);

            $detailContract = $this->queryGetDetailContract($project[0]->project_no);
            $detailPayments = $this->queryGetPayment($project[0]->project_no);

            $linkCOP = $this->makeCOP($detailContract, $detailPayments);
            $DB2->where('cop_id', $copId)->update('transaction_cop', ['cop_link' => $linkCOP]);

            $mapping = $DB2->get_where('master_mapping', ['mapping_id' => '3'])->result_array();
            $formData['approval_id']            = 'TRANS_'.md5(time()."trans");
            $formData['mapping_id']             = 3;
            $formData['project_no']             = $project[0]->project_no;
            $formData['approval_path']          = $linkCOP;
            $formData['approval_filename']      = "TESTING COP";
            $formData['approval_date']          = date('Y-m-d H:i:s');
            $formData['approval_send_vendor']   = 1;
            $DB2->insert('transaction_approval', $formData);

            for($i = 1; $i <= 7; $i++){
                if(!empty($mapping[0]['mapping_app_'.$i]) && $mapping[0]['mapping_app_'.$i] != null){
                    $DB2->insert('transaction_approval_detail', ['approval_id' => $formData['approval_id'], 'approval_detail_role' => $mapping[0]['mapping_app_'.$i]]);
                }
            }

            $this->response(['status' => true, 'message' => 'Berhasil generate COP!'], 200);
            // End Validation
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function makeCOP($detailContract, $detailPayments){
        $colPay[0] = ['I', 'H', 'I']; 
        $colPay[1] = ['II', 'J', 'K']; 
        $colPay[2] = ['III', 'L', 'M']; 
        $colPay[3] = ['IV', 'N', 'O']; 
        $colPay[4] = ['V', 'P', 'Q']; 
        $colPay[5] = ['VI', 'R', 'S']; 
        $colPay[6] = ['VII', 'T', 'U']; 
        $colPay[7] = ['VIII', 'V', 'W']; 
        $colPay[8] = ['IX', 'X', 'Y'];
        $colPay[9] = ['X', 'Z', 'AA'];

        $index = 0;
        $payments[$index]['no']       = ($index + 1);
        $payments[$index]['name']     = 'Termin I';
        $payments[$index]['desc']     = 'DP '.$detailContract->contract_dp."%";
        $payments[$index++]['weight']   = $detailContract->contract_dp."%";

        $progresss = explode(';', $detailContract->contract_progress);
        foreach ($progresss as $progress) {
            $progDetail         = explode('_', $progress);
            $temp['no']         = ($index + 1);
            $temp['name']       = 'Termin  '.$colPay[$index][0];
            $temp['desc']       = 'Progress '.$progDetail[0]."%";
            $temp['weight']     = $progDetail[1]."%";
            $payments[$index++]  = $temp;
        }

        $payments[$index]['no']       = ($index + 1);
        $payments[$index]['name']     = 'Retensi '.$detailContract->contract_retensi."%";
        $payments[$index]['desc']     = 'Retensi';
        $payments[$index]['weight']   = $detailContract->contract_retensi."%";
        // === STYLING SHEETS ===
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        $styleBrHeading1['font']['bold']                         = true;
        $styleBrHeading1['font']['size']                         = 20;
        $styleBrHeading1['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBrHeading1['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleBrHeading1['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        
        $styleBrHeading2['font']['bold']                         = true;
        $styleBrHeading2['font']['size']                         = 16;
        $styleBrHeading2['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBrHeading2['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBrHeading2['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleHeading3['font']['bold']              = true;
        $styleHeading3['font']['size']              = 12;
        $styleHeading3['alignment']['vertical']     = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleContent['font']['size']                     = 10;
        $styleContent['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleContentSmall['font']['size']                     = 8;
        $styleContentSmall['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleTcContent['font']['size']                     = 10;
        $styleTcContent['font']['size']                     = 10;
        $styleTcContent['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTcContent['alignment']['horizontal']          = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleTrContent['font']['size']                     = 10;
        $styleTrContent['font']['size']                     = 10;
        $styleTrContent['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTrContent['alignment']['horizontal']          = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
        
        $styleBrtbContent['font']['size']                     = 10;
        $styleBrtbContent['font']['size']                     = 10;
        $styleBrtbContent['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleBrtbContent['borders']['top']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBrtbContent['borders']['bottom']['borderStyle'] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $styleBrtbTcContent['font']['size']                     = 10;
        $styleBrtbTcContent['font']['size']                     = 10;
        $styleBrtbTcContent['alignment']['vertical']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleBrtbTcContent['alignment']['horizontal']          = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBrtbTcContent['borders']['top']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBrtbTcContent['borders']['bottom']['borderStyle'] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $styleBrContent['font']['size']                         = 10;
        $styleBrContent['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBrContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleBrTcContent['font']['size']                         = 10;
        $styleBrTcContent['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBrTcContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleBrTcContent['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleBBrTcContent['font']['size']                          = 10;
        $styleBBrTcContent['font']['bold']                          = true;
        $styleBBrTcContent['borders']['outline']['borderStyle']     = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBBrTcContent['alignment']['vertical']                 = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleBBrTcContent['alignment']['horizontal']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleBContent['font']['size']                         = 10;
        $styleBContent['font']['bold']                         = true;
        $styleBContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleBTcContent['font']['size']                         = 10;
        $styleBTcContent['font']['bold']                         = true;
        $styleBTcContent['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBTcContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleBUContent['font']['size']                         = 10;
        $styleBUContent['font']['bold']                         = true;
        $styleBUContent['font']['underline']                    = true;
        $styleBUContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleIContent['font']['size']                         = 10;
        $styleIContent['font']['italic']                       = true;
        $styleIContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleIContentRed['font']['size']               = 10;
        $styleIContentRed['font']['color']['argb']      = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
        $styleIContentRed['font']['italic']             = true;
        $styleIContentRed['alignment']['vertical']      = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleColBlue['font']['size']                       = 10;
        $styleColBlue['font']['bold']                       = true;
        $styleColBlue['font']['color']['argb']              = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleColBlue['fill']['fillType']                   = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleColBlue['fill']['color']['argb']              = '2F5496';
        $styleColBlue['borders']['outline']['borderStyle']  = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleColBlue['alignment']['vertical']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleColBlue['alignment']['horizontal']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleColYellow['font']['size']                       = 10;
        $styleColYellow['fill']['fillType']                   = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleColYellow['fill']['color']['argb']              = 'FFCC00';
        $styleColYellow['borders']['outline']['borderStyle']  = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleColYellow['alignment']['vertical']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleColYellow['alignment']['horizontal']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleColGreen['font']['size']                       = 10;
        $styleColGreen['font']['bold']                       = true;
        $styleColGreen['font']['color']['argb']              = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleColGreen['fill']['fillType']                   = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleColGreen['fill']['color']['argb']              = '70AD47';
        $styleColGreen['borders']['outline']['borderStyle']  = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleColGreen['alignment']['vertical']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleColGreen['alignment']['horizontal']            = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        $styleFillGrey['font']['size']                       = 11;
        $styleFillGrey['font']['bold']                       = true;
        $styleFillGrey['fill']['fillType']                   = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleFillGrey['fill']['color']['argb']              = '969696';
        $styleFillGrey['alignment']['vertical']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleTitle['font']['color']['argb']        = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleTitle['fill']['fillType']             = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleTitle['fill']['color']['argb']        = 'FF595959';
        $styleTitle['alignment']['vertical']        = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle['alignment']['horizontal']      = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        
        // HEADER
        $sheet->mergeCells('A2:E4')->setCellValue('A2', " ")->getStyle('A2:E4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('F2:Q4')->setCellValue('F2', "CERTIFICATE OF PAYMENT")->getStyle('F2:Q4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('R2:U4')->setCellValue('R2', "INTEGRASI SISTEM")->getStyle('R2:U4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('A5:E7')->setCellValue('A5', $detailContract->area_branch_address)->getStyle('A5:E7')->applyFromArray($styleBrHeading2);
        $sheet->mergeCells('F5:I5')->setCellValue('F5', "Nomor Dokumen")->getStyle('F5:I5')->applyFromArray($styleBrtbContent);
        $sheet->setCellValue('J5', ":")->getStyle('J5')->applyFromArray($styleBrtbTcContent);
        $sheet->mergeCells('K5:Q5')->setCellValue('K5', "FORM 022-PROS-MFP-MLK3-015")->getStyle('K5:Q5')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R5:U7')->setCellValue('R5', "ISO 9001:2015; ISO 14001:2015, ISO 450001:2018 & SMK3")->getStyle('R5:U7')->applyFromArray($styleBrHeading2)->getAlignment()->setWrapText(true);;
        $sheet->mergeCells('F6:I6')->setCellValue('F6', "Revisi")->getStyle('F6:I6')->applyFromArray($styleBrtbContent);
        $sheet->setCellValue('J6', ":")->getStyle('J6')->applyFromArray($styleBrtbTcContent);
        $sheet->mergeCells('K6:Q6')->setCellValue('K6', "0")->getStyle('K6:Q6')->applyFromArray($styleBrContent);
        $sheet->mergeCells('F7:I7')->setCellValue('F7', "Hal")->getStyle('F7:I7')->applyFromArray($styleBrtbContent);
        $sheet->setCellValue('J7', ":")->getStyle('J7')->applyFromArray($styleBrtbTcContent);
        $sheet->mergeCells('K7:Q7')->setCellValue('K7', "1 Dari : 1")->getStyle('K7:Q7')->applyFromArray($styleBrContent);

        $sheet->mergeCells('A10:C10')->setCellValue('A10', "Nomor COP")->getStyle('A10:C10')->applyFromArray($styleContent);
        $sheet->setCellValue('D10', ":")->getStyle('D10')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E10', "??")->getStyle('E10')->applyFromArray($styleContent);
        $sheet->mergeCells('A11:C11')->setCellValue('A11', "Nama Project")->getStyle('A11:C11')->applyFromArray($styleContent);
        $sheet->setCellValue('D11', ":")->getStyle('D11')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E11', $detailContract->project_name)->getStyle('E11')->applyFromArray($styleContent);
        $sheet->mergeCells('A12:C12')->setCellValue('A12', "Nomor Proyek")->getStyle('A12:C12')->applyFromArray($styleContent);
        $sheet->setCellValue('D12', ":")->getStyle('D12')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E12', $detailContract->project_no)->getStyle('E12')->applyFromArray($styleContent);
        $sheet->mergeCells('A13:C13')->setCellValue('A13', "Lokasi Project")->getStyle('A13:C13')->applyFromArray($styleContent);
        $sheet->setCellValue('D13', ":")->getStyle('D13')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E13', $detailContract->area_branch_address)->getStyle('E13')->applyFromArray($styleContent);
        $sheet->mergeCells('A14:C14')->setCellValue('A14', "Area Project")->getStyle('A14:C14')->applyFromArray($styleContent);
        $sheet->setCellValue('D14', ":")->getStyle('D14')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E14', $detailContract->area_name)->getStyle('E14')->applyFromArray($styleContent);
        $sheet->mergeCells('A15:C15')->setCellValue('A15', "Kontraktor")->getStyle('A15:C15')->applyFromArray($styleContent);
        $sheet->setCellValue('D15', ":")->getStyle('D15')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E15', $detailContract->vendor_name)->getStyle('E15')->applyFromArray($styleContent);
        // END HEADER
        
        // A KONTRAK AMANDEMEN
        $sheet->setCellValue('A18', "A. Kontrak / Amandemen")->getStyle('A18')->applyFromArray($styleHeading3);
        $sheet->setCellValue('A19', "No.")->getStyle('A19')->applyFromArray($styleColBlue);
        $sheet->mergeCells('B19:F19')->setCellValue('B19', "Item")->getStyle('B19:F19')->applyFromArray($styleColBlue);
        $sheet->setCellValue('G19', "Tgl")->getStyle('G19')->applyFromArray($styleColBlue);
        $sheet->mergeCells('H19:K19')->setCellValue('H19', "No. Kontrak / Amandemen")->getStyle('H19:K19')->applyFromArray($styleColBlue);
        $sheet->mergeCells('L19:Q19')->setCellValue('L19', "Nilai Kontrak")->getStyle('L19:Q19')->applyFromArray($styleColBlue);
        $sheet->mergeCells('R19:U19')->setCellValue('R19', "Keterangan")->getStyle('R19:U19')->applyFromArray($styleColBlue);
        
        $sheet->setCellValue('A20', "1")->getStyle('A20')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B20:F20')->setCellValue('B20', "Nilai Kontrak Akhir (sesuai PO)")->getStyle('B20:F20')->applyFromArray($styleBrContent);
        $sheet->setCellValue('G20', $detailContract->submitted_date)->getStyle('G20')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H20:K20')->setCellValue('H20', $detailContract->contract_name_file)->getStyle('H20:K20')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L20:Q20')->setCellValue('L20', $detailContract->project_kontrak)->getStyle('L20:Q20')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R20:U20')->setCellValue('R20', "")->getStyle('R20:U20')->applyFromArray($styleBrContent);
        
        $sheet->setCellValue('A21', "2")->getStyle('A21')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B21:F21')->setCellValue('B21', "Amandemen I")->getStyle('B21:F21')->applyFromArray($styleBrContent);
        $sheet->setCellValue('G21', "")->getStyle('G21')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H21:K21')->setCellValue('H21', "")->getStyle('H21:K21')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L21:Q21')->setCellValue('L21', "")->getStyle('L21:Q21')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R21:U21')->setCellValue('R21', "")->getStyle('R21:U21')->applyFromArray($styleBrContent);
        $sheet->setCellValue('A22', "3")->getStyle('A22')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B22:F22')->setCellValue('B22', "Amandemen II")->getStyle('B22:F22')->applyFromArray($styleBrContent);
        $sheet->setCellValue('G22', "")->getStyle('G22')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H22:K22')->setCellValue('H22', "")->getStyle('H22:K22')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L22:Q22')->setCellValue('L22', "")->getStyle('L22:Q22')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R22:U22')->setCellValue('R22', "")->getStyle('R22:U22')->applyFromArray($styleBrContent);
        $sheet->setCellValue('A23', "4")->getStyle('A23')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B23:F23')->setCellValue('B23', "Amandemen III")->getStyle('B23:F23')->applyFromArray($styleBrContent);
        $sheet->setCellValue('G23', "")->getStyle('G23')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H23:K23')->setCellValue('H23', "")->getStyle('H23:K23')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L23:Q23')->setCellValue('L23', "")->getStyle('L23:Q23')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R23:U23')->setCellValue('R23', "")->getStyle('R23:U23')->applyFromArray($styleBrContent);
        $sheet->setCellValue('A24', "5")->getStyle('A24')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B24:F24')->setCellValue('B24', "Amandemen IV")->getStyle('B24:F24')->applyFromArray($styleBrContent);
        $sheet->setCellValue('G24', "")->getStyle('G24')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H24:K24')->setCellValue('H24', "")->getStyle('H24:K24')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L24:Q24')->setCellValue('L24', "")->getStyle('L24:Q24')->applyFromArray($styleBrContent);
        $sheet->mergeCells('R24:U24')->setCellValue('R24', "")->getStyle('R24:U24')->applyFromArray($styleBrContent);
        // END A KONTRAK AMANDEMEN
        
        $currRow = 26;
        // B VARIATION ORDER
        $sheet->setCellValue('A'.$currRow, "B. Variation Order")->getStyle('A'.$currRow)->applyFromArray($styleHeading3);
        $sheet->mergeCells('A'.++$currRow.':A'.($currRow + 1))->setCellValue('A'.$currRow, "No")->getStyle('A'.$currRow.':A'.($currRow + 1))->applyFromArray($styleColBlue);
        $sheet->mergeCells('B'.$currRow.':E'.($currRow + 1))->setCellValue('B'.$currRow, "No. VO atau SI")->getStyle('B'.$currRow.':E'.($currRow + 1))->applyFromArray($styleColBlue);
        $sheet->mergeCells('F'.$currRow.':I'.($currRow + 1))->setCellValue('F'.$currRow, "Content")->getStyle('F'.$currRow.':I'.($currRow + 1))->applyFromArray($styleColBlue);
        $sheet->mergeCells('J'.$currRow.':K'.($currRow + 1))->setCellValue('J'.$currRow, "Penambahan Nilai")->getStyle('J'.$currRow.':K'.($currRow + 1))->applyFromArray($styleColBlue);
        $sheet->mergeCells('L'.$currRow.':M'.($currRow + 1))->setCellValue('L'.$currRow, "Pengurangan Nilai")->getStyle('L'.$currRow.':M'.($currRow + 1))->applyFromArray($styleColBlue);
        $sheet->mergeCells('N'.$currRow.':Q'.$currRow)->setCellValue('N'.$currRow, "Termasuk kedalam Amandemen(beri tanda √ untuk kolom Y (Yes) atau N (No))")->getStyle('N'.$currRow.':Q'.$currRow)->applyFromArray($styleColBlue)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('R'.$currRow.':U'.($currRow + 1))->setCellValue('R'.$currRow, "Nilai VO yang Tidak termasuk dalam Amandemen")->getStyle('R'.$currRow.':U'.($currRow + 1))->applyFromArray($styleColBlue)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('N'.++$currRow.':O'.$currRow)->setCellValue('N'.$currRow, "Y")->getStyle('N'.$currRow.':O'.$currRow)->applyFromArray($styleColBlue)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('P'.$currRow.':Q'.$currRow)->setCellValue('P'.$currRow, "N")->getStyle('P'.$currRow.':Q'.$currRow)->applyFromArray($styleColBlue)->getAlignment()->setWrapText(true);
        
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('F'.$currRow.':I'.$currRow)->setCellValue('F'.$currRow, "")->getStyle('F'.$currRow.':I'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('J'.$currRow.':K'.$currRow)->setCellValue('J'.$currRow, "")->getStyle('J'.$currRow.':K'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('L'.$currRow.':M'.$currRow)->setCellValue('L'.$currRow, "")->getStyle('L'.$currRow.':M'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('N'.$currRow.':O'.$currRow)->setCellValue('N'.$currRow, "")->getStyle('N'.$currRow.':O'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('P'.$currRow.':Q'.$currRow)->setCellValue('P'.$currRow, "")->getStyle('P'.$currRow.':Q'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('R'.$currRow.':U'.$currRow)->setCellValue('R'.$currRow, "")->getStyle('R'.$currRow.':U'.$currRow)->applyFromArray($styleBrContent);
        
        $sheet->mergeCells('A'.++$currRow.':Q'.$currRow)->setCellValue('A'.$currRow, "TOTAL")->getStyle('A'.$currRow.':Q'.$currRow)->applyFromArray($styleColGreen);
        $sheet->mergeCells('R'.$currRow.':U'.$currRow)->setCellValue('R'.$currRow, "")->getStyle('R'.$currRow.':U'.$currRow++)->applyFromArray($styleBrContent);
        // END B VARIATION ORDER
        
        // C PLANNING BAYAR PROGRESS KONTRAK
        $sheet->setCellValue('A'.++$currRow, "C. Planning Bayar")->getStyle('A'.$currRow)->applyFromArray($styleHeading3);
        $sheet->setCellValue('A'.++$currRow, "I. Planning Bayar Progress Kontrak")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $sheet->mergeCells('A'.++$currRow.':A'.($currRow + 2))->setCellValue('A'.$currRow, "No")->getStyle('A'.$currRow.':A'.($currRow + 2))->applyFromArray($styleColBlue);
        $sheet->mergeCells('B'.$currRow.':E'.($currRow + 2))->setCellValue('B'.$currRow, "Tahapan Termin")->getStyle('B'.$currRow.':E'.($currRow + 2))->applyFromArray($styleColBlue);
        $sheet->mergeCells('F'.$currRow.':F'.($currRow + 2))->setCellValue('F'.$currRow, "")->getStyle('F'.$currRow.':F'.($currRow + 2))->applyFromArray($styleColBlue);
        $sheet->mergeCells('G'.$currRow.':G'.($currRow + 2))->setCellValue('G'.$currRow, "Bobot")->getStyle('G'.$currRow.':G'.($currRow + 2))->applyFromArray($styleColBlue);
        $rowPayTitle = $currRow;

        $sheet->setCellValue('A'.$currRow+=3, "a. Tahapan Termin Pembayaran")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $rowPayData = ($currRow + 1);
        foreach ($payments as $payment) {
            $sheet->setCellValue('A'.++$currRow, $payment['no'])->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
            $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, $payment['name'])->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
            $sheet->setCellValue('F'.$currRow, $payment['desc'])->getStyle('F'.$currRow)->applyFromArray($styleBrContent);
            $sheet->setCellValue('G'.$currRow, $payment['weight'])->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        }
        
        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "SUB TOTAL (Ia)")->getStyle('A'.$currRow)->applyFromArray($styleColBlue);
        $rowPayTotal1 = $currRow;

        $sheet->setCellValue('A'.++$currRow, "b. Perincian Pengurangan Pekerjaan")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $rowPayDataPengurangan = ++$currRow;
        $sheet->setCellValue('A'.$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Uang Muka *")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Retensi *")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Progres Sebelumnya *")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Denda Keterlambatan")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Denda Kelalaian")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Biaya Lain - lain")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "SUB TOTAL (Ia)")->getStyle('A'.$currRow.':G'.$currRow)->applyFromArray($styleColBlue);
        $rowPayTotal2 = $currRow;
        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "Total I (Ia - Ib)")->getStyle('A'.$currRow.':G'.$currRow)->applyFromArray($styleColGreen);
        $rowPayTotalAll = $currRow;
        
        $z = 0;
        for($x = 0; $x < count($detailPayments); $x++){
            $tempRow = $rowPayTitle;
            $sheet->mergeCells($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, "Payment ".$colPay[$x][0])->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->mergeCells($colPay[$x][1].++$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, $detailPayments[$x]->date)->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][1].++$tempRow, "Actual Progress")->getStyle($colPay[$x][1].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][2].$tempRow, "Nilai (Rp)")->getStyle($colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            
            $tempRow2 = $rowPayData;
            $value              = 0;
            $valuePengurangan   = 0;
            for($y = 0; $y < count($payments); $y++){
                if($z == $x && $z == $y){
                    $value = ((float)$payments[$z]['weight'] / 100) * (int)$detailContract->project_kontrak;
                    $sheet->setCellValue($colPay[$x][1].$tempRow2, $detailPayments[$z]->cop_progress."%")->getStyle($colPay[$x][1]. $tempRow2)->applyFromArray($styleBrTcContent);
                    $sheet->setCellValue($colPay[$x][2].$tempRow2, $value)->getStyle($colPay[$x][2].$tempRow2++)->applyFromArray($styleBrContent);
                    $z++;
                }else{
                    $sheet->setCellValue($colPay[$x][1].$tempRow2, "")->getStyle($colPay[$x][1]. $tempRow2)->applyFromArray($styleBrTcContent);
                    $sheet->setCellValue($colPay[$x][2].$tempRow2, "")->getStyle($colPay[$x][2].$tempRow2++)->applyFromArray($styleBrContent);
                }
            }

            $tempRow3 = $rowPayDataPengurangan;
            for($y = 0; $y < 6; $y++){
                $sheet->setCellValue($colPay[$x][1].$tempRow3, "")->getStyle($colPay[$x][1]. $tempRow3)->applyFromArray($styleBrTcContent);
                $sheet->setCellValue($colPay[$x][2].$tempRow3, "")->getStyle($colPay[$x][2].$tempRow3++)->applyFromArray($styleBrContent);
            }

            $sheet->mergeCells($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->setCellValue($colPay[$x][1].$rowPayTotal1, $value)->getStyle($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->setCellValue($colPay[$x][1].$rowPayTotal2, $valuePengurangan)->getStyle($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->setCellValue($colPay[$x][1].$rowPayTotalAll, ($value - $valuePengurangan))->getStyle($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->applyFromArray($styleBBrTcContent);
        }
        // END C PLANNING BAYAR PROGRESS KONTRAK

        // C PLANNING BAYAR PROGRESS VO
        $sheet->setCellValue('A'.$currRow+=2, "I. Planning Bayar Progress VO")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $sheet->mergeCells('A'.++$currRow.':A'.($currRow + 2))->setCellValue('A'.$currRow, "No")->getStyle('A'.$currRow.':A'.($currRow + 2))->applyFromArray($styleColBlue);
        $sheet->mergeCells('B'.$currRow.':F'.($currRow + 2))->setCellValue('B'.$currRow, "Penilaian Variation Order")->getStyle('B'.$currRow.':F'.($currRow + 2))->applyFromArray($styleColBlue);
        $sheet->mergeCells('G'.$currRow.':G'.($currRow + 2))->setCellValue('G'.$currRow, "Bobot")->getStyle('G'.$currRow.':G'.($currRow + 2))->applyFromArray($styleColBlue);
        $rowPayTitle = $currRow;
        
        $sheet->setCellValue('A'.$currRow+=3, "a. Tahapan Termin VO")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $rowPayData = ++$currRow;
        $sheet->setCellValue('A'.$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "VO / SI no. SI/24-P/9972/XII/21")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "30%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);

        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "SUB TOTAL (IIa)")->getStyle('A'.$currRow.':G'.$currRow)->applyFromArray($styleColBlue);
        $rowPayTotal1 = $currRow;

        $sheet->setCellValue('A'.++$currRow, "b. Perincian Pengurangan Pekerjaan")->getStyle('A'.$currRow)->applyFromArray($styleBContent);
        $rowPayDataPengurangan = ++$currRow;
        $sheet->setCellValue('A'.$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Sebelumnya *")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':F'.$currRow)->setCellValue('B'.$currRow, "Pemotongan Retensi *")->getStyle('B'.$currRow.':F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "...%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        
        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "SUB TOTAL (IIa)")->getStyle('A'.$currRow.':G'.$currRow)->applyFromArray($styleColBlue);
        $rowPayTotal2 = $currRow;
        $sheet->mergeCells('A'.++$currRow.':G'.$currRow)->setCellValue('A'.$currRow, "Total II (IIa - IIb)")->getStyle('A'.$currRow.':G'.$currRow)->applyFromArray($styleColGreen);
        $rowPayTotalAll = $currRow;
        for($x = 0; $x < 8; $x++){
            $tempRow = $rowPayTitle;
            $sheet->mergeCells($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, "Payment ".$colPay[$x][0])->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->mergeCells($colPay[$x][1].++$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, "Oct-21 ".$colPay[$x][0])->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][1].++$tempRow, "Actual Progress")->getStyle($colPay[$x][1].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][2].$tempRow, "Nilai (Rp)")->getStyle($colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            
            $tempRow2 = $rowPayData;
            for($y = 0; $y < 1; $y++){
                $sheet->setCellValue($colPay[$x][1].$tempRow2, "")->getStyle($colPay[$x][1]. $tempRow2)->applyFromArray($styleBrTcContent);
                $sheet->setCellValue($colPay[$x][2].$tempRow2, "")->getStyle($colPay[$x][2].$tempRow2++)->applyFromArray($styleBrContent);
            }
            
            $tempRow3 = $rowPayDataPengurangan;
            
            for($y = 0; $y < 2; $y++){
                $sheet->setCellValue($colPay[$x][1].$tempRow3, "")->getStyle($colPay[$x][1]. $tempRow3)->applyFromArray($styleBrTcContent);
                $sheet->setCellValue($colPay[$x][2].$tempRow3, "")->getStyle($colPay[$x][2].$tempRow3++)->applyFromArray($styleBrContent);
            }

            $sheet->mergeCells($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->setCellValue($colPay[$x][1].$rowPayTotal1, 500000)->getStyle($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->setCellValue($colPay[$x][1].$rowPayTotal2, 500000)->getStyle($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->setCellValue($colPay[$x][1].$rowPayTotalAll, 500000)->getStyle($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->applyFromArray($styleBBrTcContent);
        }
        // END C PLANNING BAYAR PROGRESS VO
        $sheet->mergeCells('A'.($currRow+=2).':E'.$currRow)->setCellValue('A'.$currRow, "Jumlah Nilai Certificate of Payment yang disepakati")->getStyle('A'.$currRow.':E'.$currRow)->applyFromArray($styleContent);
        $sheet->setCellValue('F'.$currRow, ":")->getStyle('F'.$currRow)->applyFromArray($styleContent);
        $sheet->mergeCells('G'.$currRow.':H'.$currRow)->setCellValue('G'.$currRow, "Rp.".$detailContract->project_kontrak)->getStyle('G'.$currRow.':H'.$currRow)->applyFromArray($styleFillGrey);
        $sheet->setCellValue('E'.++$currRow, "Terbilang")->getStyle('E'.$currRow)->applyFromArray($styleIContent);
        $sheet->setCellValue('F'.$currRow, ":")->getStyle('F'.$currRow)->applyFromArray($styleContent);
        $sheet->mergeCells('G'.$currRow.':H'.$currRow)->setCellValue('G'.$currRow, "Tiga Puluh Lima Juta Rupiah")->getStyle('G'.$currRow.':H'.$currRow)->applyFromArray($styleIContent);
        
        $sheet->mergeCells('A'.($currRow+=2).':D'.$currRow)->setCellValue('A'.$currRow, "Jumlah yang sudah terbayar:")->getStyle('A'.$currRow.':D'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('E'.$currRow, "Rp. 13000000")->getStyle('E'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('F'.$currRow, "100%")->getStyle('F'.$currRow)->applyFromArray($styleTrContent);
        $sheet->mergeCells('A'.++$currRow.':D'.$currRow)->setCellValue('A'.$currRow, "Sisa Pembayaran:")->getStyle('A'.$currRow.':D'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('E'.$currRow, "Rp. 13000000")->getStyle('E'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('F'.$currRow, "100%")->getStyle('F'.$currRow)->applyFromArray($styleTrContent);
        
        $rowSign = $currRow+= 2;
        $sheet->mergeCells('A'.$currRow.':D'.$currRow)->setCellValue('A'.$currRow, "Jumlah SI yang sudah terbayar:")->getStyle('A'.$currRow.':D'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('E'.$currRow, "Rp. 13000000")->getStyle('E'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('F'.$currRow, "100%")->getStyle('F'.$currRow)->applyFromArray($styleTrContent);
        $sheet->mergeCells('A'.++$currRow.':D'.$currRow)->setCellValue('A'.$currRow, "Sisa SI Pembayaran:")->getStyle('A'.$currRow.':D'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('E'.$currRow, "Rp. 13000000")->getStyle('E'.$currRow)->applyFromArray($styleTrContent);
        $sheet->setCellValue('F'.$currRow, "100%")->getStyle('F'.$currRow)->applyFromArray($styleTrContent);
        
        $sheet->setCellValue('I'.$rowSign, "Pemberi Tugas,")->getStyle('I'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('N'.$rowSign, "Kontraktor")->getStyle('N'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('I'.++$rowSign, "PT United Tractors Tbk,")->getStyle('I'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('N'.$rowSign, "CV ??")->getStyle('N'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('I'.++$rowSign, "Disetujui oleh,")->getStyle('I'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('N'.$rowSign, "Disetujui oleh,")->getStyle('N'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('I'.($rowSign+=7), "Bagus Setiawan")->getStyle('I'.$rowSign)->applyFromArray($styleBUContent);
        $sheet->setCellValue('N'.$rowSign, "Bambang ??")->getStyle('N'.$rowSign)->applyFromArray($styleBUContent);
        $sheet->setCellValue('I'.++$rowSign, "GAD Head")->getStyle('I'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('N'.$rowSign, "Direktur")->getStyle('N'.$rowSign)->applyFromArray($styleContent);
        $sheet->setCellValue('I'.($rowSign+=2), "Verified")->getStyle('I'.$rowSign)->applyFromArray($styleContentSmall);
        $sheet->setCellValue('I'.($rowSign+=3), "PIC")->getStyle('I'.$rowSign)->applyFromArray($styleBTcContent);
        $sheet->setCellValue('J'.$rowSign, "BC")->getStyle('I'.$rowSign)->applyFromArray($styleBTcContent);
        $sheet->setCellValue('K'.$rowSign, "PM")->getStyle('I'.$rowSign)->applyFromArray($styleBTcContent);
        
        $sheet->setCellValue('A'.($currRow+=11), "Notes :")->getStyle('A'.$currRow)->applyFromArray($styleIContentRed);
        $sheet->setCellValue('A'.++$currRow, "*) Jika metode pembayaran dengan monthly progress termin")->getStyle('A'.$currRow)->applyFromArray($styleIContentRed);
        $sheet->setCellValue('A'.++$currRow, "**)   Jika kontrak kerjasama dilakukan oleh Cabang / Site")->getStyle('A'.$currRow)->applyFromArray($styleIContentRed);
        $sheet->setCellValue('A'.++$currRow, "Jika kontrak kerjasama dilakukan oleh Cabang / Site")->getStyle('A'.$currRow)->applyFromArray($styleIContentRed);
        // FOOTER

        // END FOOTER

        $path = 'uploads/project/cop/tes.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return site_url($path);
    }
    public function queryGetDetailContract($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mp.project_name , mp.project_no , ma.area_name , mp.project_kontrak ,
                ma.area_branch_address , mv.vendor_name , DATE_FORMAT(tc.submitted_date, '%d-%b-%y') as submitted_date ,
                tc.contract_name_file, tc.contract_dp , tc.contract_progress , tc.contract_retensi 
            FROM transaction_contract tc , master_project mp, master_area ma, master_vendor mv  
            WHERE 
                tc.project_no = '".$projectNo."' 
                AND tc.project_no = mp.project_no 
                AND mp.project_area = ma.area_code 
                AND mp.vendor_no = mv.vendor_no 
        ")->row();
    }

    public function queryGetPayment($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT tc.cop_progress , DATE_FORMAT(tc.cop_date, '%b-%y') as date , tc.cop_type 
            FROM transaction_cop tc 
            WHERE tc.project_no = '".$projectNo."' AND tc.cop_stat_approve != 2
            ORDER BY DATE(tc.cop_date) ASC
        ")->result();
    }
}
