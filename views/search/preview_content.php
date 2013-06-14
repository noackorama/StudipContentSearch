<?php
if ($result){
	if (!$result['name']) $result['name'] = $result['filename'];
	$addon = get_fullname($result['user_id'], 'no_title_short', 1) . '&nbsp;'
			. strftime('%x', $result['chdate']);
	$link = '<a class="tree" '.tooltip(_("Datei downloaden"),1).' href="' . UrlHelper::getLink(GetDownloadLink($result["dokument_id"], $result["filename"], 0)) . '">{CONTENT}</a>';
	$ext = GetFileExtension($result['filename']);
	$icon = str_replace('{CONTENT}',GetFileIcon($ext, true), $link);
	$title = str_replace('{CONTENT}', htmlReady(my_substr($result['name'],0,85)), $link);
	echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>";
	printhead(0,0,false,"open",false, $icon, $title ,$addon, $result['chdate']);
	echo "\n</tr></table>";
	echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
	$content = '<div align="center">';
	$content .= \Studip\LinkButton::create(_('Herunterladen'), UrlHelper::getLink(GetDownloadLink($result["dokument_id"], $result["filename"], 0, 'force_download')));
	if ( !in_array($ext, array("zip","tgz","gz","bz2")) ){
		$content .= '&nbsp;';
		$content .= \Studip\LinkButton::create(_('Als ZIP-Archiv'), UrlHelper::getLink(GetDownloadLink($result["dokument_id"], $result["filename"], 0, 'zip')));
	}
	$content .= '</div>';
	$content .= "<b>" . sprintf(_("Dateiname")) . ":</b>";
	$content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
	$content .= $controller->highlight_search(htmlReady($result['filename'],1,1),1) ;
	$content .= "</div><br>";
	if ($result['description']){
		$content .= "<b>" . sprintf(_("Beschreibung")) . ":</b>";
		$content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
		$content .= $controller->highlight_search(htmlReady($result['description'],1,1),1) ;
		$content .= "</div><br>";
	}
	if ($result['content']){
		$content .= "<b>" . sprintf(_("Inhalt (Textdarstellung)")) . ":</b>";
		$content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%;max-height:700px;overflow:auto\">";
		if ($search_data['_search_only']['content']) {
		    $content .= $controller->highlight_search(htmlReady($result['content'],1,1),1) ;
		} else {
		    $content .= htmlReady($result['content'],1,1);
		}
		$content .= "</div><br>";
	}
	$ext = strtolower(GetFileExtension($result['filename']));
	if ($ext == 'jpg' || $ext == 'gif' || $ext == 'png' || $ext == 'jpe' || $ext == 'jpeg'){
		$content .= "<b>" . _("Bild") . ":</b>";
		$content .= "<div style=\"margin-left:10px;margin-right:10px;max-width:700px;overflow:auto\">";
		$content .= '<img src="'. UrlHelper::getLink(GetDownloadLink($result["dokument_id"], $result["filename"], 0)) .'">';
		$content .= "</div><br>";
	}
	printcontent(0,0,$content,$edit);
	echo "\n</table>";
}