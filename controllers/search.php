<?php
require "application.php";

require_once "lib/datei.inc.php";
require_once "lib/visual.inc.php";

class SearchController extends ApplicationController
{
    function index_action(){

        if (!$_SESSION['_search_data']['sortby']){
            $_SESSION['_search_data']['sortby'] = 'chdate';
            $_SESSION['_search_data']['sortdir'] = 0;
        }
        if (!is_array($_SESSION['_search_data']['_search_only'])){
            $_SESSION['_search_data']['_search_only']['my_sem'] = 1;
            $_SESSION['_search_data']['_search_only']['ext']['all'] = 1;
        }
        if($this->do_sort){
            $sorter = new ArraySorter();
            $sorter->sort_field = $_SESSION['_search_data']['sortby'];
            $sorter->sort_flag = ($_SESSION['_search_data']['sortby'] == 'chdate' ? 'numeric' : 'string');
            $sorter->sort_dir = ($_SESSION['_search_data']['sortdir'] == 1 ? 'ASC' : 'DESC');
            $_SESSION['_search_data']['_search_result'] = $sorter->sort($_SESSION['_search_data']['_search_result']);
        }
        if(is_array($_SESSION['_search_data']['_search_result'])){
            $this->num_hits = count($_SESSION['_search_data']['_search_result']);
        }
        if ($_SESSION['_search_data']['_start_result'] < 1 || $_SESSION['_search_data']['_start_result'] > $this->num_hits){
            $_SESSION['_search_data']['_start_result'] = 1;
        }
        $this->search_data = $_SESSION['_search_data'];
        $this->extensions = $this->get_used_file_extensions();
        $this->get_infobox_content();
        Navigation::activateItem("/search/{$this->plugin->me}/search");
    }

    function perform_action(){
        if (!isset($_REQUEST['cancel_x']) && isset($_REQUEST['search_query'])){
            $_SESSION['_search_data']['_search_query'] = trim(stripslashes($_REQUEST['search_query']));
            $_SESSION['_search_data']['_search_only'] = $_REQUEST['search_only'];
            unset($_SESSION['_search_data']['_search_result']);
            $_SESSION['_search_data']['_start_result'] = 1;
            $this->do_sort = true;
        }
        if (isset($_REQUEST['cancel_x'])){
            unset($_SESSION['_search_data']['_search_query']);
            unset($_SESSION['_search_data']['_search_only']);
            unset($_SESSION['_search_data']['_search_result']);
            $_SESSION['_search_data']['_start_result'] = 1;
            unset($_SESSION['_search_data']['sortby']);
            unset($_SESSION['_search_data']['sortdir']);
        }
        if (!$_SESSION['_search_data']['_search_result'] && strlen($_SESSION['_search_data']['_search_query']) > 2){
            $_SESSION['_search_data']['_search_result'] = null;
            if (!$_SESSION['_search_data']['_search_only']['ext']['all'] && count($_SESSION['_search_data']['_search_only']['ext'])) {
                $search_exts = ' +(' . join(' ', array_map(create_function('$e','return str_pad($e,6,"_",STR_PAD_BOTH);'),array_keys($_SESSION['_search_data']['_search_only']['ext']))).')';
            }
            $object_ids = array();
            $search_for = '+('.$_SESSION['_search_data']['_search_query'] .')'. $search_exts;
            if(!$GLOBALS['perm']->have_perm('root')) {
                if($_SESSION['_search_data']['_search_only']['my_sem']){
                    $sql = "SELECT content_search_dokumente_index.*, 'sem' as object_type, 1 as access_granted,modules,su.status as object_perm, s.status as object_status FROM seminar_user su
                            INNER JOIN content_search_dokumente_index ON(seminar_id=object_id)
                            INNER JOIN seminare s ON s.seminar_id = su.seminar_id
                            WHERE su.user_id=? AND (MATCH(content_search_dokumente_index.name,description,filename,owner,content,filetype) AGAINST (? IN BOOLEAN MODE)) ";
                    $st = DbManager::get()->prepare($sql);
                    $st->execute(array($GLOBALS['user']->id, $search_for));
                    while($row = $st->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['_search_data']['_search_result'][$row['dokument_id']] = $row;
                        $object_ids[$row['object_id']]['docs'][] = $row['dokument_id'];
                        $object_ids[$row['object_id']]['status'] = $row['object_status'];
                        $object_ids[$row['object_id']]['type'] = $row['object_type'];
                        $object_ids[$row['object_id']]['modules'] = $row['modules'];
                        $object_ids[$row['object_id']]['user_status'] = $row['object_perm'];
                    }
                }
                if($_SESSION['_search_data']['_search_only']['public_sem']){
                    $sql = "SELECT content_search_dokumente_index.*, 'sem' as object_type, 0 as access_granted,modules,s.status as object_status FROM seminare s
                    INNER JOIN content_search_dokumente_index ON(seminar_id=object_id)
                        WHERE s.seminar_id NOT IN(?) AND s.visible=1 AND s.Lesezugriff IN(0,1) AND admission_type=0
                        AND (admission_endtime_sem = -1 OR admission_endtime_sem > UNIX_TIMESTAMP())
                        AND (admission_starttime = -1 OR admission_starttime < UNIX_TIMESTAMP())
                        AND (MATCH(content_search_dokumente_index.name,description,filename,owner,content,filetype) AGAINST (? IN BOOLEAN MODE))
                        ";
                    $st = DbManager::get()->prepare($sql);
                    $not_in = array_keys($object_ids);
                    if (!count($not_in)) {
                        $not_in = array('');
                    }
                    $st->execute(array($not_in, $search_for));
                    while($row = $st->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['_search_data']['_search_result'][$row['dokument_id']] = $row;
                        $object_ids[$row['object_id']]['docs'][] = $row['dokument_id'];
                        $object_ids[$row['object_id']]['status'] = $row['object_status'];
                        $object_ids[$row['object_id']]['type'] = $row['object_type'];
                        $object_ids[$row['object_id']]['modules'] = $row['modules'];
                    }
                }
                $sql = "SELECT content_search_dokumente_index.*, 'inst' as object_type, 1 as access_granted, inst_perms as object_perm,modules, i.type as object_status
                        FROM content_search_dokumente_index
                        INNER JOIN Institute i ON i.institut_id = object_id
                        LEFT JOIN user_inst ui ON ui.institut_id=i.institut_id AND ui.user_id=?
                        WHERE (MATCH(content_search_dokumente_index.name,description,filename,owner,content,filetype) AGAINST (? IN BOOLEAN MODE))";
                $st = DbManager::get()->prepare($sql);
                $st->execute(array($GLOBALS['user']->id, $search_for));
                while($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['_search_data']['_search_result'][$row['dokument_id']] = $row;
                    $object_ids[$row['object_id']]['docs'][] = $row['dokument_id'];
                    $object_ids[$row['object_id']]['status'] = $row['object_status'];
                    $object_ids[$row['object_id']]['type'] = $row['object_type'];
                    $object_ids[$row['object_id']]['modules'] = $row['modules'];
                    $object_ids[$row['object_id']]['user_status'] = $row['object_perm'];
                }
                $modules = new Modules();
                $user_domains = UserDomain::getUserDomainsForUser($GLOBALS['user']->id);
                foreach ($object_ids as $oid => $detail) {
                    //filter out hits from courses with not matching domains
                    if ($detail['type'] == 'sem' && !$detail['user_status']
                        && !SeminarCategories::GetByTypeId($detail["status"])->studygroup_mode
                        && count($user_domains) > 0) {
                        $seminar_domains = UserDomain::getUserDomainsForSeminar($oid);
                        $same_domain = count(array_intersect($seminar_domains, $user_domains)) > 0;
                        if (!$same_domain) {
                            foreach ($detail['docs'] as $doc_id) {
                                unset($_SESSION['_search_data']['_search_result'][$doc_id]);
                            }
                            continue;
                        }
                    }
                    //filter out hits in unreadable folders
                    $o_modules = $modules->getLocalModules($oid, $detail['type'] , $detail["modules"], $detail["status"]);
                    if ($o_modules['documents_folder_permissions']
                        || ($detail['type'] == 'sem' && StudipDocumentTree::ExistsGroupFolders($oid))) {
                        $must_have_perm = $detail['type'] == 'sem' ? 'tutor' : 'autor';
                        if ($GLOBALS['perm']->permissions[$detail['user_status']] < $GLOBALS['perm']->permissions[$must_have_perm]) {
                            $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $oid,'entity_type' => $detail['type']));
                            $unreadable_folders = (array)$folder_tree->getUnReadableFolders($GLOBALS['user_id']);
                            if (count($unreadable_folders)) {
                                $st = DbManager::get()->prepare("SELECT dokument_id FROM dokumente WHERE range_id IN (?) AND dokument_id IN (?) ");
                                $st->execute(array($unreadable_folders, $detail['docs']));
                                while ($doc_id = $st->fetchColumn()) {
                                    unset($_SESSION['_search_data']['_search_result'][$doc_id]);
                                }
                            }
                        }
                    }
                }
            } else {
                $sql = "SELECT content_search_dokumente_index.*, IF(seminar_id IS NULL, 'inst', 'sem') as object_type, 1 as access_granted
                        FROM content_search_dokumente_index
                        LEFT JOIN seminare ON object_id = seminar_id
                        LEFT JOIN Institute ON Institute.institut_id = object_id
                        WHERE (MATCH(content_search_dokumente_index.name,description,filename,owner,content,filetype) AGAINST (? IN BOOLEAN MODE))";
                    $st = DbManager::get()->prepare($sql);
                    $st->execute(array($search_for));
                    while($row = $st->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['_search_data']['_search_result'][$row['dokument_id']] = $row;
                    }
            }
            if (count($_SESSION['_search_data']['_search_result'])){
                $this->flash_now('success', sprintf(_("Ihre Suche ergab %s Treffer."), count($_SESSION['_search_data']['_search_result'])));
            } else {
                $this->flash_now('info', _("Ihre Suche ergab keine Treffer."));
            }
        }
        $this->index_action();
        $this->render_action('index');
    }

    function change_start_result_action(){
        $_SESSION['_search_data']['_start_result'] = array_shift(func_get_args());
        $this->index_action();
        $this->render_action('index');
    }

    function sort_action(){
        $sortby = array_shift(func_get_args());
        if ($_SESSION['_search_data']['sortby'] == $sortby){
            $_SESSION['_search_data']['sortdir'] = (int)!$_SESSION['_search_data']['sortdir'];
        } else {
            $_SESSION['_search_data']['sortby'] = $sortby;
        }
        $_SESSION['_search_data']['_start_result']  = 1;
        $this->do_sort = true;
        $this->index_action();
        $this->render_action('index');
    }

    function preview_action(){
        $document_id = array_shift(func_get_args());
        $this->result = $this->get_search_result($_SESSION['_search_data']['_search_result'][$document_id]);
        if ($this->is_xhr()) {
            $this->render_template('search/preview_content.php');
            $this->flash->discard();
            $content = $this->get_response()->body;
            $this->erase_response();
            return $this->render_json(array('title' => studip_utf8encode(_("Vorschau")),
                                                'content' => studip_utf8encode($content)));
    }
    }

    function help_action(){
        $this->helpfile = array_shift(func_get_args());
        if ($this->is_xhr()) {
            $this->render_template('search/help_' . $this->helpfile . '.php');
            $this->flash->discard();
            $content = $this->get_response()->body;
            $this->erase_response();
            return $this->render_json(array('title' => studip_utf8encode(_("Hilfe")),
                                                'content' => studip_utf8encode($content)));
    }
    }

    function get_infobox_content(){
        if ($this->num_hits) {
            $infotext = sprintf(_("%s Treffer in ihrem Suchergebnis"), $this->num_hits);
            $sortdir = ($_SESSION['_search_data']['sortdir'] == 1 ? _("aufsteigend") :_("absteigend"));
            $sorticon = 'icons/16/blue/' . ($_SESSION['_search_data']['sortdir'] == 1 ? 'arr_1up.png' : 'arr_1down.png');

            $sorttext = '<div style="margin-bottom:5px;">
                <a href="'.$this->url_for('search/sort/chdate').'">';
            if ($_SESSION['_search_data']['sortby'] == 'chdate'){
                $sorttext .= Assets::img($sorticon);
            } else {
                $sorttext .= Assets::img('blank.gif', 'width="16"');
            }
            $sorttext .= '&nbsp;' . _("Datum");
            if ($_SESSION['_search_data']['sortby'] == 'chdate'){
                $sorttext .= '&nbsp(' . $sortdir . ')';
            }
            $sorttext .= '</a></div><div style="margin-bottom:5px;">
                    <a href="'.$this->url_for('search/sort/owner').'">';
            if ($_SESSION['_search_data']['sortby'] == 'owner'){
                $sorttext .= Assets::img($sorticon);
            } else {
                $sorttext .= Assets::img('blank.gif', 'width="16"');
            }
            $sorttext .= '&nbsp;' ._("Name des Autors");
            if ($_SESSION['_search_data']['sortby'] == 'owner'){
                $sorttext .= '&nbsp(' . $sortdir . ')';
            }
            $sorttext .= '</a></div>';



            $infobox[1] = array("kategorie" => _("Ergebnis sortieren:"),
                    "eintrag" => array(    array(    "icon" => "icons/16/black/search.png",
                                                "text"  =>    $sorttext)));
        }

        else $infotext = _("Es liegt kein Suchergebnis vor.");
        $infobox[0] = array("kategorie" => _("Informationen:"),
                    "eintrag" => array(    array(    "icon" => "icons/16/black/exclaim.png",
                                                "text"  =>    $infotext)));
        $this->infobox['content'] = $infobox;
        $this->infobox['picture'] = Assets::image_path("infobox/board1.jpg");

    }

    function get_search_words($html_ready = 0){
        static $search_words;
        if (!is_array($search_words[$html_ready])){
            $s_str = $_SESSION['_search_data']['_search_query'];
            $s_str = str_replace(array('+','-','(',')','<','>'), ' ' , $s_str);
            preg_match_all('/"[^"]*"/', $s_str, $tmp1);
            $s_str = preg_replace('/"[^"]*"/', '',$s_str);
            preg_match_all('/\S*/', $s_str, $tmp2);
            foreach(array_merge($tmp1[0],$tmp2[0]) as $tmp3){
                trim($tmp3);
                if (preg_match('/^".*"$/', $tmp3)){
                    $tmp3 = substr($tmp3,1,-1);
                }
                if ($tmp3){
                    $pos = strpos($tmp3,'*');
                    if ( $pos !== false){
                        $tmp3 = substr($tmp3,0,$pos);
                        if ($html_ready){
                            $tmp3 = htmlReady($tmp3);
                        }
                        $search_words[$html_ready][] = '/\b' . preg_quote($tmp3) . '\S*\b/i';
                    } else {
                        if ($html_ready){
                            $tmp3 = htmlReady($tmp3);
                        }
                        $search_words[$html_ready][] = '/\b' . preg_quote($tmp3) . '\b/i';
                    }
                }
            }
            if (!is_array($search_words[$html_ready])){
                $search_words[$html_ready] = array();
            }
        }
        return $search_words[$html_ready];
    }

    function highlight_search($str, $html_ready = 0){
        return preg_replace($this->get_search_words($html_ready), '<span style="background-color:lime">$0</span>' , $str);
    }

    function get_search_excerpt($str, $only_one_row = false){
        $str_a = explode("|", wordwrap($str,80,"|"));
        $count = 0;
        $grepped = array();
        $ret = false;
        foreach($this->get_search_words() as $pattern){
            foreach(preg_grep($pattern, $str_a) as $grep_key => $detail){
                $grepped[$grep_key] = $detail;
            }
        }
        foreach($grepped as $grep_key => $detail){
            if (!$only_one_row && $grep_key > 0){
                $ret[$count] = '...' . $str_a[$grep_key - 1];
            }
            $ret[$count] .= $detail;
            if (!$only_one_row && $grep_key < count($str_a)-1){
                $ret[$count] .= $str_a[$grep_key + 1] . '...';
            }
            ++$count;
        }
        return $ret;
    }



    function get_search_result($hit){
        $dok_id = $hit['dokument_id'];
        $db = DBManager::get();
        $sql = "SELECT dokumente.*,content,Vorname,Nachname,username FROM dokumente
        LEFT JOIN auth_user_md5 USING(user_id)
        LEFT JOIN content_search_dokumente_index ON(dokumente.dokument_id=content_search_dokumente_index.dokument_id)
        WHERE dokumente.dokument_id = " . $db->quote($dok_id);
        $ret = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        $ret['access_granted'] = $hit['access_granted'];
        $ret['object_id'] = $hit['object_id'];
        $ret['object_type'] = $hit['object_type'];
        return $ret;
    }

    function get_used_file_extensions()
    {
        $ret = array();
        $sql = "SELECT COUNT( * ) AS  c ,  `filetype`
                FROM  `content_search_dokumente_index`
                GROUP BY  `filetype` ORDER by c DESC LIMIT 10";
        foreach (DbManager::get()->query($sql) as $row)  {
            if ($row['c'] > 10) $ret[] = trim($row['filetype'], '_');
        }
        return $ret;
    }
}
class ArraySorter{
    var $sort_flag = 'string';
    var $sort_field = null;
    var $sort_dir = 'ASC';
    var $preserve_keys = true;

    function ArraySorter(){
    }

    function sort($array){
        if ($this->sort_field && is_array($array)){
            $php_func = ($this->preserve_keys) ? 'uasort' : 'usort';
            $callback = $this->sort_flag . $this->sort_dir;
            if (method_exists($this, $callback)){
                $php_func($array, array($this, $callback));
            }
        }
        return $array;
    }

    function stringASC($a, $b){
        $one = $a[$this->sort_field];
        $two = $b[$this->sort_field];
        return (strcoll($one, $two) * -1);
    }

    function stringDESC($a, $b){
        $one = $a[$this->sort_field];
        $two = $b[$this->sort_field];
        return strcoll($one, $two);
    }

    function numericASC($a, $b){
        $one = (int)$a[$this->sort_field];
        $two = (int)$b[$this->sort_field];
        if ($one == $two) return 0;
        return ($one < $two) ? -1 : 1;
    }

    function numericDESC($a, $b){
        $one = (int)$a[$this->sort_field];
        $two = (int)$b[$this->sort_field];
        if ($one == $two) return 0;
        return ($one > $two) ? -1 : 1;
    }

}
