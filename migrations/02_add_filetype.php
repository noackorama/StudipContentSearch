<?php
class AddFiletype extends DBMigration
{
    function up(){
        $db = DbManager::get();
        $exists = $db->query("SHOW COLUMNS FROM content_search_dokumente_index WHERE field =  'filetype'")->fetchColumn();
        if (!$exists) {
            $db->exec("
                ALTER TABLE  `content_search_dokumente_index` ADD  `filetype` VARCHAR( 6 ) NOT NULL DEFAULT  ''
                ");
            $db->exec("
                ALTER TABLE  `content_search_dokumente_index` DROP INDEX  `name` ,
                ADD FULLTEXT  `name` (
                `name` ,
                `description` ,
                `filename` ,
                `owner` ,
                `content` ,
                `filetype`
                )
                ");
            $st = $db->prepare("UPDATE content_search_dokumente_index set filetype=? WHERE dokument_id=?");
            foreach ($db->query("SELECT dokument_id,filename FROM content_search_dokumente_index") as $row) {
                $ext = strtolower(substr(strrchr($row['filename'],'.'),1));
                if ($ext) {
                    $st->execute(array(str_pad($ext,6,"_",STR_PAD_BOTH), $row['dokument_id']));
                }
            }
        }
    }
}