<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProjectController extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Area');
        $this->load->model('ProjectStatus');
        $this->load->model('ProjectType');
        $this->load->model('ProjectCategory');
        $this->load->model('ProjectType');
        $this->load->model('ProjectVendor');
    }
    public function signed($docType){
        $res = $this->document->signDoc($docType, "TRANS_0474b2e3b2fc9805c97dd145e567a561");
        print_r($res);
    }
    public function download(){
        // === STYLING SHEETS ===
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

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
        $styleTitle['fill']['color']['argb']                = 'FF595959';
        $styleTitle['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleTitle['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $styleTitle['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $styleTitle2['font']['bold']                         = true;
        $styleTitle2['font']['size']                         = 11;
        $styleTitle2['font']['color']['argb']                = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE;
        $styleTitle2['fill']['fillType']                     = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $styleTitle2['fill']['color']['argb']                = 'FF0070C0';
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

        $styleContentCenterBold['font']['size']                         = 11;
        $styleContentCenterBold['font']['bold']                         = true;
        $styleContentCenterBold['borders']['outline']['borderStyle']    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $styleContentCenterBold['alignment']['horizontal']              = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleContentCenterBold['alignment']['vertical']                = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(25);
        
        $list = $sheet->getCell('B9')->getDataValidation();
        $list->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setShowDropDown(true);
        $list->setFormula1('"Item A,Item B,Item C"');
        // $sheet->freezePane('A','B');

        // set height row
        $sheet->getRowDimension('6')->setRowHeight(50);
        $rowStart = 8;
        for($i = 1; $i <= 21; $i++){
            $sheet->getRowDimension($rowStart)->setRowHeight(40);
            $rowStart++;
        }

        // HEADER
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Paid');
        $drawing->setDescription('Paid');
        $drawing->setPath("assets/img/debitnote/header.png"); /* put your path and image here */
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
        $sheet->setCellValue('A6', 'CAPEX PROJECT MB'.((int)date('Y') + 1))->getStyle('A6')->applyFromArray($styleHeading1);

        // WRITE DATA
        $sheet->setCellValue('A8', 'NO')->getStyle('A8')->applyFromArray($styleTitle);
        $sheet->setCellValue('B8', 'KODE AREA')->getStyle('B8')->applyFromArray($styleTitle);
        $sheet->setCellValue('C8', 'NAMA PROJECT')->getStyle('C8')->applyFromArray($styleTitle);
        $sheet->setCellValue('D8', 'STATUS')->getStyle('D8')->applyFromArray($styleTitle);
        $sheet->setCellValue('E8', 'TYPE OF PROJECT')->getStyle('E8')->applyFromArray($styleTitle);
        $sheet->setCellValue('F8', 'CATEGORY')->getStyle('F8')->applyFromArray($styleTitle);
        $sheet->setCellValue('G8', 'NILAI TOTAL PEKERJAAN')->getStyle('G8')->applyFromArray($styleTitle2);
        $area = array();
        foreach ($this->Area->getAll() as $item) {
            array_push($area, $item->area_code);
        }
        $status = array();
        foreach ($this->ProjectStatus->getAll() as $item) {
            array_push($status, $item->status_name);
        }
        $type = array();
        foreach ($this->ProjectType->getAll() as $item) {
            array_push($type, $item->type_name);
        }
        $category = array();
        foreach ($this->ProjectCategory->getAll() as $item) {
            array_push($category, $item->category_name);
        }
        $vendor = array();
        foreach ($this->ProjectVendor->getAll() as $item) {
            array_push($vendor, $item->vendor_name);
        }
        $rowStart = 9;
        for($i = 1; $i <= 20; $i++){
            // NO
            $sheet->setCellValue('A'.$rowStart, $i)->getStyle('A'.$rowStart)->applyFromArray($styleContentCenterBold)->getAlignment()->setWrapText(true);
            // Area
            $sheet->getStyle('B'.$rowStart)->applyFromArray($styleContentCenterBold)->getAlignment()->setWrapText(true);
            $lstArea = $sheet->getCell('B'.$rowStart)->getDataValidation();
            $lstArea->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setShowDropDown(true);
            $lstArea->setFormula1('"'.implode(',', $area).'"');
            // Nama Project
            $sheet->getStyle('C'.$rowStart)->applyFromArray($styleContent)->getAlignment()->setWrapText(true);
            // Status
            $sheet->getStyle('D'.$rowStart)->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
            $lstStatus = $sheet->getCell('D'.$rowStart)->getDataValidation();
            $lstStatus->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setShowDropDown(true);
            $lstStatus->setFormula1('"'.implode(',', $status).'"');
            // Type
            $sheet->getStyle('E'.$rowStart)->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
            $lstType = $sheet->getCell('E'.$rowStart)->getDataValidation();
            $lstType->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setShowDropDown(true);
            $lstType->setFormula1('"'.implode(',', $type).'"');
            // Category
            $sheet->getStyle('F'.$rowStart)->applyFromArray($styleContentCenter)->getAlignment()->setWrapText(true);
            $lstCategory = $sheet->getCell('F'.$rowStart)->getDataValidation();
            $lstCategory->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setShowDropDown(true);
            $lstCategory->setFormula1('"'.implode(',', $category).'"');
            // Sheet With Number Format
            $sheet->getStyle('G'.$rowStart)->applyFromArray($styleContentCenter)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G'.$rowStart)->getAlignment()->setWrapText(true);

            $rowStart++;
        }

        $fileName = 'CAPEX_PROJECT_MB'.((int)date('Y') + 1);
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"'); 
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
    public function downloadFormTender($idFormTender){
        $DB2 = $this->load->database('gaSys2', true);
        $tender = $this->queryGetFormTender($idFormTender);
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
    public function downloadFormContract($idFormContract){
        $contract = $this->queryGetFormContract($idFormContract);
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
        

        $fileName = 'FRC_'.$contract->project_no.''.date('Ymd');
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"'); 
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
    public function downloadCOP(){
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
        
        // HEADER
        $sheet->mergeCells('A2:E4')->setCellValue('A2', " ")->getStyle('A2:E4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('F2:Q4')->setCellValue('F2', "CERTIFICATE OF PAYMENT")->getStyle('F2:Q4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('R2:U4')->setCellValue('R2', "INTEGRASI SISTEM")->getStyle('R2:U4')->applyFromArray($styleBrHeading1);
        $sheet->mergeCells('A5:E7')->setCellValue('A5', "Jl. ??")->getStyle('A5:E7')->applyFromArray($styleBrHeading2);
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
        $sheet->setCellValue('E11', "??")->getStyle('E11')->applyFromArray($styleContent);
        $sheet->mergeCells('A12:C12')->setCellValue('A12', "Nomor Proyek")->getStyle('A12:C12')->applyFromArray($styleContent);
        $sheet->setCellValue('D12', ":")->getStyle('D12')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E12', "??")->getStyle('E12')->applyFromArray($styleContent);
        $sheet->mergeCells('A13:C13')->setCellValue('A13', "Lokasi Project")->getStyle('A13:C13')->applyFromArray($styleContent);
        $sheet->setCellValue('D13', ":")->getStyle('D13')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E13', "??")->getStyle('E13')->applyFromArray($styleContent);
        $sheet->mergeCells('A14:C14')->setCellValue('A14', "Area Project")->getStyle('A14:C14')->applyFromArray($styleContent);
        $sheet->setCellValue('D14', ":")->getStyle('D14')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E14', "??")->getStyle('E14')->applyFromArray($styleContent);
        $sheet->mergeCells('A15:C15')->setCellValue('A15', "Kontraktor")->getStyle('A15:C15')->applyFromArray($styleContent);
        $sheet->setCellValue('D15', ":")->getStyle('D15')->applyFromArray($styleTcContent);
        $sheet->setCellValue('E15', "??")->getStyle('E15')->applyFromArray($styleContent);
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
        $sheet->setCellValue('G20', "29-Nov-21")->getStyle('G20')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('H20:K20')->setCellValue('H20', "LUT/042/3324-A/X/2021 ???")->getStyle('H20:K20')->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('L20:Q20')->setCellValue('L20', "700000000")->getStyle('L20:Q20')->applyFromArray($styleBrContent);
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
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "SI/24poiwjef??")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('F'.$currRow.':I'.$currRow)->setCellValue('F'.$currRow, "Penambahan grill ??")->getStyle('F'.$currRow.':I'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('J'.$currRow.':K'.$currRow)->setCellValue('J'.$currRow, "1378345345")->getStyle('J'.$currRow.':K'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('L'.$currRow.':M'.$currRow)->setCellValue('L'.$currRow, "")->getStyle('L'.$currRow.':M'.$currRow)->applyFromArray($styleBrContent);
        $sheet->mergeCells('N'.$currRow.':O'.$currRow)->setCellValue('N'.$currRow, "")->getStyle('N'.$currRow.':O'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('P'.$currRow.':Q'.$currRow)->setCellValue('P'.$currRow, "V")->getStyle('P'.$currRow.':Q'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('R'.$currRow.':U'.$currRow)->setCellValue('R'.$currRow, "1378345345")->getStyle('R'.$currRow.':U'.$currRow)->applyFromArray($styleBrContent);
        
        $sheet->mergeCells('A'.++$currRow.':Q'.$currRow)->setCellValue('A'.$currRow, "TOTAL")->getStyle('A'.$currRow.':Q'.$currRow)->applyFromArray($styleColGreen);
        $sheet->mergeCells('R'.$currRow.':U'.$currRow)->setCellValue('R'.$currRow, "1378345345")->getStyle('R'.$currRow.':U'.$currRow++)->applyFromArray($styleBrContent);
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
        $rowPayData = ++$currRow;
        $sheet->setCellValue('A'.$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "Termin I")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('F'.$currRow, "DP 30%")->getStyle('F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "30%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "Termin I")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('F'.$currRow, "DP 30%")->getStyle('F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "30%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "Termin I")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('F'.$currRow, "DP 30%")->getStyle('F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "30%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->setCellValue('A'.++$currRow, "1")->getStyle('A'.$currRow)->applyFromArray($styleBrTcContent);
        $sheet->mergeCells('B'.$currRow.':E'.$currRow)->setCellValue('B'.$currRow, "Termin I")->getStyle('B'.$currRow.':E'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('F'.$currRow, "DP 30%")->getStyle('F'.$currRow)->applyFromArray($styleBrContent);
        $sheet->setCellValue('G'.$currRow, "30%")->getStyle('G'.$currRow)->applyFromArray($styleBrTcContent);
        
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
        
        for($x = 0; $x < 5; $x++){
            $tempRow = $rowPayTitle;
            $sheet->mergeCells($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, "Payment ".$colPay[$x][0])->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->mergeCells($colPay[$x][1].++$tempRow.':'.$colPay[$x][2].$tempRow)->setCellValue($colPay[$x][1].$tempRow, "Oct-21 ".$colPay[$x][0])->getStyle($colPay[$x][1].$tempRow.':'.$colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][1].++$tempRow, "Actual Progress")->getStyle($colPay[$x][1].$tempRow)->applyFromArray($styleColBlue);
            $sheet->setCellValue($colPay[$x][2].$tempRow, "Nilai (Rp)")->getStyle($colPay[$x][2].$tempRow)->applyFromArray($styleColBlue);
            
            $tempRow2 = $rowPayData;
            for($y = 0; $y < 4; $y++){
                $sheet->setCellValue($colPay[$x][1].$tempRow2, "")->getStyle($colPay[$x][1]. $tempRow2)->applyFromArray($styleBrTcContent);
                $sheet->setCellValue($colPay[$x][2].$tempRow2, "")->getStyle($colPay[$x][2].$tempRow2++)->applyFromArray($styleBrContent);
            }

            $tempRow3 = $rowPayDataPengurangan;
            for($y = 0; $y < 6; $y++){
                $sheet->setCellValue($colPay[$x][1].$tempRow3, "")->getStyle($colPay[$x][1]. $tempRow3)->applyFromArray($styleBrTcContent);
                $sheet->setCellValue($colPay[$x][2].$tempRow3, "")->getStyle($colPay[$x][2].$tempRow3++)->applyFromArray($styleBrContent);
            }

            $sheet->mergeCells($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->setCellValue($colPay[$x][1].$rowPayTotal1, 500000)->getStyle($colPay[$x][1].$rowPayTotal1.':'.$colPay[$x][2].$rowPayTotal1)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->setCellValue($colPay[$x][1].$rowPayTotal2, 500000)->getStyle($colPay[$x][1].$rowPayTotal2.':'.$colPay[$x][2].$rowPayTotal2)->applyFromArray($styleColYellow);
            $sheet->mergeCells($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->setCellValue($colPay[$x][1].$rowPayTotalAll, 500000)->getStyle($colPay[$x][1].$rowPayTotalAll.':'.$colPay[$x][2].$rowPayTotalAll)->applyFromArray($styleBBrTcContent);
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
        $sheet->mergeCells('G'.$currRow.':H'.$currRow)->setCellValue('G'.$currRow, "Rp.35000000")->getStyle('G'.$currRow.':H'.$currRow)->applyFromArray($styleFillGrey);
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


        $fileName = 'TESTING';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName .'.xlsx"'); 
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
    public function queryGetFormTender($idFormTender){
        $DB2 = $this->load->database('gaSys2', true);
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
                tt.tender_no = '".$idFormTender."'
                AND mta.tender_area_code = tt.tender_area_code
                AND mtt.tender_type_no = tt.tender_type_no
                AND md.division_no = tt.division_no
                AND md2.department_no = tt.department_no 
                AND mp.project_no = tt.project_no 
        ")->row();
    }
    public function queryGetPIC($idFormTender){
        $DB2 = $this->load->database('gaSys2', true);
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
    public function queryGetFormContract($idFormContract){
        $DB2 = $this->load->database('gaSys2', true);
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
                tc.contract_no = '".$idFormContract."'
                AND mp.project_no = tc.project_no COLLATE utf8mb4_unicode_ci
                AND mv.vendor_no = tc.vendor_no COLLATE 
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
    public function queryGetOtherDocument($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
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
        $DB2    = $this->load->database('gaSys2', true);
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
        $DB2 = $this->load->database('gaSys2', true);
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
        $DB2 = $this->load->database('gaSys2', true);
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
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                *
            FROM 
                master_vendor
            WHERE 
                vendor_no = '".$vendorNo."'
        ")->row();
    }
    public function tes(){
        print_r(hash('sha256', md5('maya123')));
    }
}