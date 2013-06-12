<?php
require_once 'lib/datei.inc.php';
require_once 'lib/classes/StudipDocument.class.php';

class StudipContentIndexerDocuments
{
    private static $instance;
    private $config = array();

    public function getInstance() {
        if (is_null(self::$instance)) {
            $me = __CLASS__;
            self::$instance = new $me();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->restoreConfig() || $this->setDefaultConfig();
    }

    function getConfig()
    {
        return $this->config;
    }

    function setConfig($config)
    {
        $this->config = $config;
    }

    function setDefaultConfig(){
        $this->config['pdf']['enabled'] = true;
        $this->config['pdf']['path']['pdfinfo'] = "/usr/bin/pdfinfo";
        $this->config['pdf']['path']['pdftotext'] = "/usr/bin/pdftotext";
        $this->config['doc']['enabled'] = true;
        $this->config['doc']['path']['catdoc'] = "/usr/local/bin/catdoc";
        $this->config['ppt']['enabled'] = true;
        $this->config['ppt']['path']['catppt'] = "/usr/local/bin/catppt";
        $this->config['xls']['enabled'] = true;
        $this->config['xls']['path']['xls2csv'] = "/usr/local/bin/xls2csv";
        $this->config['odt']['enabled'] = true;
        $this->config['odt']['path']['odt2txt'] = "/usr/local/bin/odt2txt";
        $this->config['txt']['enabled'] = true;
        $this->config['docx']['enabled'] = true;
        $this->config['pptx']['enabled'] = true;
        $this->config['xlsx']['enabled'] = true;
        $this->config['rtf']['same_as'] = 'doc';
        $this->config['csv']['same_as'] = 'txt';
        $this->config['sxw']['same_as'] = 'odt';
        $this->config['ods']['same_as'] = 'odt';
        $this->config['sxc']['same_as'] = 'odt';
    }

    function getEnabledFileTypes(){
        $ret = array();
        foreach($this->config as $type => $info){
            if($info['enabled'] || $this->config[$info['same_as']]['enabled']){
                $ret[] = $type;
            }
        }
        return $ret;
    }

    function checkConfig(){
        $ret = array();
        foreach($this->config as $type => $info){
            if(is_array($info['path'])){
                foreach($info['path'] as $tool => $path) {
                    $ret[$tool] = $path . ' - ' . (is_executable($path) ? 'ok' : 'nicht ok');
                    $this->config[$type]['enabled'] = $this->config[$type]['enabled'] && is_executable($path);
                }
            }
        }
        return $ret;
    }

    function restoreConfig(){
        $config = DBManager::get()
                ->query("SELECT comment FROM config WHERE field = 'CONFIG_" . __CLASS__ . "' AND is_default=1")
                ->fetchColumn();
        $this->config = unserialize($config);
        return $this->config != false;
    }

    function storeConfig(){
        $config = serialize($this->config);
        $field = "CONFIG_" . __CLASS__;
        $st = DBManager::get()
        ->prepare("REPLACE INTO config (config_id, field, value, is_default, `type`, `range`, chdate, comment)
            VALUES (?,?,'do not edit',1,'string','global',UNIX_TIMESTAMP(),?)");
        return $st->execute(array(md5($field), $field, $config));
    }

    function create($document_id){
        $db = DBManager::get();
        $doc = new StudipDocument($document_id);
        if(!$doc->isNew() && $doc->url == '' && $doc->name !== null && get_object_type($doc->seminar_id, array('sem','inst'))) {
            $chdate = $db->query("SELECT chdate FROM content_search_dokumente_index WHERE dokument_id = " . $db->quote($doc->getId()))->fetchColumn();
            if ($chdate < $doc->getValue('chdate')) {
                $file_path = get_upload_file_path($document_id);
                $ext = strtolower(substr(strrchr($doc->getValue('filename'),'.'),1));
                if(in_array($ext, $this->getEnabledFileTypes())){
                    $content_func = "getExtractedContent" . ($this->config[$ext]['same_as'] ? $this->config[$ext]['same_as'] : $ext);
                    $content = (string)$this->$content_func($file_path);
                    $max_size = $db->query("SELECT @@max_allowed_packet")->fetchColumn();
                    if (strlen($content) >  $max_size*0.75) {
                        $content = '';
                    }
                } else {
                    $content = '';
                }
                $st = $db
                    ->prepare("REPLACE INTO content_search_dokumente_index
                     (`dokument_id`, `name`, `description`, `filename`,`filetype`, `owner`, `chdate`, `object_id`, `content`)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                return $st->execute(array($doc->getId(),
                                         $doc->getValue('name'),
                                         $doc->getValue('description'),
                                         $doc->getValue('filename'),
                                         str_pad($ext,6,"_",STR_PAD_BOTH),
                                         get_fullname($doc->getValue('user_id'), 'no_title'),
                                         $doc->getValue('chdate'),
                                         $doc->getValue('seminar_id'),
                                         $content));
            }
        }
    }

    function delete($document_id){
        $db = DBManager::get();
        return $db->exec("DELETE FROM content_search_dokumente_index WHERE dokument_id=" . $db->quote($document_id));
    }

    function getExtractedContentPDF($file_path){
        $content = false;
        $config = $this->config['pdf'];
        $temp = $GLOBALS['TMP_PATH'] . '/'. md5($file_path);
        @unlink ($temp);
        if (@file_exists($file_path)){
            $cmd =  $config['path']['pdfinfo'] . ' ' . escapeshellarg($file_path);
            $res = array();
            exec($cmd, $res);
            if (count($res)){
                foreach($res as $line){
                    $parts = explode(':', $line);
                    if (count($parts) > 1 && trim($parts[0]))    {
                        $pdfinfo[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }
                }
            }
            if (intval($pdfinfo['pages'])){
                $res = array();
                $cmd = $config['path']['pdftotext'] . ' -q -enc Latin1 ' . escapeshellarg($file_path) .' ' . $temp;
                exec($cmd, $res);
                if (@file_exists($temp)){
                    $content = file_get_contents($temp);
                }
                @unlink($temp);
            }
        }
        return $content;
    }

    function getExtractedContentDOC($file_path){
        $content = false;
        $config = $this->config['doc'];
        if (@file_exists($file_path)){
            $cmd = $config['path']['catdoc'] . ' -w -a -scp1252 -dcp1252 ' .escapeshellarg($file_path);
            exec($cmd, $res);
            $content = implode(chr(10),$res);
        }
        return $content;
    }

    function getExtractedContentPPT($file_path){
        $content = false;
        $config = $this->config['ppt'];
        if (@file_exists($file_path)){
            $cmd = $config['path']['catppt'] . ' -scp1252 -dcp1252 ' .escapeshellarg($file_path);
            exec($cmd, $res);
            $content = implode(chr(10),$res);
        }
        return $content;
    }

    function getExtractedContentXLS($file_path){
        $content = false;
        $config = $this->config['xls'];
        if (@file_exists($file_path)){
            $cmd = $config['path']['xls2csv'] . ' -scp1252 -dcp1252 ' .escapeshellarg($file_path);
            exec($cmd, $res);
            $content = implode(chr(10),$res);
        }
        return $content;
    }

    function getExtractedContentODT($file_path){
        $content = false;
        $config = $this->config['odt'];
        if (@file_exists($file_path)){
            $cmd = $config['path']['odt2txt'] . ' --encoding=cp1252 --width=-1 ' .escapeshellarg($file_path);
            exec($cmd, $res);
            $content = implode(chr(10),$res);
        }
        return $content;
    }

    function getExtractedContentTXT($file_path){
        return @file_get_contents($file_path);
    }

    function getExtractedContentDOCX($file_path)
    {
        $xslt = <<<XML
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
    xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <xsl:output method="text" />

  <xsl:template match="/">
    <xsl:apply-templates select="//w:body" />
  </xsl:template>

  <xsl:template match="w:body">
    <xsl:apply-templates />
  </xsl:template>

  <xsl:template match="w:p">
    <xsl:text>&#xa;</xsl:text>
    <xsl:apply-templates select="w:r" />
  </xsl:template>

  <xsl:template match="w:r">
    <xsl:for-each select="w:t">
        <xsl:value-of select="." />
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
XML;
        $content = false;
        if (@file_exists($file_path)) {
            $tmpdir = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid());
            @mkdir($tmpdir);
            unzip_file($file_path, $tmpdir);
            if (@file_exists($tmpdir . '/word/document.xml')) {
                $xh = new XSLTProcessor();
                $xml_doc = new DOMDocument();
                $xml_doc->load($tmpdir . '/word/document.xml');
                $xsl_doc = new DOMDocument();
                $xsl_doc->loadXML($xslt);
                $xh->importStylesheet($xsl_doc);
                $content = studip_utf8decode(trim($xh->transformToXML($xml_doc)));
            }
            rmdirr($tmpdir);
        }
        return $content;
    }

    function getExtractedContentPPTX($file_path)
    {
        $xslt = <<<XML
<xsl:stylesheet
    xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
    xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
    xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
    <xsl:output method="text"/>
    <xsl:template match="a:r">

    <xsl:text>&#xa;</xsl:text>
        <xsl:for-each select="a:t">
                <xsl:text>&#x20;</xsl:text>
            <xsl:value-of select="."/>
        </xsl:for-each>
    <xsl:text>&#xa;</xsl:text>
    </xsl:template>
</xsl:stylesheet>
XML;
        $content = false;
        if (@file_exists($file_path)) {
            $tmpdir = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid());
            @mkdir($tmpdir);
            unzip_file($file_path, $tmpdir);
            if (@file_exists($tmpdir . '/ppt/slides/slide1.xml')) {
                $xh = new XSLTProcessor();
                $xsl_doc = new DOMDocument();
                $xsl_doc->loadXML($xslt);
                $xh->importStylesheet($xsl_doc);
                $slides = glob($tmpdir . '/ppt/slides/*.xml');
                natsort($slides);
                foreach ($slides as $slide) {
                    $xml_doc = new DOMDocument();
                    $xml_doc->load($slide);
                    $content.= "\n\n-- \n\n";
                    $content.= studip_utf8decode(trim($xh->transformToXML($xml_doc)));
                }
            }
            rmdirr($tmpdir);
        }
        return $content;
    }

        function getExtractedContentXLSX($file_path)
    {
        $xslt = <<<XML
<xsl:stylesheet
  xmlns:sps="http://schemas.openxmlformats.org/spreadsheetml/2006/5/main"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
  xmlns:ConvJulianDate="http://mycompany.com/mynamespace"
  xmlns:msxsl="urn:schemas-microsoft-com:xslt"
  extension-element-prefixes="msxsl"
  exclude-result-prefixes="ConvJulianDate"
  version="1.0" >
  <xsl:output method="text" standalone="yes"  omit-xml-declaration="no" encoding="utf-8"/>
  <xsl:variable name="blank" select="' '"/>
    <xsl:template match="/">
        <xsl:apply-templates></xsl:apply-templates>
    </xsl:template>
    <xsl:template match="row">
        <xsl:for-each select="c">
            <xsl:if test="count(@t)=0">
                <xsl:value-of select="concat(v,\$blank)"/>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="si">
        <xsl:for-each select="t">
            <xsl:value-of select="concat(.,\$blank)"/>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="is">
        <xsl:for-each select="t">
            <xsl:value-of select="concat(.,\$blank)"/>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
XML;
        $content = false;
        if (@file_exists($file_path)) {
            $tmpdir = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid());
            @mkdir($tmpdir);
            unzip_file($file_path, $tmpdir);
            if (@file_exists($tmpdir . '/xl/sharedStrings.xml')) {
                $xh = new XSLTProcessor();
                $xml_doc = new DOMDocument();
                $xml_doc->load($tmpdir . '/xl/sharedStrings.xml');
                $xsl_doc = new DOMDocument();
                $xsl_doc->loadXML($xslt);
                $xh->importStylesheet($xsl_doc);
                $content = studip_utf8decode(trim($xh->transformToXML($xml_doc)));
            }
            rmdirr($tmpdir);
        }
        return $content;
    }
}
