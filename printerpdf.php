<?php
   

/**
 * CLASE PARA IMPRIMIR PDF DE MANERA MASIVA, COMPRESIÓN Y DESCARGA DIRECTA DE ARCHIVOS.
 * CLASE NO MEJORADA.
 * Tablespace de PHP no permiten funcionamiento de otras clases. 
 **/
require_once dirname(__FILE__).'/../plugins/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// require_once dirname(__FILE__).'/../plugins/html2pdf/vendor/autoload.php';

require_once dirname(__FILE__).'/../plugins/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter; 
// set_time_limit(-1);

class printerPDF
{
    private $base_url = WEB_ROOT;
    
    function __construct()
    {
        set_time_limit(-1);   
    }
    public function maker_pdfHTML2PDF($content, $namefile, $type = 'P', $size = 'A4'){ 
        ob_get_clean();
        ob_start();
        $html2pdf = new HTML2PDF($type, $size, 'fr');
        $html2pdf->WriteHTML($content);
        $html2pdf->output(str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/'.$namefile.'.pdf','F');
        return array('fileName' => $namefile.'.pdf');
    }
    public function simple_pdf($content, $namefile, $type = 'P', $size = 'A4') {
        try {
            $namefile = ($namefile!='') ? $namefile : 'Reporte Eplux' ;
            ob_get_clean();
            ob_start();
            $html2pdf = new HTML2PDF($type, $size, 'fr');
            // $html2pdf = new HTML2PDF($type, "LETTER", "es", array(5, 5, 5, 5), "UTF-8");
            // $html2pdf->pdf->SetDisplayMode('fullpage');
            // $html2pdf->setModeDebug(); // Habilitar para mostrar errorres 
            $html2pdf->writeHTML($content);
            $html2pdf->output($namefile.'.pdf','I');
        } catch (Html2PdfException $e) {
            // $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
    }
    public function maker_pdfDOMPDF($content, $namefile){
        ob_start();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/'.$namefile.'.pdf','F'); 
        // die;
    }
    public function call_class(){
        require_once dirname(__FILE__).'/../main.php';
        spl_autoload_register(function($class) {
            $filename = DOC_ROOT . '/class/' . $class . '.php';
            require $filename; 
        });
        
    }
    public function compressFiles($Arrayfile){
        if(count($Arrayfile)>0){
            $zipName = "AdjuntoReportes.zip";
            $rootPath = str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/';
            $zip = new ZipArchive();
            $zip->open($zipName, ZipArchive::CREATE);
            $zip->addEmptyDir($rootPath);
            foreach ($Arrayfile as $file) {
                $zip->addFile($file);
            }
            $result = $zip->close();
            return array('fileName' => $zipName, 'status' => $result, 'routeFolder'=>$rootPath);
        } else {
            return false;
        }
    }
    public function compressFolder($zipName='Reportes_'){
        $rootPath = str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/';
        $zipName = $zipName.'.zip';

        $zip = new ZipArchive(); 
        $zip->open($rootPath.$zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE); 
        $filesToDelete = array(); 
         /** @var SplFileInfo[] $files */ 
        $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY ); 
        foreach ($files as $name => $file) { 
            // echo $name." Dir:  ".$file."<br>";
            if (!$file->isDir()) { 
                $filePath = $file->getRealPath(); 
                $relativePath = substr($filePath, strlen($rootPath) + 1); 
                // if ($file->getExtension() == 'pdf' || $file->getExtension() == '.pdf') { //Agregar unicamente PDF al zip
                    $zip->addFile($filePath, $relativePath);
                // }                
                if ($file->getFilename() != 'important.txt') { //Excluyo este archivo de ser borrado
                    $filesToDelete[] = $filePath; 
                }
            } 
        }
        $result = $zip->close(); 
        foreach ($filesToDelete as $file) { 
            unlink($file); 
        }
        return array('fileName' => $zipName, 'status' => $result, 'routeFolder'=>$rootPath);
    }
    public function downloadZipFile($file){
        set_time_limit(-1);
        sleep(12);
        $filename = $file;
        $filepath = str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/';
        if(!empty($filename) && file_exists($filepath)){
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"".$filename."\"");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($filepath.$filename));
            ob_end_flush();
            @readfile($filepath.$filename);
        } else {
            return "No existe el archivo";
        }
    }
    public function downloadFile($file, $ClientFolder){
        $url = str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/'.$file;
        // if ($dir['error']=='') {
            if (file_exists(str_replace('\\', '/', dirname(__FILE__)).'/../soportes/public/'.$file)) {
                $source = file_get_contents($url);
                file_put_contents($ClientFolder.$file, $source);
                unlink($url);
            } else {
                echo "No existe el archivo";
            }
        // } else{
        //     echo "No existe la carpeta";
        // }
    }
    public function makeDIR(){
        $result = array('error'=>'');
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $userAgent = strtolower ($userAgent);
        if(strpos($userAgent, "windows") !== false) {
            // print ("<P>OS Client: Windows: ".$_SERVER['REMOTE_ADDR']."<br>");
            $baser = 'C:/reportes_eplux/';
            if (!file_exists($baser)) {
                mkdir($baser, 0777, true);
                $result['folder_make'] = $baser;
            } else{
                $result['folder_make'] = $baser;
            }
            // file_put_contents('D:/reportes/d.pdf', $source);
        } 
        if(strpos($userAgent, "linux") !== false) { 
            // print ("<P>OS Client: Linux". $_SERVER['REMOTE_ADDR']."<br>");
            $baser = '/home/reportes_eplux/';
            if (!file_exists($baser)) {
                mkdir($baser, 0777, true);
                $result['folder_make'] = $baser;
            } else{
                $result['folder_make'] = $baser;
            }
        }
        $PHP_OS = PHP_OS;
        $result['server_os'] = $PHP_OS;
        return $result;
        // print ("OS Server: ".$PHP_OS." - ". $_SERVER['SERVER_NAME']);

    }

    public function automatic_make_pfd($content, $namefile) {
        $this->maker_pdfHTML2PDF($content, $namefile);
        $this->compressFiles();
    }



}

?>
