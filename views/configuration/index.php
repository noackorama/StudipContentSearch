<div style="padding: 1em 1em 1em 1em;">
<form name="config" action="<?=$controller->url_for('')?>" method="post">
<div style="float:left;display:inline;width:300px;font-weight: bold;">
<?=_("Volltextindex aktualisieren")?>
</div>
<div style="float:left;display:inline">
<?=\Studip\Button::createAccept('OK', 'recreate_index');?>
<input type="checkbox" name="truncate_index" id="truncate_index" value="1">
<label for="truncate_index"><?=_("Index löschen")?></label>
</div>
<div style="clear:both">&nbsp;</div>

<div style="float:left;display:inline;width:300px;font-weight: bold;">
<?=_("Konfiguration zurücksetzen")?>
</div>
<div style="float:left;display:inline">
<?=\Studip\Button::createAccept('OK', 'revert_config');?>
</div>
<div style="clear:both">&nbsp;</div>

<div style="float:left;display:inline;width:300px;font-weight: bold;">
<?=_("Konfiguration anpassen und prüfen")?>
</div>
<div style="float:left;display:inline">
<? foreach($config as $type => $settings) : ?>
    <? if(isset($settings['enabled'])) : ?>
        <div style="font-weight:bold"><?=$type?>:</div>
        <label for="config[<?=$type?>][enabled]"><?=_("Aktiv:")?>
        </label><input type="checkbox" id="config[<?=$type?>][enabled]" name="config[<?=$type?>][enabled]" value="1" <?=($settings['enabled'] ? 'checked' : '')?>>
        <br>
        <? if ($settings['path']) : ?>
            <? foreach($settings['path'] as $tool => $path) : ?>
                <label for="config[<?=$type?>][path][<?=$tool?>]">
                <?=$tool?>:
                </label>
                <input type="text" id="config[<?=$type?>][path][<?=$tool?>]" name="config[<?=$type?>][path][<?=$tool?>]" value="<?=$path?>">
                <br>
            <? endforeach; ?>
        <? else : ?>
            <?=_("(intern)")?>
        <? endif; ?>
        <br>
    <? endif;?>
<? endforeach; ?>
<?=\Studip\Button::createAccept('OK', 'check_config');?>
</div>
</form>
</div>
