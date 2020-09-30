<?php
   

/**
 * CLASE PARA spreadsheet manjeo de Excel.
 * CLASE NO MEJORADA.
 * Tablespace de PHP no permiten funcionamiento de otras clases. 
 **/

require_once dirname(__FILE__).'/../plugins/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class printerEXCEL
{
    private $base_url = WEB_ROOT;
    
    function __construct()
    {
        set_time_limit(-1);   
    }
    
    public function arrayToExcel($FileName, $HeadersExcel, $DataArray)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $HeadersExcel,   // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
                             //    we want to set these values (default is A1)
            );

        $spreadsheet->getActiveSheet()->fromArray($DataArray, null, 'A2');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$FileName.'.xls"');
        header('Cache-Control: max-age=0');

          // Do your stuff here
         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');

        ob_end_clean();
        $writer->save("php://output");

        exit();

    }
}

?>
