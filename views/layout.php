<?
include 'lib/include/html_head.inc.php';
include 'lib/include/header.php';

if($question) echo $question;
if($infobox){
    if(!$infobox['template']){
        $infobox['template'] = $standard_templates . 'infobox/infobox_generic_content';
    }
}
?>
<div id="layout_container" style="padding-top:1px;">
    <div>
          <div id="layout_content">
<?
if (is_array($msg = $flash->get('msg'))) {
    foreach($msg as $one_msg) {
        list($type, $content, $aux) = $one_msg;
        echo call_user_func(array('MessageBox',$type), $content, $aux);
    }
}
?>
            <?= $content_for_layout ?>
          </div>
          <? if ($infobox) : ?>
              <div id="layout_sidebar">
                  <div id="layout_infobox">
                    <?= $this->render_partial($infobox['template'], $infobox) ?>
                  </div>
              </div>
         <? endif; ?>
        </div>
    </div>
</div>

<? include 'lib/include/html_end.inc.php';
