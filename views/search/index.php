<div style="padding: 1em 1em 1em 1em;">
<div>
<?=_("Hier können Sie eine Volltextsuche in den Inhalten von Veranstaltungen durchführen")?>
</div>
<form action="<?=$controller->url_for('search/perform')?>" method="post">
<table width = "100%" border="0" cellspacing = "0" cellpadding = "2" align="center">
<tr>
    <td>
    <b><?=_("Suchbegriff(e)")?></b>
    <a href="<?=$controller->url_for('search/help/suchbegriff')?>" target="_blank"
    onClick="STUDIP.ContentSearchDialog.initialize(this.href);return false;">
    <?=Assets::img('icons/16/black/info-circle.png',  '' . tooltip("Klicken für Hinweise zur Eingabe der Suchbegriffe"))?>
    </a>
    </td>
    <td align="left" width="75%" colspan="2">
    <input name="search_query" size="60" style="width:90%" value="<?=htmlready($search_data['_search_query'])?>">
    </td>
    </tr>
    <?php if(!$GLOBALS['perm']->have_perm('root')){?>
        <tr>
        <td>
        <b><?=_("Suche einschränken")?></b>
        </td>
        <td align="left" width="35%">
        <input name="search_only[my_sem]" id="search_only[my_sem]" value="1" type="checkbox" style="vertical-align:middle" <?=($search_data['_search_only']['my_sem'] ? 'checked' : '')?>>
        <label for="search_only[my_sem]" style="font-weight:bold;"><?=_("Nur meine Veranstaltungen")?></label>
        </td>
        <td align="left" width="35%">
        <input name="search_only[public_sem]" id="search_only[public_sem]" value="1" type="checkbox" style="vertical-align:middle" <?=($search_data['_search_only']['public_sem'] ? 'checked' : '')?>>
        <label for="search_only[public_sem]" style="font-size:10pt;font-weight:bold;">&nbsp;<?=_("Alle zugänglichen Veranstaltungen")?></label>
        </td>
        </tr>
    <?php }?>
    <tr>
        <td>
        <b><?=_("Dateitypen")?></b>
        </td>
        <td align="left" colspan="2">
        <input name="search_only[ext][all]" id="search_only[ext][all]" value="1" type="checkbox" style="vertical-align:middle" <?=($search_data['_search_only']['ext']['all'] ? 'checked' : '')?>>
        <label for="search_only[ext][all]" style="font-weight:bold;padding-right:5px;"><?=_("Alle")?></label>
        <? foreach($extensions as $ext) : ?>
            <label for="search_only[ext][<?=$ext?>]" style="font-weight:bold;padding-right:5px;white-space:nowrap">
            <input name="search_only[ext][<?=$ext?>]" id="search_only[ext][<?=$ext?>]" value="1" type="checkbox" style="vertical-align:middle" <?=($search_data['_search_only']['ext'][$ext] ? 'checked' : '')?>>
            <?=$ext?>
            </label>
        <? endforeach; ?>
        </td>

    </tr>
    <tr>
    <td colspan="3" align="center">
    <?=\Studip\Button::createAccept(_("Suche starten"), 'search');?>
    &nbsp;
    <?=\Studip\Button::createCancel(_("Suche zurücksetzen"), 'cancel');?>
    &nbsp;
    </td>
    </tr>
</table>
<div>
</div>
</form>
<?php
if ($num_hits){
    echo $this->render_partial('search/result.php');
}
?>
</div>