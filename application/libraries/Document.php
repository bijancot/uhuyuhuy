<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class Document {
    function __construct(){
        $this->_ci = &get_instance();
        $this->_ci->load->library(['pdfgenerator', 'zip']);
        $this->_ci->load->model('TransactionApproval');;
    }

    public function genPdf($docType, $param){
        $DB2 = $this->_ci->load->database('gaSys2', true);

        $project    = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->row_array();
        $user       = $DB2->get_where('master_user', ['user_no' => $project['project_pic']])->row_array();
        if($docType == 'LPK'){
            $file_pdf = "LPK" . " - " . str_replace('/', '', $param['projectNo']);
            $path_pdf = 'uploads/project/lpk/' . $file_pdf . '.pdf';
            $html = $this->_ci->load->view("pdf_template/form_LPK", ['user' => $user, 'project' => $project], true);
        }else if($docType == 'BAPP'){
            $area = $DB2->get_where('master_area', ['area_code' => $user['user_area']])->row_array();
            $param['picName']   = $user['user_name'];
            $param['area']      = $area['area_name'];

            $day                        = date_format(date_create($param['tglPembuatan']), 'j');
            $month                      = $this->_ci->datefunction->getMonth()[date_format(date_create($param['tglPembuatan']), 'n')];
            $year                       = date_format(date_create($param['tglPembuatan']), 'Y');
            $param['date']              = $day." ".$month." ".$year;
            $param['dateDay']           = $day;
            $param['dateMonth']         = date_format(date_create($param['tglPembuatan']), 'n');
            $param['dateDayLatin']      = $this->_ci->datefunction->getDateLatin()[$day];
            $param['dateMonthLatin']    = $month;
            
            $day                        = date_format(date_create($param['mulaiPengerjaan']), 'j');
            $month                      = $this->_ci->datefunction->getMonth()[date_format(date_create($param['mulaiPengerjaan']), 'n')];
            $year                       = date_format(date_create($param['mulaiPengerjaan']), 'Y');
            $param['mulaiPengerjaan']   = $day." ".$month." ".$year;
            
            $day                        = date_format(date_create($param['akhirPengerjaan']), 'j');
            $month                      = $this->_ci->datefunction->getMonth()[date_format(date_create($param['akhirPengerjaan']), 'n')];
            $year                       = date_format(date_create($param['akhirPengerjaan']), 'Y');
            $param['akhirPengerjaan']   = $day." ".$month." ".$year;

            $file_pdf = "BAPP" . ' - ' . str_replace('/', '', $param['docNo']) . " - " . str_replace('/', '', $param['projectNo']);
            $path_pdf = 'uploads/project/bapp/' . $file_pdf . '.pdf';
            $html = $this->_ci->load->view("pdf_template/form_BAPP", $param, true);
        }else if($docType == 'BAST'){
            $day                        = date_format(date_create($param['tglPembuatan']), 'j');
            $month                      = $this->_ci->datefunction->getMonth()[date_format(date_create($param['tglPembuatan']), 'n')];
            $year                       = date_format(date_create($param['tglPembuatan']), 'Y');
            $param['date']              = $day." ".$month." ".$year;
            $param['dateDay']           = $day;
            $param['dateMonth']         = date_format(date_create($param['tglPembuatan']), 'n');
            $param['dateDayLatin']      = $this->_ci->datefunction->getDateLatin()[$day];
            $param['dateMonthLatin']    = $month;

            $file_pdf = "BAST" . ' - ' . str_replace('/', '', $param['docNo']) . " - " . str_replace('/', '', $param['projectNo']);
            $path_pdf = 'uploads/project/bast/' . $file_pdf . '.pdf';
            $html = $this->_ci->load->view("pdf_template/form_BAST", $param, true);
        } 

        $paper = 'A4';
        $orientation = 'portrait';
        $resPdf = $this->_ci->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
        file_put_contents($path_pdf, $resPdf);
        return ['link' => site_url($path_pdf), 'fileName' => $file_pdf];
    }

    public function signDoc($docType, $approvalId){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        
        $approvalTrans      = $DB2->get_where('transaction_approval', ['approval_id' => $approvalId])->row();
        $approvalDetails    = $DB2->get_where('transaction_approval_detail', ['approval_id' => $approvalId])->result();
        $project            = $DB2->get_where('master_project', ['project_no' => $approvalTrans->project_no])->row_array();
        $approval           = [];

        foreach ($approvalDetails as $approvalDetail) {
            if($approvalDetail->approval_detail_role == 'Department Head' || $approvalDetail->approval_detail_role == 'Division Head' || $approvalDetail->approval_detail_role == 'Directors'){
                if($approvalDetail->approval_detail_userid != null){
                    $user = $this->_ci->TransactionApproval->getUserById($approvalDetail->approval_detail_userid);
                    $approval[$approvalDetail->approval_detail_role]['name']    = $user->NAMA_USERS;
                    $approval[$approvalDetail->approval_detail_role]['sign']    = $user->PATH_TTD;
                    $approval[$approvalDetail->approval_detail_role]['status']  = $approvalDetail->approval_detail_stat_approve;
                }
            }else if($approvalDetail->approval_detail_role == 'Vendor'){
                if($approvalDetail->approval_detail_userid != null){
                    $user = $DB2->get_where('master_vendor', ['vendor_no' => $approvalDetail->approval_detail_userid])->row();
                    $approval['Vendor']['name']     = $user->vendor_name;
                    $approval['Vendor']['status']   = $approvalDetail->approval_detail_stat_approve;
                }
            }else{
                if($approvalDetail->approval_detail_userid != null){
                    $user = $DB2->get_where('master_user', ['user_no' => $approvalDetail->approval_detail_userid])->row();
                    $approval[$approvalDetail->approval_detail_role]['name'] = $user->user_name;
                    $approval[$approvalDetail->approval_detail_role]['sign'] = $user->path_ttd;
                    $approval[$approvalDetail->approval_detail_role]['status']  = $approvalDetail->approval_detail_stat_approve;
                }
            }
        }

        
        if($docType == 'Contract' || $docType == 'Contract Cabang'){
            $pdf = new Fpdi();
            $pdf->AddPage();
            $user     = $DB2->get_where('master_user', ['user_no' => $project['project_pic']])->row();
            $file_pdf = "[SIGN] LPK" . " - " . str_replace('/', '', $project['project_no']);
            $path = 'uploads/project/lpk/' . $file_pdf . '.pdf';

            $fileContent = file_get_contents('https://ut-staging.bgskr-project.my.id/uploads/project/lpk/LPK - '.str_replace('/', '', $project['project_no']).".pdf",'rb');
            // $pdf->setSourceFile('uploads/project/lpk/LPK - '.str_replace('/', '', $project['project_no']).".pdf");
            $pdf->setSourceFile(StreamReader::createByString($fileContent));

            $pdf->Image(str_replace(site_url(), '', $user->path_ttd), 62, 78, 10, 10);
            if(!empty($approval['SPV']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['SPV']['sign']), 62, 86, 10, 10);
            if(!empty($approval['Section Head']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Section Head']['sign']), 62, 95, 10, 10);
            if(!empty($approval['Department Head']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Department Head']['sign']), 62, 129, 10, 10);
            if(!empty($approval['Division Head']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Division Head']['sign']), 62, 197, 10, 10);
            if(!empty($approval['Directors']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Directors']['sign']), 62, 231, 10, 10);

            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 220);
            $pdf->Output($path, 'F');
            
            $name = $file_pdf.".pdf";
            $data = $path;
            $this->_ci->zip->add_data($name, $data);
            
            $name = str_replace(site_url(), '', $approvalTrans->approval_path);
            $name = str_replace('uploads/project/approvalContract/', '', $name);
            $data = str_replace(site_url(), '', $approvalTrans->approval_path);
            $this->_ci->zip->add_data($name, $data);
            
            $resPath = "uploads/project/approvalContract/[SIGN] Contract - ".str_replace('/', '', $project['project_no']).".zip";
            $this->_ci->zip->archive($resPath);
            
        }else if($docType == 'BAPP'){
            $pdf = new Fpdi();
            $pdf->AddPage();
            $docNo      = str_replace(site_url(), '' ,$approvalTrans->approval_path);
            $docNo      = explode('-', $docNo)[1];
            $file_pdf   = "[SIGN] BAPP" . ' - ' . str_replace('/', '', $docNo) . " - " . str_replace('/', '', $project['project_no']);
            $path       = 'uploads/project/bapp/' . $file_pdf . '.pdf';
            $pdf->setSourceFile(str_replace(site_url(), '' ,$approvalTrans->approval_path));

            if(!empty($approval['PICP']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['PICP']['sign']), 62, 86, 10, 10);
            if(!empty($approval['Section Head']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Section Head']['sign']), 55, 182, 20, 20);
            if(!empty($approval['Department Head']['status']) == '1') $pdf->Image(str_replace(site_url(), '', $approval['Department Head']['sign']), 153, 182, 20, 20);
            
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 220);
            $pdf->Output($path, 'F');
        }else if($docType == 'BAST'){
            $pdf = new Fpdi();
            $pdf->AddPage();
        }

        return ['approval_signed_path' => $resPath];
    }
    
    public function genFormContract($projectNo){
        $contract = $this->queryGetFormContract($projectNo);
        if(empty($contract->contract_no)){
            echo json_encode(['status' => false, 'message' => 'Form contract tidak terdaftar!']);
            return;
        }
        $tenderDoc = $this->queryGetTenderDocument($contract->project_no);
        $otherDoc = $this->queryGetOtherDocument($contract->project_no);
        $project = $this->queryGetProject($contract->project_no);
        $area = $this->queryGetArea($project->project_area);
        $vendor = $this->queryGetVendor($contract->vendor_no);
        // === STYLING SHEETS ===
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);


        $styleHeading1['font']['bold'] = true;
        $styleHeading1['font']['size'] = 20;
        
        $styleHeading2['font']['bold'] = true;
        $styleHeading2['font']['size'] = 14;
        
        $styleHeading3['font']['bold'] = true;
        $styleHeading3['font']['size'] = 12;
        
        $styleTitle['font']['bold']                         = true;
        $styleTitle['font']['size']                         = 11;
        $styleTitle['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleTitle['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $styleTitle2['font']['bold']                         = true;
        $styleTitle2['font']['size']                         = 11;
        $styleTitle2['fill']['fillType']                     = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleTitle2['fill']['color']['argb']                = 'c2c2c2';
        $styleTitle2['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle2['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        
        $styleContent['font']['size']                         = 11;
        $styleContent['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleContentCenter['font']['size']                         = 11;
        $styleContentCenter['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContentCenter['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleContentCenter['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

        $styleBorder['borders']['outline']['borderStyle']       = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBorderTop['borders']['top']['borderStyle']        = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContentWithoutBorder['font']['size']  = 11;

        $styleContentWithoutBorderBold['font']['size']  = 11;
        $styleContentWithoutBorderBold['font']['bold']  = false;

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(7);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(70);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(25);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(25);
        $sheet->getColumnDimension('Q')->setWidth(40);
        
        $sheet->getRowDimension('8')->setRowHeight(30);
        // HEADER
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath("assets/img/debitnote/header.png"); /* put your path and image here */
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
        $sheet->setCellValue('A6', 'FORM REQUEST CONTRACT')->getStyle('A6')->applyFromArray($styleHeading1);
        $sheet->mergeCells('B8:D8');
        $sheet->setCellValue('B8', 'Data Request Pembuatan Kontrak')->getStyle('B8:D8')->applyFromArray($styleTitle);

        // 1. INFORMASI PROYEK
        $sheet->setCellValue('B9', '1')->getStyle('B9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('C9', 'INFORMASI PROYEK')->getStyle('C9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('D9', ' ')->getStyle('D9')->applyFromArray($styleTitle2);

        $sheet->setCellValue('B10', '1.1')->getStyle('B10')->applyFromArray($styleContent);
        $sheet->setCellValue('B11', '1.2')->getStyle('B11')->applyFromArray($styleContent);
        $sheet->setCellValue('B12', '1.3')->getStyle('B12')->applyFromArray($styleContent);
        $sheet->setCellValue('B13', '1.4')->getStyle('B13')->applyFromArray($styleContent);
        
        $sheet->getStyle('B9:B13')->getAlignment()->setVertical('center');
        $sheet->getStyle('B9:B13')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('C10', 'Nama Pekerjaan')->getStyle('C10')->applyFromArray($styleContent);
        $sheet->setCellValue('C11', 'Nilai Pekerjaan')->getStyle('C11')->applyFromArray($styleContent);
        $sheet->setCellValue('C12', 'Alamat detail UT Cabang/Site')->getStyle('C12')->applyFromArray($styleContent);
        $sheet->setCellValue('C13', 'Alamat detail Proyek')->getStyle('C13')->applyFromArray($styleContent);

        $sheet->setCellValue('D10', ''.$project->project_name)->getStyle('D10')->applyFromArray($styleContent);
        $sheet->setCellValue('D11', ''.$project->project_kontrak)->getStyle('D11')->applyFromArray($styleContent);
        $sheet->getStyle('D11')->getAlignment()->setHorizontal('left');
        $sheet->setCellValue('D12', ''.$area->area_branch_address)->getStyle('D12')->applyFromArray($styleContent);
        $sheet->setCellValue('D13', '-')->getStyle('D13')->applyFromArray($styleContent);
        
        $sheet->getStyle('D11')->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('D12')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D13')->getAlignment()->setWrapText(true);

        // 2. INFORMASI CABANG
        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $richText->createText('INFORMASI CABANG ');
        $payable = $richText->createTextRun('(hanya diisi jika kontrak cabang)');
        $payable->getFont()->setBold(true);
        $payable->getFont()->setItalic(true);
        $sheet->getCell('C14')->setValue($richText)->getStyle('C14')->applyFromArray($styleTitle2);

        $sheet->setCellValue('B14', '2')->getStyle('B14')->applyFromArray($styleTitle2);
        $sheet->setCellValue('D14', ' ')->getStyle('D14')->applyFromArray($styleTitle2);
        $sheet->mergeCells('C14:D14');

        $sheet->setCellValue('B15', '2.1')->getStyle('B15')->applyFromArray($styleContent);
        $sheet->setCellValue('B16', '2.2')->getStyle('B16')->applyFromArray($styleContent);
        $sheet->setCellValue('B17', '2.3')->getStyle('B17')->applyFromArray($styleContent);
        $sheet->setCellValue('B18', '2.4')->getStyle('B18')->applyFromArray($styleContent);
        
        $sheet->getStyle('B14:B18')->getAlignment()->setVertical('center');
        $sheet->getStyle('B14:B18')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('C15', 'Nomor SPK')->getStyle('C15')->applyFromArray($styleContent);
        $sheet->setCellValue('C16', 'Nama')->getStyle('C16')->applyFromArray($styleContent);
        $sheet->setCellValue('C17', 'Jabatan')->getStyle('C17')->applyFromArray($styleContent);
        $sheet->setCellValue('C18', 'No. Surat Kuasa')->getStyle('C18')->applyFromArray($styleContent);

        $sheet->setCellValue('D15', ''.$contract->contract_branch_spk)->getStyle('D15')->applyFromArray($styleContent);
        $sheet->setCellValue('D16', ''.$contract->contract_branch_name)->getStyle('D16')->applyFromArray($styleContent);
        $sheet->setCellValue('D17', ''.$contract->contract_branch_position)->getStyle('D17')->applyFromArray($styleContent);
        $sheet->setCellValue('D18', ''.$contract->contract_branch_proc)->getStyle('D18')->applyFromArray($styleContent);
        $sheet->getStyle('D18')->getAlignment()->setHorizontal('left');

        // 3. INFORMASI VENDOR
        $sheet->setCellValue('B19', '3')->getStyle('B19')->applyFromArray($styleTitle2);
        $sheet->setCellValue('C19', 'INFORMASI VENDOR')->getStyle('C19')->applyFromArray($styleTitle2);
        $sheet->setCellValue('D19', ' ')->getStyle('D19')->applyFromArray($styleTitle2);

        $sheet->setCellValue('B20', '3.1')->getStyle('B20')->applyFromArray($styleContent);
        $sheet->setCellValue('B21', '3.2')->getStyle('B21')->applyFromArray($styleContent);
        $sheet->setCellValue('B22', '3.3')->getStyle('B22')->applyFromArray($styleContent);
        $sheet->setCellValue('B23', '3.4')->getStyle('B23')->applyFromArray($styleContent);
        
        $sheet->getStyle('B19:B23')->getAlignment()->setVertical('center');
        $sheet->getStyle('B19:B23')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('C20', 'Nama Kontraktor/Konsultan')->getStyle('C20')->applyFromArray($styleContent);
        $sheet->setCellValue('C21', 'Alamat Kantor Vendor')->getStyle('C21')->applyFromArray($styleContent);
        $sheet->setCellValue('C22', 'Nama Vendor yang TTD Kontrak')->getStyle('C22')->applyFromArray($styleContent);
        $sheet->setCellValue('C23', 'Jabatan Vendor yang TTD kontrak')->getStyle('C23')->applyFromArray($styleContent);

        $sheet->setCellValue('D20', ''.$vendor->vendor_name)->getStyle('D20')->applyFromArray($styleContent);
        $sheet->setCellValue('D21', ''.$vendor->vendor_address)->getStyle('D21')->applyFromArray($styleContent);
        $sheet->getStyle('D21')->getAlignment()->setWrapText(true);
        $sheet->setCellValue('D22', ''.$vendor->vendor_contract_sign)->getStyle('D22')->applyFromArray($styleContent);
        $sheet->setCellValue('D23', ''.$vendor->vendor_cs_position)->getStyle('D23')->applyFromArray($styleContent);

        // 4. PEMBAYARAN
        $sheet->setCellValue('B24', '4')->getStyle('B24')->applyFromArray($styleTitle2);
        $sheet->setCellValue('C24', 'PEMBAYARAN')->getStyle('C24')->applyFromArray($styleTitle2);
        $sheet->setCellValue('D24', ' ')->getStyle('D24')->applyFromArray($styleTitle2);

        $sheet->setCellValue('C25', 'Termin Pembayaran')->getStyle('C25')->applyFromArray($styleTitle);
        $sheet->getStyle('C25')->getAlignment()->setHorizontal('left');
        $sheet->setCellValue('D25', ' ')->getStyle('D25')->applyFromArray($styleContent);
        $sheet->setCellValue('C26', 'DP by '.$contract->contract_dp.'%')->getStyle('C26')->applyFromArray($styleContent);
        $dpAmmount = round($contract->contract_dp/100*$project->project_kontrak);
        $sheet->setCellValue('D26', ''.$dpAmmount)->getStyle('D26')->applyFromArray($styleContent);
        
        $pos = 26;
        $i = 1;$value = explode(";", $contract->contract_progress);
        foreach($value as $item){
            $pos++;
            $percent = explode("_", $item);
            
            $sheet->setCellValue('C'.$pos, 'Progress '.$percent[0].'% by '.$percent[1].'%')->getStyle('C'.$pos)->applyFromArray($styleContent);
            $ammount = round($percent[1]/100*$project->project_kontrak);
            $sheet->setCellValue('D'.$pos, ''.$ammount)->getStyle('D'.$pos)->applyFromArray($styleContent);
            
            $i++;
        }
        $pos++;
        $sheet->setCellValue('B25', '4.1')->getStyle('B25:B'.$pos)->applyFromArray($styleContent);
        $sheet->mergeCells('B25:B'.$pos);

        $sheet->setCellValue('C'.$pos, 'Retensi by '.$contract->contract_retensi.'%')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $retency = round($contract->contract_retensi/100*$project->project_kontrak);
        $sheet->setCellValue('D'.$pos, ''.$retency)->getStyle('D'.$pos)->applyFromArray($styleContent);
        
        $sheet->getStyle('D26:D'.$pos)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('D26:D'.$pos)->getAlignment()->setHorizontal('left');
        
        //4.2 Durasi Pekerjaan
        $pos++;$nsection = $pos+3;           
        $sheet->setCellValue('B'.$pos, '4.2')->getStyle('B'.$pos.':'.'B'.$nsection)->applyFromArray($styleContent);
        $sheet->mergeCells('B'.$pos.':'.'B'.$nsection);     
        $sheet->getStyle('B25:B'.$nsection)->getAlignment()->setVertical('top');
        $sheet->getStyle('B24')->getAlignment()->setVertical('center');
        $sheet->getStyle('B24:B'.$nsection)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('C'.$pos, 'Durasi Pekerjaan')->getStyle('C'.$pos)->applyFromArray($styleTitle);
        $sheet->getStyle('C'.$pos)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue('D'.$pos, ' ')->getStyle('D'.$pos)->applyFromArray($styleContent);
        $pos++;
        $date=date_create($contract->contract_start);
        $tanggalMulai = date_format($date,"d-M-Y");
        $sheet->setCellValue('C'.$pos, 'Tanggal Mulai')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $tanggalMulai)->getStyle('D'.$pos)->applyFromArray($styleContent);
        $pos++;        
        $date=date_create($contract->contract_end);
        $tanggalSelesai = date_format($date,"d-M-Y");
        $sheet->setCellValue('C'.$pos, 'Tanggal Selesai')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $tanggalSelesai)->getStyle('D'.$pos)->applyFromArray($styleContent);
        $pos++;
        $sheet->setCellValue('C'.$pos, 'Durasi Masa Pemeliharaan')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $contract->contract_duration." bulan")->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('left');

        //5. LAMPIRAN
        $docStat = array();
        if($tenderDoc['fsv'] == null){
            $docStat[0] = "x";
        }else{
            $docStat[0] = "v";
        };
        if($tenderDoc['gambar'] == null){
            $docStat[1] = "x";
        }else{
            $docStat[1] = "v";
        }
        if($tenderDoc['bastl'] == null){
            $docStat[2] = "x";
        }else{
            $docStat[2] = "v";
        }
        if($tenderDoc['sCurve'] == null){
            $docStat[3] = "x";
        }else{
            $docStat[3] = "v";
        }
        if($tenderDoc['boq'] == null){
            $docStat[4] = "x";
        }else{
            $docStat[4] = "v";
        }
        $pos++;
        $sheet->setCellValue('B'.$pos, '5')->getStyle('B'.$pos)->applyFromArray($styleTitle2);        
        $sheet->getStyle('B'.$pos)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('C'.$pos, 'LAMPIRAN (diisi dengan (v) jika ada dan dengan (X) jika tidak ada')->getStyle('C'.$pos)->applyFromArray($styleTitle2);
        $sheet->setCellValue('D'.$pos, ' ')->getStyle('D'.$pos)->applyFromArray($styleTitle2);
        $sheet->mergeCells('C'.$pos.':D'.$pos);

        $pos++;$nsection = $pos+6;
        $sheet->getStyle('B'.$pos.':B'.$nsection)->getAlignment()->setHorizontal('center');        
        $sheet->setCellValue('B'.$pos, '5.1')->getStyle('B'.$pos)->applyFromArray($styleContent); 
        $sheet->setCellValue('C'.$pos, 'Gambar teknis/ forcon drawing/ lainnya (sebutkan) *) ')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $docStat[1])->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('C'.$pos)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');

        $pos++;   
        $sheet->setCellValue('B'.$pos, '5.2')->getStyle('B'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('C'.$pos, 'BQ')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $docStat[4])->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');
        
        $pos++;   
        $sheet->setCellValue('B'.$pos, '5.3')->getStyle('B'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('C'.$pos, 'S-curve/ timeline')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $docStat[3])->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');
        
        $pos++;   
        $sheet->setCellValue('B'.$pos, '5.4')->getStyle('B'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('C'.$pos, 'Form seleksi vendor')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $docStat[0])->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');
        
        $pos++;   
        $sheet->setCellValue('B'.$pos, '5.5')->getStyle('B'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('C'.$pos, 'Materi aanwidjing / TOR *)')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $docStat[2])->getStyle('D'.$pos)->applyFromArray($styleContent); 
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');   

        if($otherDoc == null){
            $pos++;   
            $sheet->setCellValue('B'.$pos, '5.6')->getStyle('B'.$pos)->applyFromArray($styleContent);
            $sheet->setCellValue('C'.$pos, 'Lain-lain : ')->getStyle('C'.$pos)->applyFromArray($styleContent);
            $sheet->setCellValue('D'.$pos, '')->getStyle('D'.$pos)->applyFromArray($styleContent);
            $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');
        }
        $i = 6;
        foreach($otherDoc as $item){
            $pos++;   
            $sheet->setCellValue('B'.$pos, '5.'.$i)->getStyle('B'.$pos)->applyFromArray($styleContent);
            $sheet->setCellValue('C'.$pos, 'Lain-lain : '.$item->document_tender_name)->getStyle('C'.$pos)->applyFromArray($styleContent);
            $sheet->setCellValue('D'.$pos, 'v')->getStyle('D'.$pos)->applyFromArray($styleContent);
            $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('center');
            
            $i++;
        }

        //6. KORESPONDENSI VENDOR
        $pos++;
        $sheet->setCellValue('B'.$pos, '6')->getStyle('B'.$pos)->applyFromArray($styleTitle2);        
        $sheet->getStyle('B'.$pos)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('C'.$pos, 'KORESPONDENSI VENDOR (untuk kontrak)')->getStyle('C'.$pos)->applyFromArray($styleTitle2);
        $sheet->setCellValue('D'.$pos, ' ')->getStyle('D'.$pos)->applyFromArray($styleTitle2);
        $sheet->mergeCells('C'.$pos.':D'.$pos);

        $pos++;$nsection = $pos+2;
        $sheet->getStyle('B'.$pos.':B'.$nsection)->getAlignment()->setHorizontal('center');        
        $sheet->setCellValue('B'.$pos, '6.1')->getStyle('B'.$pos)->applyFromArray($styleContent); 
        $sheet->setCellValue('C'.$pos, 'Nama')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $contract->vendor_contract_pic)->getStyle('D'.$pos)->applyFromArray($styleContent);

        $pos++;        
        $sheet->setCellValue('B'.$pos, '6.2')->getStyle('B'.$pos)->applyFromArray($styleContent); 
        $sheet->setCellValue('C'.$pos, 'Email')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $contract->vendor_email)->getStyle('D'.$pos)->applyFromArray($styleContent);

        $pos++;        
        $sheet->setCellValue('B'.$pos, '6.3')->getStyle('B'.$pos)->applyFromArray($styleContent); 
        $sheet->setCellValue('C'.$pos, 'No.HP')->getStyle('C'.$pos)->applyFromArray($styleContent);
        $sheet->setCellValue('D'.$pos, $contract->vendor_phone)->getStyle('D'.$pos)->applyFromArray($styleContent);
        $sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal('left');

        $pos++;        
        $sheet->setCellValue('B'.$pos, '*) coret salah satu')->getStyle('B'.$pos)->applyFromArray($styleContentWithoutBorderBold); 
        $sheet->mergeCells('B'.$pos.':C'.$pos);
        $sheet->getStyle('B'.$pos)->getFont()->setItalic(true);
        
        // === WRITE DATA ===        
        $fileName   = 'FRC_'.str_replace('/', '', $contract->project_no).''.date('Ymd');
        $path       = 'uploads/project/formContract/'.$fileName.'.xlsx';
        $writer     = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return ['link' => site_url($path), 'fileName' => $fileName];
    }

    public function genFormTender($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        $tender = $this->queryGetFormTender($projectNo);
        if(empty($tender->tender_no)){
            echo json_encode(['status' => false, 'message' => 'Form tender tidak terdaftar!']);
            return;
        }
        $torDocument = $this->queryGetTorDocument($tender->project_no);
        $pic = $this->queryGetPIC($idFormTender);
        $adminPIC = $DB2->get_where('master_user', ['user_role' => 'Admin PICP'])->row();
        // === STYLING SHEETS ===
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);


        $styleHeading1['font']['bold'] = true;
        $styleHeading1['font']['size'] = 20;
        
        $styleHeading2['font']['bold'] = true;
        $styleHeading2['font']['size'] = 14;
        
        $styleHeading3['font']['bold'] = true;
        $styleHeading3['font']['size'] = 12;
        
        $styleTitle['font']['bold']                         = true;
        $styleTitle['font']['size']                         = 11;
        $styleTitle['font']['color']['argb']                = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleTitle['fill']['fillType']                     = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleTitle['fill']['color']['argb']                = 'FF0070C0';
        $styleTitle['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleTitle['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $styleTitle2['font']['bold']                         = true;
        $styleTitle2['font']['size']                         = 11;
        $styleTitle2['font']['color']['argb']                = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleTitle2['fill']['fillType']                     = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleTitle2['fill']['color']['argb']                = 'FF595959';
        $styleTitle2['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleTitle2['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle2['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        
        $styleContent['font']['size']                         = 11;
        $styleContent['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContent['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        
        $styleContentCenter['font']['size']                         = 11;
        $styleContentCenter['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContentCenter['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleContentCenter['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

        $styleBorder['borders']['outline']['borderStyle']       = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleBorderTop['borders']['top']['borderStyle']        = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContentWithoutBorder['font']['size']  = 11;

        $styleContentWithoutBorderBold['font']['size']  = 11;
        $styleContentWithoutBorderBold['font']['bold']  = true;

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(25);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(25);
        $sheet->getColumnDimension('Q')->setWidth(40);

        // set height row
        $sheet->getRowDimension('6')->setRowHeight(20);
        $sheet->getRowDimension('8')->setRowHeight(20);
        $sheet->getRowDimension('9')->setRowHeight(20);
        $sheet->getRowDimension('10')->setRowHeight(45);

        // set merge
        $sheet->mergeCells('A8:A9')->getStyle('A8:A9')->applyFromArray($styleBorder);
        $sheet->mergeCells('B8:B9')->getStyle('B8:B9')->applyFromArray($styleBorder);
        $sheet->mergeCells('C8:C9')->getStyle('C8:C9')->applyFromArray($styleBorder);
        $sheet->mergeCells('D8:D9')->getStyle('D8:D9')->applyFromArray($styleBorder);
        $sheet->mergeCells('E8:E9')->getStyle('E8:E9')->applyFromArray($styleBorder);
        $sheet->mergeCells('F8:F9')->getStyle('F8:F9')->applyFromArray($styleBorder);
        $sheet->mergeCells('G8:G9')->getStyle('G8:G9')->applyFromArray($styleBorder);
        $sheet->mergeCells('H8:H9')->getStyle('H8:H9')->applyFromArray($styleBorder);
        $sheet->mergeCells('I8:K8')->getStyle('I8:I9')->applyFromArray($styleBorder);
        $sheet->mergeCells('L8:L9')->getStyle('L8:L9')->applyFromArray($styleBorder);
        $sheet->mergeCells('M8:N8')->getStyle('M8:N8')->applyFromArray($styleBorder);
        $sheet->mergeCells('O8:O9')->getStyle('O8:O9')->applyFromArray($styleBorder);
        $sheet->mergeCells('P8:P9')->getStyle('P8:P9')->applyFromArray($styleBorder);
        $sheet->mergeCells('Q8:Q9')->getStyle('Q8:Q9')->applyFromArray($styleBorder);
        $sheet->mergeCells('Q8:Q9')->getStyle('Q8:Q9')->applyFromArray($styleBorder);
        $sheet->mergeCells('A14:H14')->getStyle('A14:H14');
        $sheet->mergeCells('A15:H15')->getStyle('A15:H15');
        $sheet->mergeCells('A16:H16')->getStyle('A16:H16');
        $sheet->mergeCells('A17:H17')->getStyle('A17:H17');
        $sheet->mergeCells('A18:H18')->getStyle('A18:H18');
        $sheet->mergeCells('A19:H19')->getStyle('A19:H19');
        $sheet->mergeCells('A20:H20')->getStyle('A20:H20');
        $sheet->mergeCells('A21:H21')->getStyle('A21:H21');
        $sheet->mergeCells('A22:H22')->getStyle('A22:H22');
        $sheet->mergeCells('A23:H23')->getStyle('A23:H23');
        $sheet->mergeCells('A24:H24')->getStyle('A24:H24');

        // HEADER
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath("assets/img/debitnote/header.png"); /* put your path and image here */
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
        $sheet->setCellValue('A6', 'FORM REQUEST TENDER PROJECT CONSTRUCTION')->getStyle('A6')->applyFromArray($styleHeading1);

        // === WRITE DATA ===
        $sheet->setCellValue('A8', 'Area (HO/Cab/Site)')->getStyle('A8')->applyFromArray($styleTitle)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('B8', 'Tender ID')->getStyle('B8')->applyFromArray($styleTitle);
        $sheet->setCellValue('C8', 'Nama Tender')->getStyle('C8')->applyFromArray($styleTitle);
        $sheet->setCellValue('D8', 'Lokasi')->getStyle('D8')->applyFromArray($styleTitle);
        $sheet->setCellValue('E8', 'Divisi')->getStyle('E8')->applyFromArray($styleTitle);
        $sheet->setCellValue('F8', 'Department')->getStyle('F8')->applyFromArray($styleTitle);
        $sheet->setCellValue('G8', 'Tipe Tender')->getStyle('G8')->applyFromArray($styleTitle);
        $sheet->setCellValue('H8', 'Budget')->getStyle('H8')->applyFromArray($styleTitle);
        $sheet->setCellValue('I8', 'Responsible Person')->getStyle('I8')->applyFromArray($styleTitle);
        $sheet->setCellValue('I9', 'PIC')->getStyle('I9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('J9', 'PIC Email')->getStyle('J9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('K9', 'No HP')->getStyle('K9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('L8', 'Email Approval')->getStyle('L8')->applyFromArray($styleTitle);
        $sheet->setCellValue('M8', 'Target Pengerjaan ')->getStyle('M8')->applyFromArray($styleTitle);
        $sheet->setCellValue('M9', 'Mulai ')->getStyle('M9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('N9', 'Selesai ')->getStyle('N9')->applyFromArray($styleTitle2);
        $sheet->setCellValue('O8', 'Lead Time / Day(s) ')->getStyle('O8')->applyFromArray($styleTitle)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('P8', 'Div. Participant')->getStyle('P8')->applyFromArray($styleTitle);
        $sheet->setCellValue('Q8', 'Deskripsi')->getStyle('Q8')->applyFromArray($styleTitle);

        $sheet->setCellValue('A10', $tender->tender_area_code)->getStyle('A10')->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('B10', $tender->tender_id)->getStyle('B10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('C10', $tender->tender_name)->getStyle('C10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('D10', $tender->tender_location)->getStyle('D10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('E10', $tender->division_name)->getStyle('E10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F10', $tender->department_name)->getStyle('F10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('G10', $tender->tender_type_name)->getStyle('G10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('H10', $tender->tender_budget)->getStyle('H10')->applyFromArray($styleContentCenter)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('I10', $tender->tender_pic)->getStyle('I10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('J10', $tender->tender_pic_email)->getStyle('J10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('K10', $tender->tender_telp)->getStyle('K10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('L10', $tender->tender_approve_email)->getStyle('L10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('M10', date_format(date_create($tender->tender_start), 'd-M-y'))->getStyle('M10')->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('N10', date_format(date_create($tender->tender_end), 'd-M-y'))->getStyle('N10')->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('O10', $tender->tender_lead)->getStyle('O10')->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('P10', $tender->tender_division_participant)->getStyle('P10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('Q10', $tender->tender_description)->getStyle('Q10')->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('A11', '* Amount budget yang diisi sesuai dengan budget yang sudah di approve oleh finance')->getStyle('A11')->applyFromArray($styleContentWithoutBorder);

        $sheet->setCellValue('A13', 'Remark')->getStyle('A13')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setWrapText(true);
        
        $sheet->getStyle('A14:H24')->applyFromArray($styleBorder);
        $sheet->setCellValue('A14', 'Terlampir form:')->getStyle('A14')->applyFromArray($styleContentWithoutBorder);
        $startRow = 15;
        $no = 1;
        foreach ($torDocument as $item) {
            $sheet->setCellValue('A'.$startRow, $no++.". ".$item->tor_name.($item->tor_total > 1 ? " (".$item->tor_total."x)" : ""))->getStyle('A'.$startRow++)->applyFromArray($styleContentWithoutBorder);
        }

        
        $sheet->setCellValue('A26', 'Lampiran :')->getStyle('A26')->applyFromArray($styleContentWithoutBorderBold);
        $sheet->setCellValue('A27', '1. Dokumen teknis penunjang (TOR / Design Guideline)')->getStyle('A27')->applyFromArray($styleContentWithoutBorder);
        $sheet->setCellValue('A28', '2. Latar belakang pengajuan project sesuai dengan kebutuhan')->getStyle('A28')->applyFromArray($styleContentWithoutBorder);
        $sheet->setCellValue('A30', 'Note :')->getStyle('A30')->applyFromArray($styleContentWithoutBorderBold);
        $sheet->setCellValue('A31', 'Pengajuan project dengan amount > 1M di review bersama team PIN')->getStyle('A31')->applyFromArray($styleContentWithoutBorder);

        // ttd
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Signature PIC');
        $drawing->setDescription('Signatur PIC');
        $drawing->setPath(str_replace(base_url(), '', $pic->path_ttd)); /* put your path and image here */
        $drawing->setCoordinates('H28');
        $drawing->setWidth(150);
        $drawing->setOffsetX(15);
        $drawing->setWorksheet($sheet);
        
        if($tender->project_stat_app == '1'){
            $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing2->setName('Signature PIC');
            $drawing2->setDescription('Signatur PIC');
            $drawing2->setPath(str_replace(base_url(), '', $adminPIC->path_ttd)); /* put your path and image here */
            $drawing2->setCoordinates('G28');
            $drawing2->setWidth(150);
            $drawing2->setOffsetX(15);
            $drawing2->setWorksheet($sheet);
        }
        
        $sheet->setCellValue('H26', $tender->project_stat_app == '1' ? 'Jakarta, '.date_format(date_create($tender->approved_date), 'j F Y') : '-' )->getStyle('H26')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F27:F33')->applyFromArray($styleBorder);
        $sheet->getStyle('G27:G33')->applyFromArray($styleBorder);
        $sheet->getStyle('H27:H33')->applyFromArray($styleBorder);
        $sheet->setCellValue('F27', 'Penerima,')->getStyle('F27')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G27', 'Mengetahui,')->getStyle('G27')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H27', 'Pemohon,')->getStyle('H27')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('F32', '( Rahma Nur Ilma ),')->getStyle('F32')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G32', '( '.$adminPIC->user_name.' ),')->getStyle('G32')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H32', '( '.$pic->user_name.' ),')->getStyle('H32')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('F33', 'PIC PIN,')->getStyle('F33')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G33', 'Project Section Head,')->getStyle('G33')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H33', 'PIC Project,')->getStyle('H33')->applyFromArray($styleContentWithoutBorderBold)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $fileName = 'FRT_'.str_replace('/', '', $tender->project_no);
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"'); 
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }

    public function genSI($si){
        $contract = $this->queryGetFormContract($si['project_no']);
        $project = $this->queryGetProject($si['project_no']);
        $area = $this->queryGetArea($project->project_area);
        $vendor = $this->queryGetVendor($project->vendor_no);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        $date       = date_format(date_create($si['si_date']), 'j');
        $month      = $this->_ci->datefunction->getMonth()[date_format(date_create($si['si_date']), 'n')];
        $year       = date_format(date_create($si['si_date']), 'Y');
        $fullDate   = $date." ".$month." ".$year;

        $sheet->getColumnDimension('A')->setWidth('25');
        $sheet->getColumnDimension('B')->setWidth('25');
        $sheet->getColumnDimension('C')->setWidth('20');
        $sheet->getColumnDimension('D')->setWidth('25');
        $sheet->getColumnDimension('E')->setWidth('15');
        $sheet->getColumnDimension('F')->setWidth('20');
        $sheet->getColumnDimension('G')->setWidth('25');
        $sheet->getColumnDimension('H')->setWidth('25');
        $sheet->getColumnDimension('I')->setWidth('40');
        $sheet->getColumnDimension('J')->setWidth('8');
        $sheet->getColumnDimension('K')->setWidth('8');
        $sheet->getColumnDimension('L')->setWidth('8');

        $sheet->getRowDimension('24')->setRowHeight('k50');
        $sheet->getRowDimension('28')->setRowHeight('25');
        $sheet->getRowDimension('29')->setRowHeight('25');
        $sheet->getRowDimension('33')->setRowHeight('25');
        $sheet->getRowDimension('34')->setRowHeight('25');
        $sheet->getRowDimension('37')->setRowHeight('25');
        $sheet->getRowDimension('38')->setRowHeight('25');

        // HEADER
        $sheet->mergeCells('A3:B5')->setCellValue('A3', " ")->getStyle('A3:B5')->applyFromArray($this->styling_header_center(true, 16, ['top', 'right']));
        $sheet->mergeCells('A6:B6')->setCellValue('A6', $area->area_name)->getStyle('A6:B6')->applyFromArray($this->styling_header_left(true, 9, ['right']));
        $sheet->mergeCells('A7:B7')->setCellValue('A7', $area->area_branch_address)->getStyle('A7:B7')->applyFromArray($this->styling_header_center(true, 9, ['right']));
        $sheet->mergeCells('A8:B8')->setCellValue('A8', "")->getStyle('A8:B8')->applyFromArray($this->styling_header_left(true, 9, ['right', 'bottom']));
        $sheet->mergeCells('C3:H5')->setCellValue('C3', "SITE INSTRUCTION")->getStyle('C3:H5')->applyFromArray($this->styling_header_center(true, 16, ['outline']));
        $sheet->mergeCells('C6:H6')->setCellValue('C6', "Nomor Dokumen  : FORM 011/PROS-MFP-MLK3-015")->getStyle('C6:H6')->applyFromArray($this->styling_header_left(false, 9));
        $sheet->mergeCells('C7:H7')->setCellValue('C7', "Nomor Revisi   : 01")->getStyle('C7:H7')->applyFromArray($this->styling_header_left(false, 9));
        $sheet->mergeCells('C8:H8')->setCellValue('C8', "Halaman        : .....Dari.....")->getStyle('C8:H8')->applyFromArray($this->styling_header_left(false, 9, ['bottom']));
        $sheet->mergeCells('I3:I5')->setCellValue('I3', "INTEGRASI SISTEM")->getStyle('I3:I5')->applyFromArray($this->styling_header_center(true, 16, ['outline']));
        $sheet->mergeCells('I6:I8')->setCellValue('I6', "ISO 9001:2008; ISO 14001:2004, OHSAS 18001: 2007 & SMK3")->getStyle('I6:I8')->applyFromArray($this->styling_header_center(true, 11, ['outline']))->getAlignment()->setWrapText(true);

        $sheet->setCellValue('A11', "No")->getStyle('A11')->applyFromArray($this->styling_header_left(true, 12))->getFont()->setItalic(true);
        $sheet->setCellValue('A12', "Perihal")->getStyle('A12')->applyFromArray($this->styling_header_left(true, 12))->getFont()->setItalic(true);
        $sheet->setCellValue('A13', "No . SPK")->getStyle('A13')->applyFromArray($this->styling_header_left(true, 12))->getFont()->setItalic(true);
        $sheet->mergeCells('B11:H11')->setCellValue('B11', ": ".$si['si_no'])->getStyle('B11:H11')->applyFromArray($this->styling_header_left(false, 12));
        $sheet->mergeCells('B12:H12')->setCellValue('B12', ": ".$si['si_perihal'])->getStyle('B12:H12')->applyFromArray($this->styling_header_left(false, 12));
        $sheet->mergeCells('B13:H13')->setCellValue('B13', ": ".$contract->contract_branch_spk)->getStyle('B13:H13')->applyFromArray($this->styling_header_left(false, 12));

        $sheet->mergeCells('A16:B16')->setCellValue('A16', "Nama Form :")->getStyle('A16:B16')->applyFromArray($this->styling_header_left(false, 12, ['outline']))->getFont()->setItalic(true);
        $sheet->mergeCells('A17:B17')->setCellValue('A17', "Proyek :")->getStyle('A17:B17')->applyFromArray($this->styling_header_left(false, 12, ['outline']))->getFont()->setItalic(true);
        $sheet->mergeCells('A18:B18')->setCellValue('A18', "Ditujukan Kepada :")->getStyle('A18:B18')->applyFromArray($this->styling_header_left(false, 12, ['outline']))->getFont()->setItalic(true);
        $sheet->mergeCells('C16:I16')->setCellValue('C16', $si['si_no'])->getStyle('C16:I16')->applyFromArray($this->styling_header_left(true, 12, ['outline']));
        $sheet->mergeCells('C17:I17')->setCellValue('C17', $project->project_name)->getStyle('C17:I17')->applyFromArray($this->styling_header_left(true, 12, ['outline']));
        $sheet->mergeCells('C18:I18')->setCellValue('C18', $vendor->vendor_name)->getStyle('C18:I18')->applyFromArray($this->styling_header_left(true, 12, ['outline']));

        $sheet->mergeCells('A21:I21')->setCellValue('A21', "Dengan ini diinstruksikan kepada pihak yang tersebut di atas untuk melakukan pekerjaan tambah seperti yang tercantum di bawah ini.")->getStyle('A21:I21')->applyFromArray($this->styling_header_left(false, 12));
        // END HEADER

        // BODY
        $sheet->setCellValue('A23', "No")->getStyle('A23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->mergeCells('B23:C23')->setCellValue('B23', "Item Pekerjaan")->getStyle('B23:C23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->setCellValue('D23', "Vol")->getStyle('D23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->setCellValue('E23', "Satuan")->getStyle('E23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->mergeCells('F23:H23')->setCellValue('F23', "Alasan Perubahan")->getStyle('F23:H23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->setCellValue('I23', "Dokumen Pendukung")->getStyle('I23')->applyFromArray($this->styling_body_center(true, 12, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->setCellValue('A24', "1")->getStyle('A24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('B24:C24')->setCellValue('B24', $si['si_item'])->getStyle('B24:C24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('D24', number_format($si['si_vol']))->getStyle('D24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('E24', "Rp")->getStyle('E24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'))->getNumberFormat()->setFormatCode('$ #,##0.00');
        $sheet->mergeCells('F24:H24')->setCellValue('F24', $si['si_alasan_perubahan'])->getStyle('F24:H24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'));

        $dokPend = [];
        if(!empty($si['si_doc1_name'])) $dokPend[] = $si['si_doc1_name'];
        if(!empty($si['si_doc1_name'])) $dokPend[] = $si['si_doc2_name'];

        $sheet->setCellValue('I24', implode(' dan ', $dokPend))->getStyle('I24')->applyFromArray($this->styling_body_center(false, 12, ['outline'], 'FF000000', '00FFFFFF'));
        
        $sheet->mergeCells('A27:C27')->setCellValue('A27', "Pengaruh terhadap biaya - * Beri tanda centang ( √ )")->getStyle('A27:C27')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('A28', "Ada")->getStyle('A28')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B28', $si['si_pengaruh_biaya'] == '1' ? '√' : '')->getStyle('B28')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('C28:D28')->setCellValue('C28', "Pemberi Tugas*")->getStyle('C28:D28')->applyFromArray($this->styling_body_center(true, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('E28', "")->getStyle('E28')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('H28:I28')->setCellValue('H28', $si['si_catatan_biaya'])->getStyle('H28:I28')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('F28:G29')->setCellValue('F28', "Catatan")->getStyle('F28:G29')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('A29', "Tidak")->getStyle('A29')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B29', $si['si_pengaruh_biaya'] == '0' ? '√' : '')->getStyle('B29')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('C29:D29')->setCellValue('C29', "Penerima Tugas*")->getStyle('C29:D29')->applyFromArray($this->styling_body_center(true, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('E29', "")->getStyle('E29')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('H29:I29')->setCellValue('H29', "")->getStyle('H29:I29')->applyFromArray($this->styling_body_left(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('A30:C30')->setCellValue('A30', "* Diparaf oleh Pemberi dan Penerima Tugas untuk persetujuan ")->getStyle('A30:C30')->applyFromArray($this->styling_body_left(false, 10, [], 'FF000000', '00FFFFFF'));

        $sheet->mergeCells('A32:C32')->setCellValue('A32', "Pengaruh terhadap waktu - * Beri tanda centang ( √ )")->getStyle('A32:C32')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('A33', "Ada")->getStyle('A33')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B33', $si['si_pengaruh_waktu'] == '1' ? '√' : '')->getStyle('B33')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('C33', "Jumlah Hari")->getStyle('C33')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('D33:E33')->setCellValue('D33', "0")->getStyle('D33:E33')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('F33:G34')->setCellValue('F33', "Keterangan")->getStyle('F33:G34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('H33:I34')->setCellValue('H33', $si['si_keterangan_waktu'])->getStyle('H33:I34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('A34', "Tidak")->getStyle('A34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B34', $si['si_pengaruh_waktu'] == '0' ? '√' : '')->getStyle('B34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('C34', "")->getStyle('C34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', 'FF000000'))->getFill();
        $sheet->mergeCells('D34:E34')->setCellValue('D34', "")->getStyle('D34:E34')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', 'FF000000'))->getFill();

        
        $sheet->mergeCells('A36:C36')->setCellValue('A36', "Pengaruh terhadap scope - * Beri tanda centang ( √ )")->getStyle('A36:C36')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('A37', "Ada")->getStyle('A37')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B37', $si['si_pengaruh_scope'] == '1' ? '√' : '')->getStyle('B37')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('C37:E38')->setCellValue('C37', "Keterangan")->getStyle('C37:E38')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('F37:I38')->setCellValue('F37', $si['si_keterangan_scope'])->getStyle('F37:I38')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('A38', "Tidak")->getStyle('A38')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('B38', $si['si_pengaruh_scope'] == '0' ? '√' : '')->getStyle('B38')->applyFromArray($this->styling_body_center(false, 10, ['outline'], 'FF000000', '00FFFFFF'));
        // END BODY

        // FOOTER
        $sheet->setCellValue('A41', $area->area_name.", ".$fullDate)->getStyle('A41')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('A43:B43')->setCellValue('A43', "Pemberi Tugas,")->getStyle('A43:B43')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('A44:B44')->setCellValue('A44', "PT United Tractors Tbk.")->getStyle('A44:B44')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('I43', "Penerima Tugas,")->getStyle('I43')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('I44', "PT Graha Sarana Duta")->getStyle('I44')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));

        $sheet->setCellValue('A50', "Maya Alfianti S")->getStyle('A50')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('A51:B51')->setCellValue('A51', "Project Management Sect. Head")->getStyle('A51:B51')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));

        $sheet->setCellValue('C50', "Bagus Setiawan")->getStyle('C50')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('C51:D51')->setCellValue('C51', "GA Department Head")->getStyle('C51:D51')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));

        $sheet->setCellValue('E50', "Sara K. Loebis")->getStyle('E50')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('E51:F51')->setCellValue('E51', "Corp. ESRSGACOM Head")->getStyle('E51:F51')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));

        $sheet->setCellValue('I50', "Ferry Tumbelaka")->getStyle('I50')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->setCellValue('I51', "General Manager Area VII")->getStyle('I51')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));

        $sheet->setCellValue('A56', "Lampiran :")->getStyle('A56')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', '00FFFFFF'));
        $sheet->mergeCells('B56:H56')->setCellValue('B56', "")->getStyle('B56:H56')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', '00FFFFFF'));
        // END FOOTER

        $path = 'uploads/project/si/' . str_replace('/', '', $si['si_no']). '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return site_url($path);
    }
    public function genVO($si){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        $contract = $this->queryGetFormContract($si['project_no']);
        $project = $this->queryGetProject($si['project_no']);
        $area = $this->queryGetArea($project->project_area);
        $vendor = $this->queryGetVendor($project->vendor_no);

        $date       = date_format(date_create($si['si_date']), 'j');
        $month      = $this->_ci->datefunction->getMonth()[date_format(date_create($si['si_date']), 'n')];
        $year       = date_format(date_create($si['si_date']), 'Y');
        $fullDate   = $date." ".$month." ".$year;

        $sheet->getColumnDimension('A')->setWidth('5');
        $sheet->getColumnDimension('B')->setWidth('10');
        $sheet->getColumnDimension('C')->setWidth('20');
        $sheet->getColumnDimension('D')->setWidth('5');
        $sheet->getColumnDimension('E')->setWidth('12');
        $sheet->getColumnDimension('F')->setWidth('12');
        $sheet->getColumnDimension('G')->setWidth('20');
        $sheet->getColumnDimension('H')->setWidth('20');
        $sheet->getColumnDimension('I')->setWidth('10');

        // HEADER
        $sheet->mergeCells('B2:C4')->setCellValue('B2', " ")->getStyle('B2:C4')->applyFromArray($this->styling_header_center(false, 11, ['outline']));
        $sheet->mergeCells('D2:I4')->setCellValue('D2', "Form Variation Order")->getStyle('D2:I4')->applyFromArray($this->styling_header_center(true, 14, ['outline']));
        $sheet->mergeCells('J2:L4')->setCellValue('J2', "INTEGRASI SISTEM")->getStyle('J2:L4')->applyFromArray($this->styling_header_center(true, 11, ['outline']));
        $sheet->mergeCells('B5:C7')->setCellValue('B5', "Jl. ???")->getStyle('B5:C7')->applyFromArray($this->styling_header_center(true, 11, ['outline']));
        $sheet->mergeCells('D5:F5')->setCellValue('D5', "Nomor Dokumen")->getStyle('D5:F5')->applyFromArray($this->styling_header_left(false, 11, ['top', 'right']));
        $sheet->mergeCells('D6:F6')->setCellValue('D6', "Revisi")->getStyle('D6:F6')->applyFromArray($this->styling_header_left(false, 11, ['right']));
        $sheet->mergeCells('D7:F7')->setCellValue('D7', "Hal")->getStyle('D7:F7')->applyFromArray($this->styling_header_left(false, 11, ['bottom', 'right']));
        $sheet->setCellValue('G5', ":       ")->getStyle('G5')->applyFromArray($this->styling_header_left(true, 11, ['top', 'right']));
        $sheet->setCellValue('G6', ":       ")->getStyle('G6')->applyFromArray($this->styling_header_left(true, 11, ['right']));
        $sheet->setCellValue('G7', ":       ")->getStyle('G7')->applyFromArray($this->styling_header_left(true, 11, ['bottom', 'right']));
        $sheet->mergeCells('H5:I5')->setCellValue('H5', "")->getStyle('H5:I5')->applyFromArray($this->styling_header_left(false, 11, ['top', 'right']));
        $sheet->mergeCells('H6:I6')->setCellValue('H6', "")->getStyle('H6:I6')->applyFromArray($this->styling_header_left(false, 11, ['right']));
        $sheet->mergeCells('H7:I7')->setCellValue('H7', "1 Dari 1")->getStyle('H7:I7')->applyFromArray($this->styling_header_left(false, 11, ['bottom', 'right']));
        $sheet->mergeCells('J5:L7')->setCellValue('J5', "ISO 9001:2015; ISO 14001:2015, ISO450001:2018 & SMK3")->getStyle('J5:L7')->applyFromArray($this->styling_header_center(true, 11, ['outline']))->getAlignment()->setWrapText(true);
        // END HEADER

        // BODY
        $sheet->mergeCells('B9:C9')->setCellValue('B9', "Nomor Variation Order")->getStyle('B9:C9')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B10:C10')->setCellValue('B10', "Variation Order Ke")->getStyle('B10:C10')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B11:C11')->setCellValue('B11', "Nama Project")->getStyle('B11:C11')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B12:C12')->setCellValue('B12', "Kontraktor")->getStyle('B12:C12')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('D9', ":")->getStyle('D9')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('D10', ":")->getStyle('D10')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('D11', ":")->getStyle('D11')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('D12', ":")->getStyle('D12')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('E9', $si['si_vo_name'])->getStyle('E9')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('E10', "01 (Satu)")->getStyle('E10')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('E11', $project->project_name)->getStyle('E11')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('E12', $vendor->vendor_name)->getStyle('E12')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));

        $sheet->mergeCells('B14:G14')->setCellValue('B14', "Mengacu kepada Surat Perintah Kerja (SPK) dengan nomor")->getStyle('B14:G14')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B15:G15')->setCellValue('B15', "Mengacu kepada Site Instruction (SI) dengan nomor")->getStyle('B15:G15')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('H14', ": ".$contract->contract_branch_spk)->getStyle('H14')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('H15', ": ".$si['si_no'])->getStyle('D12')->applyFromArray($this->styling_body_left(true, 11, [], 'FF000000', 'FFFFFFFF'));
        $sheet->setCellValue('B17', "Dikarenakan dikeluarkan Variasi ini, maka Harga Kontrak disesuaikan menjadi sebagai berikut :")->getStyle('B17')->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFFF'));

        $sheet->setCellValue('B19', "No")->getStyle('B19')->applyFromArray($this->styling_body_center(true, 11, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->mergeCells('C19:I19')->setCellValue('C19', "Kontrak")->getStyle('C19:I19')->applyFromArray($this->styling_body_center(true, 11, ['outline'], 'FF000000', 'FFD8D8D8'));
        $sheet->mergeCells('J19:L19')->setCellValue('J19', "Total Nominal Kontrak")->getStyle('J19:L19')->applyFromArray($this->styling_body_center(true, 11, ['outline'], 'FF000000', 'FFD8D8D8'));

        $rowStart = 20;
        
        $sheet->setCellValue('B' . $rowStart, 'i')->getStyle('B' . $rowStart)->applyFromArray($this->styling_body_left(true, 11, ['outline'], 'FF000000', '00FFFFFF'))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $rowStart . ':I' . $rowStart)->setCellValue('C' . $rowStart, "Harga Kontrak Awal")->getStyle('C' . $rowStart . ':I' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . $rowStart . ':L' . $rowStart)->setCellValue('J' . $rowStart, $project->project_kontrak)->getStyle('J' . $rowStart . ':L' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $rowStart++;
        
        $sheet->setCellValue('B' . $rowStart, 'ii')->getStyle('B' . $rowStart)->applyFromArray($this->styling_body_left(true, 11, ['outline'], 'FF000000', '00FFFFFF'))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $rowStart . ':I' . $rowStart)->setCellValue('C' . $rowStart, "Harga Kontrak setelah adanya Variation Order sebelumnya No. ................")->getStyle('C' . $rowStart . ':I' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . $rowStart . ':L' . $rowStart)->setCellValue('J' . $rowStart, "-")->getStyle('J' . $rowStart . ':L' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $rowStart++;
        
        $sheet->setCellValue('B' . $rowStart, 'iii')->getStyle('B' . $rowStart)->applyFromArray($this->styling_body_left(true, 11, ['outline'], 'FF000000', '00FFFFFF'))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $rowStart . ':I' . $rowStart)->setCellValue('C' . $rowStart, "Penambahan/ Pengurangan dikarenakan adanya Variation Order No. 01 (Satu)")->getStyle('C' . $rowStart . ':I' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . $rowStart . ':L' . $rowStart)->setCellValue('J' . $rowStart, $si['si_vol'])->getStyle('J' . $rowStart . ':L' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $rowStart++;
        
        $sheet->setCellValue('B' . $rowStart, 'iv')->getStyle('B' . $rowStart)->applyFromArray($this->styling_body_left(true, 11, ['outline'], 'FF000000', '00FFFFFF'))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $rowStart . ':I' . $rowStart)->setCellValue('C' . $rowStart, "Harga Kontrak SPK/333-P/XI/2018 karena Variation Order ini menjadi")->getStyle('C' . $rowStart . ':I' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . $rowStart . ':L' . $rowStart)->setCellValue('J' . $rowStart, (int)$si['si_vol']+(int)$project->project_kontrak)->getStyle('J' . $rowStart . ':L' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $rowStart++;
        
        $sheet->setCellValue('B' . $rowStart, '')->getStyle('B' . $rowStart)->applyFromArray($this->styling_body_left(true, 11, ['outline'], 'FF000000', '00FFFFFF'))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $rowStart . ':I' . $rowStart)->setCellValue('C' . $rowStart, "")->getStyle('C' . $rowStart . ':I' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . $rowStart . ':L' . $rowStart)->setCellValue('J' . $rowStart, "")->getStyle('J' . $rowStart . ':L' . $rowStart)->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $rowStart++;
        
        // END BODY

        // FOOTER
        $sheet->setCellValue('B' . ($rowStart + 1), $area->area_name.", ".$fullDate)->getStyle('B' . ($rowStart + 1))->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 2) . ':I' . ($rowStart + 2))->setCellValue('B' . ($rowStart + 2), "Pemberi Tugas, ")->getStyle('B' . ($rowStart + 2) . ':I' . ($rowStart + 2))->applyFromArray($this->styling_body_left(true, 11, ['left', 'top', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 3) . ':I' . ($rowStart + 3))->setCellValue('B' . ($rowStart + 3), "PT United Tractors Tbk.")->getStyle('B' . ($rowStart + 3) . ':I' . ($rowStart + 3))->applyFromArray($this->styling_body_left(true, 11, ['left', 'bottom', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 2) . ':L' . ($rowStart + 2))->setCellValue('J' . ($rowStart + 2), "Penerima Tugas, ")->getStyle('J' . ($rowStart + 2) . ':L' . ($rowStart + 2))->applyFromArray($this->styling_body_left(true, 11, ['left', 'top', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 3) . ':L' . ($rowStart + 3))->setCellValue('J' . ($rowStart + 3), "PT Graha Sarana Duta")->getStyle('J' . ($rowStart + 3) . ':L' . ($rowStart + 3))->applyFromArray($this->styling_body_left(true, 11, ['left', 'bottom', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 4) . ':F' . ($rowStart + 7))->setCellValue('B' . ($rowStart + 4), "")->getStyle('B' . ($rowStart + 4) . ':F' . ($rowStart + 7))->applyFromArray($this->styling_body_left(false, 11, ['left', 'top', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 8) . ':F' . ($rowStart + 8))->setCellValue('B' . ($rowStart + 8), "Sara K. Loebis")->getStyle('B' . ($rowStart + 8) . ':F' . ($rowStart + 8))->applyFromArray($this->styling_body_left(true, 11, ['left', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 9) . ':F' . ($rowStart + 9))->setCellValue('B' . ($rowStart + 9), "CESRSGACOM Div. Head")->getStyle('B' . ($rowStart + 9) . ':F' . ($rowStart + 9))->applyFromArray($this->styling_body_left(false, 11, ['left', 'bottom', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('G' . ($rowStart + 4) . ':I' . ($rowStart + 9))->setCellValue('G' . ($rowStart + 4), "")->getStyle('G' . ($rowStart + 4) . ':I' . ($rowStart + 9))->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 4) . ':L' . ($rowStart + 7))->setCellValue('J' . ($rowStart + 4), "")->getStyle('J' . ($rowStart + 4) . ':L' . ($rowStart + 7))->applyFromArray($this->styling_body_left(false, 11, ['left', 'top', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 8) . ':L' . ($rowStart + 8))->setCellValue('J' . ($rowStart + 8), "Ferry Tumbelaka")->getStyle('J' . ($rowStart + 8) . ':L' . ($rowStart + 8))->applyFromArray($this->styling_body_left(true, 11, ['left', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 9) . ':L' . ($rowStart + 9))->setCellValue('J' . ($rowStart + 9), "General Manager Area VII")->getStyle('J' . ($rowStart + 9) . ':L' . ($rowStart + 9))->applyFromArray($this->styling_body_left(false, 11, ['left', 'bottom', 'right'], 'FF000000', 'FFFFFFFF'));

        $sheet->mergeCells('B' . ($rowStart + 10) . ':F' . ($rowStart + 14))->setCellValue('B' . ($rowStart + 10), "")->getStyle('B' . ($rowStart + 10) . ':F' . ($rowStart + 14))->applyFromArray($this->styling_body_left(false, 11, ['left', 'top', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 15) . ':F' . ($rowStart + 15))->setCellValue('B' . ($rowStart + 15), "Edhie Sarwono")->getStyle('B' . ($rowStart + 15) . ':F' . ($rowStart + 15))->applyFromArray($this->styling_body_left(true, 11, ['left', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('B' . ($rowStart + 16) . ':F' . ($rowStart + 16))->setCellValue('B' . ($rowStart + 16), "HCESRSGACOM Director")->getStyle('B' . ($rowStart + 16) . ':F' . ($rowStart + 16))->applyFromArray($this->styling_body_left(false, 11, ['left', 'bottom', 'right'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('G' . ($rowStart + 10) . ':I' . ($rowStart + 16))->setCellValue('G' . ($rowStart + 10), "")->getStyle('G' . ($rowStart + 10) . ':I' . ($rowStart + 16))->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));
        $sheet->mergeCells('J' . ($rowStart + 10) . ':L' . ($rowStart + 16))->setCellValue('J' . ($rowStart + 10), "")->getStyle('J' . ($rowStart + 10) . ':L' . ($rowStart + 16))->applyFromArray($this->styling_body_left(false, 11, ['outline'], 'FF000000', 'FFFFFFFF'));

        $sheet->setCellValue('B' . ($rowStart + 21), "Lampiran : ")->getStyle('B' . ($rowStart + 21))->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFF'));
        $sheet->setCellValue('C' . ($rowStart + 21), "- Copy LUT/024/4400-D/III/22")->getStyle('C' . ($rowStart + 21))->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFF'));
        $sheet->setCellValue('C' . ($rowStart + 22), "- Copy SI/10-P/9972/V/22")->getStyle('C' . ($rowStart + 22))->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFF'));
        $sheet->setCellValue('C' . ($rowStart + 23), "- Penawaran Kerja Tambah Sectic Tank 22 April 2022")->getStyle('C' . ($rowStart + 23))->applyFromArray($this->styling_body_left(false, 11, [], 'FF000000', 'FFFFFFF'));
        // END FOOTER

        $path   = 'uploads/project/vo/' . str_replace('/', '', $si['si_vo_name']). '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return site_url($path);
    }
    public function styling_header_center($Bold, $FontSize, $border = array())
    {
        $styleHeader['font']['bold']                        = $Bold;
        $styleHeader['font']['size']                        = $FontSize;
        $styleHeader['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleHeader['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleHeader['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;
        }
        $styleHeader['borders']['outline']['color']['argb'] = 'FF000000';

        return $styleHeader;
    }

    public function styling_header_left($Bold, $FontSize, $border = array())
    {
        $styleHeader['font']['bold']                        = $Bold;
        $styleHeader['font']['size']                        = $FontSize;
        $styleHeader['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
        $styleHeader['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleHeader['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;
        }
        $styleHeader['borders']['outline']['color']['argb'] = 'FF000000';

        return $styleHeader;
    }

    public function styling_body_center($Bold, $FontSize, $border = array(), $ColorText, $ColorFill)
    {
        $styleBody['font']['bold']                        = $Bold;
        $styleBody['font']['size']                        = $FontSize;
        $styleBody['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBody['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleBody['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        }
        $styleBody['borders']['outline']['color']['argb']   = 'FF000000';
        $styleBody['fill']['fillType']                      = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleBody['font']['color']['argb']                 = $ColorText;
        $styleBody['fill']['color']['argb']                 = $ColorFill;

        return $styleBody;
    }

    public function styling_body_left($Bold, $FontSize, $border = array(), $ColorText, $ColorFill)
    {
        $styleBody['font']['bold']                        = $Bold;
        $styleBody['font']['size']                        = $FontSize;
        $styleBody['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
        $styleBody['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleBody['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        }
        $styleBody['borders']['outline']['color']['argb']   = 'FF000000';
        $styleBody['font']['color']['argb']                 = $ColorText;
        $styleBody['fill']['color']['argb']                 = $ColorFill;

        return $styleBody;
    }

    public function queryGetFormContract($projectNo){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT
                mp.* ,
                mv.* ,
                tc.*
            FROM 
                transaction_contract tc , 
                master_project mp ,
                master_vendor mv 
            WHERE 
                tc.project_no = '".$projectNo."'
                AND mp.project_no = tc.project_no COLLATE utf8mb4_unicode_ci
                AND mv.vendor_no = tc.vendor_no
        ")->row();
    }
    public function queryGetOtherDocument($projectNo){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                document_tender_name
            FROM 
                transaction_tender_document
            WHERE 
                project_no = '".$projectNo."'
                AND tender_document_no = 6
        ")->result();
    }
    public function queryGetTenderDocument($projectNo){
        $DB2    = $this->_ci->load->database('gaSys2', true);
        $docs['fsv'] = $DB2->query("
            SELECT tender_document_no, document_tender_name
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '1'
        ")->row();
        $docs['gambar'] = $DB2->query("
            SELECT tender_document_no, document_tender_name
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '3'
        ")->row();
        $docs['bastl'] = $DB2->query("
            SELECT tender_document_no, document_tender_name
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '4'
        ")->row();
        $docs['sCurve'] = $DB2->query("
            SELECT tender_document_no, document_tender_name
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '2'
        ")->row();
        $docs['boq'] = $DB2->query("
            SELECT tender_document_no, document_tender_name
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '10'
        ")->row();

        return $docs;
    }
    public function queryGetProject($projectNo){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                *
            FROM 
                master_project
            WHERE 
                project_no = '".$projectNo."'
        ")->row();
    }
    public function queryGetArea($areaCode){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                *
            FROM 
                master_area
            WHERE 
                area_code = '".$areaCode."'
        ")->row();
    }
    public function queryGetVendor($vendorNo){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                *
            FROM 
                master_vendor
            WHERE 
                vendor_no = '".$vendorNo."'
        ")->row();
    }
    public function queryGetFormTender($projectNo){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                tt.*,
                mp.approved_date ,
                mp.project_stat_app ,
                mta.tender_area_name ,
                mtt.tender_type_name ,
                md.division_name ,
                md2.department_name 
            FROM 
                transaction_tender tt ,
                master_project mp ,
                master_tender_area mta ,
                master_tender_type mtt ,
                master_division md ,
                master_department md2 
            WHERE 
                tt.project_no = '".$projectNo."'
                AND mta.tender_area_code = tt.tender_area_code
                AND mtt.tender_type_no = tt.tender_type_no
                AND md.division_no = tt.division_no
                AND md2.department_no = tt.department_no 
                AND mp.project_no = tt.project_no 
        ")->row();
    }
    public function queryGetPIC($idFormTender){
        $DB2 = $this->_ci->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mu.user_name ,
                mu.path_ttd 
            FROM 
                transaction_tender tt ,
                master_project mp ,
                master_user mu 
            WHERE 
                tt.tender_no = '".$idFormTender."'
                AND tt.project_no = mp.project_no 
                AND mp.project_pic = mu.user_no 
        ")->row();
    }
    public function queryGetTorDocument($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mt.tor_name , 
                COUNT(ttd.document_no) as tor_total
            FROM 
                transaction_tor_document ttd ,
                master_tor mt 
            WHERE 
                ttd.project_no = '".$projectNo."'
                AND mt.tor_no = ttd.tor_no 
            GROUP BY mt.tor_name 
        ")->result();
    }
}