<?php
require "application.php";

require_once "lib/datei.inc.php";
require_once "lib/visual.inc.php";

class ConfigurationController extends ApplicationController
{
    function index_action()
    {
        $indexer = StudipContentIndexerDocuments::getInstance();
        if (Request::submitted('recreate_index')) {
            $num = $this->recreate_index(Request::int('truncate_index'));
            $this->flash_now('info', sprintf(_("Der Index wurde aktualisiert. %s Dokumente indiziert."), $num));
        }
        if (Request::submitted('revert_config')) {
            $indexer->setDefaultConfig();
            $indexer->checkConfig();
            $indexer->storeConfig();
            $this->flash_now('info', _("Die Konfiguration wurde zurück gesetzt."));
        }
        if (Request::submitted('check_config')) {
            $config = $indexer->getConfig();
            $new_config = Request::getArray('config');
            foreach ($new_config as $type => $settings) {
                $config[$type]['enabled'] = isset($settings['enabled']);
                if ($settings['path']) {
                    $config[$type]['path'] = $settings['path'];
                }
            }
            $config['txt']['enabled'] = isset($new_config['txt']['enabled']);
            $indexer->setConfig($config);
            $info = $indexer->checkConfig();
            $indexer->storeConfig();
            $this->flash_now('info', '<ul><li>'.join('</li><li>',$info).'</li></ul>');
        }
        $this->config = $indexer->getConfig();
        Navigation::activateItem("/search/{$this->plugin->me}/config");
    }

    function recreate_index($truncate = false)
    {
        ini_set('memory_limit', '256M');
        $db = DBManager::get();
        if ($truncate) {
            $db->exec("TRUNCATE TABLE content_search_dokumente_index");
        }
        $rs = $db->query("SELECT dokument_id FROM dokumente where seminar_id in (select seminar_id from seminare union select institut_id from Institute)");
        $num = 0;
        foreach($rs as $row){
            $num += $this->plugin->updateIndex($row['dokument_id']);
        }
        return $num;
    }
}