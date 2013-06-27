<?php
require 'StudipContentIndexerDocuments.php';

class StudipContentSearch extends StudIPPlugin implements SystemPlugin {

    public static $config = array();

    function __construct() {

        parent::__construct();
        $this->restoreConfig();
        $this->me = strtolower(__CLASS__);
        // set up tab navigation
        $navigation = new Navigation($this->getDisplaytitle());
        $subnav1 = new Navigation($this->getDisplaytitle());
        $subnav1->setURL(PluginEngine::getURL("$this->me/search"));
        $navigation->addSubNavigation('search', $subnav1);
        Navigation::addItem("/$this->me", $navigation);
        Navigation::addItem("/start/search/$this->me", $subnav1);
        Navigation::addItem("/search/$this->me", $navigation);
        if($GLOBALS["perm"]->have_perm("root")){
            $subnav2 = new Navigation(_("Konfiguration"));
            $subnav2->setURL(PluginEngine::getURL("$this->me/configuration"));
            $navigation->addSubNavigation('config', $subnav2);
        }
        NotificationCenter::addObserver($this, 'observeDocuments', 'DocumentDidCreate');
        NotificationCenter::addObserver($this, 'observeDocuments', 'DocumentDidUpdate');
        NotificationCenter::addObserver($this, 'observeDocuments', 'DocumentDidDelete');
    }

    function observeDocuments($event, $document, $data){
        $indexer = StudipContentIndexerDocuments::getInstance();
        if($event == 'DocumentDidCreate' || $event == 'DocumentDidUpdate') {
            $indexer->create($document->getId());
        }
        if($event == 'DocumentDidDelete') {
            $indexer->delete($document->getId());
        }
    }

    function restoreConfig(){
        $config = DBManager::get()
        ->query("SELECT comment FROM config WHERE field = 'CONFIG_" . $this->getPluginName() . "' AND is_default=1")
        ->fetchColumn();
        self::$config = unserialize($config);
        return self::$config != false;
    }

    function storeConfig(){
        $config = serialize(self::$config);
        $field = "CONFIG_" . $this->getPluginName();
        $st = DBManager::get()
        ->prepare("REPLACE INTO config (config_id, field, value, is_default, type, range, chdate, comment)
            VALUES (?,?,'do not edit',1,'string','global',UNIX_TIMESTAMP(),?)");
        return $st->execute(array(md5($field), $field, $config));
    }

    function getPluginname() {
        return 'StudipContentSearch';
    }

    function getDisplayTitle() {
        return _("Dateisuche");
    }

    function updateIndex($document_id) {
        $indexer = StudipContentIndexerDocuments::getInstance();
        return $indexer->create($document_id);
    }

    function deleteIndex($document_id) {
        $indexer = StudipContentIndexerDocuments::getInstance();
        return $indexer->delete($document_id);
    }

    /**
    * This method dispatches and displays all actions. It uses the template
    * method design pattern, so you may want to implement the methods #route
    * and/or #display to adapt to your needs.
    *
    * @param  string  the part of the dispatch path, that were not consumed yet
    *
    * @return void
    */
    function perform($unconsumed_path) {
        if(!$unconsumed_path){
            header("Location: " . PluginEngine::getUrl($this), 302);
            return false;
        }
        $trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, null, 'search');
        $dispatcher->current_plugin = $this;
        $dispatcher->dispatch($unconsumed_path);

    }

}
