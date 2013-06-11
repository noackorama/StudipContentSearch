<?
$end_result = (($search_data['_start_result'] + 5 > $num_hits) ? $num_hits : $search_data['_start_result'] + 4);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
     <tr>
          <td class="steel2" align="left"><?printf(_("%s Treffer in ihrem Suchergebnis."), $num_hits);?>
          </td>
          <td class="steel2" align="right"><?
          echo _("Anzeige: ");
          if ($search_data['_start_result'] > 1) {
               echo "<a href=\"".$controller->url_for('search/change_start_result/'
                    . ($search_data['_start_result'] - 5)) . "\">"
                    . Assets::Img('icons/16/yellow/arr_1left.png',array('style' => 'vertical-align:middle'))
                    . "</a>";
          } else {
               echo Assets::Img('blank.gif', "width=\"16\" height=\"16\"");
          }
          echo $search_data['_start_result'] . " - " . $end_result;
          if ($search_data['_start_result'] + 4 < $num_hits) {
               echo "<a href=\"".$controller->url_for('search/change_start_result/'
                    . ($search_data['_start_result'] + 5)) . "\">"
               . Assets::Img('icons/16/yellow/arr_1right.png', array('style' => 'vertical-align:middle'))
                    . "</a>";
          } else {
               echo Assets::Img('blank.gif', "width=\"16\" height=\"16\"");
          }
          ?></td>
     </tr>
     <tr>
          <td colspan="2"><?
          $index = array_keys($search_data['_search_result']);
          for ($i = $search_data['_start_result']; $i <= $end_result; ++$i){
               $result = $controller->get_search_result($search_data['_search_result'][$index[$i-1]]);
               if ($result){
                    if (!$result['name']) $result['name'] = $result['filename'];
                    $addon = '<a href="'.URLHelper::getLink('about.php?username=' . $result['username']). '">'
                    . get_fullname($result['user_id'], 'no_title_short', 1) . '</a>&nbsp;'
                    . strftime('%x', $result['chdate']);
                    if ($result['access_granted']) {
                        $link = '<a class="tree" href="'.$controller->url_for('search/preview/'.$result['dokument_id']).'"'
                        . 'target="_blank" onClick="STUDIP.ContentSearchDialog.initialize(this.href);return false;"'
                        . tooltip(_("Vorschau öffnen"), 1) . '>{CONTENT}</a>';
                        $icon = str_replace('{CONTENT}',GetFileIcon(GetFileExtension($result['filename']), true), $link);
                        $title = str_replace('{CONTENT}', htmlReady(my_substr($result['name'],0,85)), $link);
                    } else {
                        $icon = GetFileIcon(GetFileExtension($result['filename']), true);
                        $title = '<span '. tooltip(_("Sieh haben noch keinen Zugriff auf diese Datei"), 1) .'>' . htmlReady(my_substr($result['name'],0,85)) . '</span>';
                    }
                    echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>";
                    printhead(0,0,false,"open",false, $icon, $title ,$addon, $result['chdate']);
                    echo "\n</tr></table>";
                    $content = "";
                    echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
                    if ($result['object_type'] == 'sem') {
                        $url = !$result['access_granted'] ? 'details.php?sem_id='.$result['object_id']
                    . '&send_from_search=1&send_from_search_page='.$controller->url_for('search')
                    : 'seminar_main.php?auswahl='.$result['object_id']
                    . '&redirect_to=folder.php&cmd=tree';
                    }
                    if ($result['object_type'] == 'inst') {
                        $url = 'institut_main.php?auswahl='.$result['object_id']
                        . '&redirect_to=folder.php&cmd=tree';
                    }
                    $content = '<div style="margin-bottom:10px;"><b><u>'
                    .'<a href="'.UrlHelper::getLink($url).'">'
                    .htmlready(getHeaderLine($result['object_id'])).'</a></u></b></div>';
                    if ($result['access_granted']) {
                        if ($hits = $controller->get_search_excerpt($result['name'],1)){
                             $content .= "<b>" . sprintf(_("Name (%s Fundstellen)"), count($hits)) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= $controller->highlight_search(htmlReady($hits[0],1,1),1) ;
                             $content .= "</div><br>";
                        }
                        if ($hits = $controller->get_search_excerpt($result['description'])){
                             $content .= "<b>" . sprintf(_("Beschreibung (%s Fundstellen)"), count($hits)) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= $controller->highlight_search(htmlReady($hits[0],1,1),1) ;
                             $content .= "</div><br>";
                        } elseif ($result['description']) {
                            $content .= "<b>" . sprintf(_("Beschreibung")) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= htmlReady($result['description'],1,1) ;
                             $content .= "</div><br>";
                        }
                        if ($hits = $controller->get_search_excerpt($result['filename'],1)){
                             $content .= "<b>" . sprintf(_("Dateiname (%s Fundstellen)"), count($hits)) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= $controller->highlight_search(htmlReady($hits[0],1,1),1) ;
                             $content .= "</div><br>";
                        }
                        if ($hits = $controller->get_search_excerpt($result['Vorname'] . ' ' . $result['Nachname'] ,1)){
                             $content .= "<b>" . sprintf(_("Autor (%s Fundstellen)"), count($hits)) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= $controller->highlight_search(htmlReady($hits[0],1,1),1) ;
                             $content .= "</div><br>";
                        }
                        if ($hits = $controller->get_search_excerpt($result['content'])){
                             $content .= "<b>" . sprintf(_("Inhalt (%s Fundstellen)"), count($hits)) . ":</b>";
                             $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%\">";
                             $content .= $controller->highlight_search(htmlReady($hits[0],1,1),1) ;
                             $content .= "</div><br>";
                        }
                    } else {
                        $content .= "<div style=\"margin-left:10px;margin-right:10px;font-size:80%;\">";
                        $content .= _("(Sie haben noch keinen Zugriff auf diese Datei. Folgen Sie dem Link und tragen Sie sich in die Veranstaltung ein, um Zugriff zu erlangen.)");
                        $content .= "</div>";
                    }
                    printcontent(0,0,$content,$edit);
                    echo "\n</table>";

               }
          }
          ?></td>
     </tr>
     <tr>
          <td class="steel2" align="left"><?printf(_("%s Treffer in ihrem Suchergebnis."), $num_hits);?>
          </td>
          <td class="steel2" align="right"><?
          echo _("Anzeige: ");
          if ($search_data['_start_result'] > 1) {
               echo "<a href=\"".$controller->url_for('search/change_start_result/'
                    . ($search_data['_start_result'] - 5)) . "\">"
                    . Assets::Img('icons/16/yellow/arr_1left.png',array('style' => 'vertical-align:middle'))
                    . "</a>";
          } else {
               echo Assets::Img('blank.gif', "width=\"16\" height=\"16\"");
          }
          echo $search_data['_start_result'] . " - " . $end_result;
          if ($search_data['_start_result'] + 4 < $num_hits) {
          echo "<a href=\"".$controller->url_for('search/change_start_result/'
                    . ($search_data['_start_result'] + 5)) . "\">"
                    . Assets::Img('icons/16/yellow/arr_1right.png',array('style' => 'vertical-align:middle'))
                    . "</a>";
          } else {
               echo Assets::Img('blank.gif', "width=\"16\" height=\"16\"");
          }
     ?></td>
     </tr>
</table>
