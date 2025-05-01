<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

class LectureTransactionEtoro {
    public static function Lire ($relever) {
        $inputFileName = 'chemin/vers/ton_fichier.xls';

        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        
        foreach ($data as $row) {
            print_r($row);
        }
    }
}