<?php defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once(FCPATH . '/vendor/autoload.php');

class Bast extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
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

    public function styling_body_center($Bold, $FontSize, $border = array(), $ColorText, $ColorFill, $solid)
    {
        $styleBody['font']['bold']                        = $Bold;
        $styleBody['font']['size']                        = $FontSize;
        $styleBody['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $styleBody['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleBody['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        }
        $styleBody['borders']['outline']['color']['argb']   = 'FF000000';
        if ($solid) {
            $styleBody['fill']['fillType']                  = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        }
        $styleBody['font']['color']['argb']                 = $ColorText;
        $styleBody['fill']['color']['argb']                 = $ColorFill;

        return $styleBody;
    }

    public function styling_body_left($Bold, $FontSize, $border = array(), $ColorText, $ColorFill, $solid)
    {
        $styleBody['font']['bold']                        = $Bold;
        $styleBody['font']['size']                        = $FontSize;
        $styleBody['alignment']['horizontal']             = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
        $styleBody['alignment']['vertical']               = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        for ($i = 0; $i < count($border); $i++) {
            $styleBody['borders'][$border[$i]]['borderStyle']   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        }
        $styleBody['borders']['outline']['color']['argb']   = 'FF000000';
        if ($solid) {
            $styleBody['fill']['fillType']                  = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        }
        $styleBody['font']['color']['argb']                 = $ColorText;
        $styleBody['fill']['color']['argb']                 = $ColorFill;

        return $styleBody;
    }

    public function MakeBast_get()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $spreadsheet->getSheet(0)->getStyle('A1:O58')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', true));;

        $sheet->getColumnDimension('A')->setWidth('4');
        $sheet->getColumnDimension('B')->setWidth('10');
        $sheet->getColumnDimension('C')->setWidth('12');
        $sheet->getColumnDimension('D')->setWidth('4');
        $sheet->getColumnDimension('E')->setWidth('12');
        $sheet->getColumnDimension('F')->setWidth('4');
        $sheet->getColumnDimension('G')->setWidth('12');
        $sheet->getColumnDimension('H')->setWidth('12');
        $sheet->getColumnDimension('I')->setWidth('12');
        $sheet->getColumnDimension('J')->setWidth('12');
        $sheet->getColumnDimension('K')->setWidth('12');
        $sheet->getColumnDimension('L')->setWidth('10');
        $sheet->getColumnDimension('M')->setWidth('10');
        $sheet->getColumnDimension('N')->setWidth('10');
        $sheet->getColumnDimension('O')->setWidth('4');

        $sheet->getRowDimension('29')->setRowHeight('15');
        $sheet->getRowDimension('30')->setRowHeight('15');
        $sheet->getRowDimension('33')->setRowHeight('25');
        $sheet->getRowDimension('34')->setRowHeight('25');
        $sheet->getRowDimension('41')->setRowHeight('50');

        // HEADER
        $sheet->mergeCells('B2:D4')->setCellValue('B2', " ")->getStyle('B2:D4')->applyFromArray($this->styling_header_center(true, 18, ['outline']));
        $sheet->mergeCells('E2:K4')->setCellValue('E2', "BERITA ACARA SERAH TERIMA")->getStyle('E2:K4')->applyFromArray($this->styling_header_center(true, 18, ['outline']));
        $sheet->mergeCells('L2:N4')->setCellValue('L2', " ")->getStyle('L2:N4')->applyFromArray($this->styling_header_center(true, 18, ['outline']));

        $sheet->mergeCells('B5:D7')->setCellValue('B5', "Jl. Raya Bekasi Km.22 Jakarta")->getStyle('B5:D7')->applyFromArray($this->styling_header_center(true, 12, ['outline']))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('E5:F5')->setCellValue('E5', "Nomor Dokumen ")->getStyle('E5:F5')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('E6:F6')->setCellValue('E6', "Revisi ")->getStyle('E6:F6')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('E7:F7')->setCellValue('E7', "Hal ")->getStyle('E7:F7')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('G5:K5')->setCellValue('G5', ": ???")->getStyle('G5:K5')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('G6:K6')->setCellValue('G6', ": ???")->getStyle('G6:K6')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('G7:K7')->setCellValue('G7', ": 1 Dari : 1")->getStyle('G7:K7')->applyFromArray($this->styling_header_left(false, 9, ['outline']));
        $sheet->mergeCells('L5:N7')->setCellValue('L5', " ")->getStyle('L5:N7')->applyFromArray($this->styling_header_center(true, 12, ['outline']));

        $sheet->mergeCells('E9:K9')->setCellValue('E9', "BERITA ACARA SERAH TERIMA")->getStyle('E9:K9')->applyFromArray($this->styling_header_center(false, 12, []));
        $sheet->mergeCells('E10:K10')->setCellValue('E10', "No. ________________________")->getStyle('E10:K10')->applyFromArray($this->styling_header_center(false, 12, []));
        // END HEADER

        // BODY
        $sheet->mergeCells('B12:N13')->setCellValue('B12', "Pada hari ini Jumat tanggal 4 bulan Januari tahun 2022 , kami yang bertanda tangan dibawah ini :")->getStyle('B12:N13')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));

        $sheet->mergeCells('C15:E15')->setCellValue('C15', "Nama")->getStyle('C15:E15')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C16:E16')->setCellValue('C16', "Jabatan")->getStyle('C16:E16')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C17:E17')->setCellValue('C17', "Nama Badan Usaha")->getStyle('C17:E17')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C18:E18')->setCellValue('C18', "Alamat")->getStyle('C18:E18')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F15', ":")->getStyle('F15')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F16', ":")->getStyle('F16')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F17', ":")->getStyle('F17')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F18', ":")->getStyle('F18')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G15:N15')->setCellValue('G15', "???")->getStyle('G15:N15')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G16:N16')->setCellValue('G16', "???")->getStyle('G16:N16')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G17:N17')->setCellValue('G17', "???")->getStyle('G17:N17')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G18:N18')->setCellValue('G18', "???")->getStyle('G18:N18')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('B20', "Selanjutnya disebut sebagai Pemberi Tugas,")->getStyle('B20')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C22:E22')->setCellValue('C22', "Nama")->getStyle('C22:E22')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C23:E23')->setCellValue('C23', "Jabatan")->getStyle('C23:E23')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C24:E24')->setCellValue('C24', "Nama Badan Usaha")->getStyle('C24:E24')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('C25:E25')->setCellValue('C25', "Alamat")->getStyle('C25:E25')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F22', ":")->getStyle('F22')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F23', ":")->getStyle('F23')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F24', ":")->getStyle('F24')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('F25', ":")->getStyle('F25')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G22:N22')->setCellValue('G22', "???")->getStyle('G22:N22')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G23:N23')->setCellValue('G23', "???")->getStyle('G23:N23')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G24:N24')->setCellValue('G24', "???")->getStyle('G24:N24')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->mergeCells('G25:N25')->setCellValue('G25', "???")->getStyle('G25:N25')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));
        $sheet->setCellValue('B20', "Selanjutnya disebut sebagai Penerima Tugas,")->getStyle('B20')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false));

        $project = "Bibit Pohon Transplant";
        $sheet->mergeCells('B29:N30')->setCellValue('B29', "Kedua belah pihak telah setuju dan sepakat untuk melakukan Serah Terima Pekerjaan ke 1 untuk Pekerjaan Pengadaan " . $project . " sebagai berikut :")->getStyle('B29:N30')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B32', "Pasal 1")->getStyle('B32')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', '00FFFFFF', false));
        $no_pekerjaan = ".............";
        $date_pasal1 = ".................";
        $sheet->mergeCells('B33:N34')->setCellValue('B33', "Penerima Tugas menyerahkan kepada Pemberi Tugas dan Pemberi Tugas menyatakan menerima dari Penerima Tugas seluruh hasil Pekerjaan Pelaksanaan berdasarkan Berita Acara Pemeriksaan Pekerjaan Selesai Membangun nomor " . $no_pekerjaan . "  tanggal " . $date_pasal1 . " .")->getStyle('B33:N34')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B36', "Pasal 2")->getStyle('B36')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', '00FFFFFF', false));
        $no_spk = ".............";
        $date_pasal2 = ".................";
        $sheet->mergeCells('B37:N38')->setCellValue('B37', "Penyerahan sebagaimana dimaksud dalam pasal 1 berupa lingkup pekerjaan sesuai Surat Perintah Kerja No. SPK   " . $no_spk . "tanggal " . $date_pasal2)->getStyle('B37:N38')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B40', "Pasal 3")->getStyle('B40')->applyFromArray($this->styling_body_left(true, 12, [], 'FF000000', '00FFFFFF', false));
        $jml_hari = "....................";
        $sheet->mergeCells('B41:N41')->setCellValue('B41', "Penerima Tugas tetap bertanggung jawab terhadap kekurangan atau penyempurnaan Pekerjaan sampai dengan masa pemeliharaan berakhir atau selama " . $jml_hari . " hari sejak tanggal Berita Acara Serah Terima) ditandatangani.")->getStyle('B41:N41')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);

        $sheet->mergeCells('B43:N44')->setCellValue('B43', "Demikian Berita Acara Serah Terima Pekerjaan Yang Pertama (BAST 1) ini dibuat rangkap 2 (dua) dengan bunyi dan kekuatan hukum yang sama.")->getStyle('B43:N44')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);
        $date_BA = "4 Januari 2022";
        $sheet->mergeCells('B48:E48')->setCellValue('B48', "Jakarta, " . $date_BA . ".")->getStyle('B48:E48')->applyFromArray($this->styling_body_left(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);

        $sheet->mergeCells('B50:D50')->setCellValue('B50', "Penerima Tugas")->getStyle('B50:D50')->applyFromArray($this->styling_body_center(true, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('B51:D51')->setCellValue('B51', "PT Mitra Bakti UT")->getStyle('B51:D51')->applyFromArray($this->styling_body_center(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('B56:D56')->setCellValue('B56', "Zaenal Muharom")->getStyle('B56:D56')->applyFromArray($this->styling_body_center(true, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);
        $sheet->mergeCells('B57:D57')->setCellValue('B57', "Direktur ")->getStyle('B57:D57')->applyFromArray($this->styling_body_center(false, 12, [], 'FF000000', '00FFFFFF', false))->getAlignment()->setWrapText(true);
        // END BODY

        $fileName = 'TESTING';
        $path = 'uploads/project/bast/' . $fileName . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return site_url($path);
    }
}
