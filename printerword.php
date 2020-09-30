<?php
   

/**
 * CLASE PARA IMPRIMIR WORD DE MANERA MASIVA, COMPRESIÓN Y DESCARGA DIRECTA DE ARCHIVOS.
 * CLASE NO MEJORADA.
 * Tablespace de PHP no permiten funcionamiento de otras clases. 
 **/

// require_once dirname(__FILE__).'/../plugins/vendor/phpoffice/phpword/bootstrap.php'; // Cargar segun necesidad
// require dirname(__FILE__).'/plugins/vendor/phpoffice/phpword/src/PhpWord/IOFactory.php'; // Cargar segun necesidad
// require_once dirname(__FILE__).'/plugins/PHPWord/index.php';  // Cargar segun necesidad
require_once  dirname(__FILE__).'/../plugins/PHPWord/vendor/autoload.php';
// use PhpOffice\PhpWord\PhpWord(); // Crear tablespace si no funciona la clase o hay conflicto con los nombres de las clases


class printerWORD
{
    
    function __construct()
    {
        date_default_timezone_set('UTC');
        error_reporting(E_ALL);
        define('CLI', (PHP_SAPI == 'cli') ? true : false);
        define('EOL', CLI ? PHP_EOL : '<br />');
        set_time_limit(-1);
    }
    public function make_WORD(){
        // Código unicamente de prueba, carga de Plantillas
        require_once ('../main.php');
        ob_get_clean();
        ob_start();
        $PHPWord = new \PhpOffice\PhpWord\PhpWord();
        $document = $PHPWord->loadTemplate('templates/requerimiento_contrato.docx');
        $document->setValue('Value1', 'Great');

        $section = $PHPWord->createSection();
        $section->addText('Hello World!');
        $section->addTextBreak(2);

        $document->setValue('Value2', $section);

        $document->save('test.docx');
    }
    public function simpleDOC_template($template, $dataArray, $filename='Doc_Eplux'){
        // Funcion para cargar a plantilla datos de un array tipo ('Valor/Etiqueta()' => 'Valor(specialchart)')
        ob_get_clean();
        ob_start();
        $FolderFiles = 'templates/';
        $newDoc = $filename."_".date('Y-m-d').'.docx';
        $result = array('error' => true, 'msg' => '', 'filename' =>$newDoc);
        $PHPWord = new \PhpOffice\PhpWord\PhpWord();
        if (file_exists($FolderFiles.$template)) {
            $document = $PHPWord->loadTemplate($FolderFiles.$template);
            $labelRows = count($dataArray);
            if ($labelRows > 0 ) {
                foreach ($dataArray as $key => $value) {
                    $document->setValue($key, htmlspecialchars($value));
                }
                $result['msg'] = 'success';
                $result['error'] = false;
                $result['labelRows'] = $labelRows;
            } else{
                $result['msg'] = 'Array data is null';
            }
        } else {
            $result['msg'] = 'Template no found';
        }
        $document->saveAs('soportes/'.$newDoc);
        return $result;

    }
    public function htmlDOC_template($template, $dataArray='', $filename='Doc_Eplux') {
        // echo date('H:i:s'), ' Create new PhpWord object', EOL;
        // Funcion para cargar a plantilla datos de un array tipo ('Valor/Etiqueta' => 'Valor(HTML/XML text)')
        ob_get_clean();
        ob_start();
        $FolderFiles = 'templates/';
        $newDoc = $filename."_".date('Y-m-d').'.docx';
        $result = array('error' => true, 'msg' => '', 'filename' =>$newDoc);
        // Template Processor
        if (file_exists($FolderFiles.$template)) {
            $phpWord = new \PhpOffice\PhpWord\TemplateProcessor('templates/'.$template);
            $labelRows = count($dataArray);
            if ($labelRows > 0) {
                foreach ($dataArray as $key => $value) {
                    $HTMLtoOpenXML = new HTMLtoOpenXML\Parser();

                    \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(false);
                    // $toOpenXML = $HTMLtoOpenXML->fromHTML($value);
                    $toOpenXML = $HTMLtoOpenXML->fromHTML(str_replace("<p>&nbsp;</p>","<w:br/>", $value));
                    $phpWord->setValue($key, $toOpenXML);
                }
                // $phpWord->setValue('header', $html);
                $phpWord->setValue('footer', 'Documento generado por Eplux - Grandtek, '.date('Y'));
                $result['msg'] = 'success';
                $result['error'] = false;
                $result['labelRows'] = $labelRows;
            } else{
                $result['msg'] = 'Array data is null'; 
            }
        } else {
            $result['msg'] = 'Template no found';
        }
        $phpWord->saveAs('soportes/'.$newDoc);
        // $this->download($result['filename']); //Descargar nuevo documento
        return $result;
    }
    public function htmlSection_Templates($template='', $dataArray='', $filename='Doc_Eplux'){
        // Array tipo array('Label_Name'=>Value_Label);
       ob_get_clean();
        ob_start();
        $FolderFiles = 'templates/';
        $newDoc = $filename."_".date('Y-m-d').'.docx';
        $result = array('error' => true, 'msg' => '', 'filename' =>$newDoc);
        // Template Processor
        if (file_exists($FolderFiles.$template)) {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('templates/'.$template);
            $phpWord = new \PhpOffice\PhpWord\TemplateProcessor('templates/'.$template);
            $labelRows = count($dataArray);
            if ($labelRows > 0) {
                foreach ($dataArray as $block => $value) {
                    $phpWord->cloneBlock($block, $value,1);
                }
                $result['msg'] = 'success';
                $result['error'] = false;
                $result['labelRows'] = $labelRows;
            } else{ $result['msg'] = 'Array data is null'; }
        } else { $result['msg'] = 'Template no found'; }
        $phpWord->saveAs('soportes/'.$newDoc);
        // echo getEndingNotes(array('Word2007' => 'docx'), 'soportes/'.$newDoc);
        // $this->download($result['filename']); //Descargar nuevo documento
        return $result;
        // $templateProcessor->cloneBlock('CLONEME', 3);
        // $templateProcessor->deleteBlock('DELETEME');
        // $templateProcessor->saveAs('soportes/Sample_23_TemplateBlock.docx');
        
    }
    public function htmlTemplate_DOC($template, $dataArray='', $filename='Doc_Eplux'){
        // De plantilla HTML a nuevo documento DOCX
        $section = $pw->addSection();
        $filename = $filename."_".date('Y-m-d').".docx";
        $html = $html;
        $phpWord = \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
        $phpWord->saveAs('soportes/'.$filename);
    }
    function download($filename){
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        // $templateProcessor->saveAs("php://output");
    }

}

?>
