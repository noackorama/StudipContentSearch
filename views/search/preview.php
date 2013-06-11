<?php
include 'lib/include/html_head.inc.php';
foreach (Navigation::getItem('/') as $path => $nav) {
	Navigation::removeItem('/'.$path );
}
echo $this->render_partial($standard_templates . 'header.php', array('current_page' => _("Vorschau")));
echo $this->render_partial('search/preview_content.php');
include 'lib/include/html_end.inc.php';
?>