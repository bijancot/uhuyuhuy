<?php defined('BASEPATH') OR exit('No direct script access allowed');
    class Datefunction{
        public function getMonth(){
            return array(
                '1'     => 'Januari',
                '2'     => 'Februari',
                '3'     => 'Maret',
                '4'     => 'April',
                '5'     => 'Mei',
                '6'     => 'Juni',
                '7'     => 'Juli',
                '8'     => 'Agustus',
                '9'     => 'September',
                '10'    => 'Oktober',
                '11'    => 'November',
                '12'    => 'Desember'
            );
        }
        public function getMonthRomawi(){
            return array(
                '1'     => 'I',
                '2'     => 'II',
                '3'     => 'III',
                '4'     => 'IV',
                '5'     => 'V',
                '6'     => 'VI',
                '7'     => 'VII',
                '8'     => 'VIII',
                '9'     => 'IX',
                '10'    => 'X',
                '11'    => 'XI',
                '12'    => 'XII'
            );
        }
        public function getRomawiMonth(){
            return array(
                'I'     => '1',
                'II'    => '2',
                'III'   => '3',
                'IV'    => '4',
                'V'     => '5',
                'VI'    => '6',
                'VII'   => '7',
                'VIII'  => '8',
                'IX'    => '9',
                'X'     => '10',
                'XI'    => '11',
                'XII'   => '12'
            );
        }
        public function getDateLatin(){
            return array(
                '1'     => 'Satu',
                '2'     => 'Dua',
                '3'     => 'Tiga',
                '4'     => 'Empat',
                '5'     => 'Lima',
                '6'     => 'Enam',
                '7'     => 'Tujuh',
                '8'     => 'Delapan',
                '9'     => 'Sembilan',
                '10'    => 'Sepuluh',
                '11'    => 'Sebelas',
                '12'    => 'Dua belas',
                '13'    => 'Tiga belas',
                '14'    => 'Empat belas',
                '15'    => 'Lima belas',
                '16'    => 'Enam belas',
                '17'    => 'Tujuh belas',
                '18'    => 'Delapan belas',
                '19'    => 'Sembilan belas',
                '20'    => 'Dua puluh',
                '21'    => 'Dua puluh satu',
                '22'    => 'Dua puluh dua',
                '23'    => 'Dua puluh tiga',
                '24'    => 'Dua puluh empat',
                '25'    => 'Dua puluh lima',
                '26'    => 'Dua puluh enam',
                '27'    => 'Dua puluh tujuh',
                '28'    => 'Dua puluh delapan',
                '29'    => 'Dua puluh sembilan',
                '30'    => 'Tiga puluh',
                '31'    => 'Tiga puluh satu'
            );
        }
    }
?>