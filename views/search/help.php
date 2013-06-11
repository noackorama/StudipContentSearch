<?php
include 'lib/include/html_head.inc.php';
foreach (Navigation::getItem('/') as $path => $nav) {
    Navigation::removeItem('/'.$path );
}
echo $this->render_partial($standard_templates . 'header.php', array('current_page' => _("Hilfe")));
echo $this->render_partial('search/help_' . $helpfile . '.php');
include 'lib/include/html_end.inc.php';
?>