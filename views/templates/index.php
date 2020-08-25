<?php if (count($templates) == 0) : ?>
    <?php echo Messagebox::info(dgettext('coursecertificate',
        'Es sind keine Vorlagen für Teilnahmezertifikate vorhanden.')) ?>
<?php else : ?>
    <table class="default">
        <caption><?php echo sprintf(dngettext('coursecertificate','Eine Vorlage gefunden',
            '%s Vorlagen gefunden', count($templates)), count($templates)) ?></caption>
        <colgroup>
            <col width="33%">
            <col width="100">
            <col>
            <col width="40">
        </colgroup>
        <thead>
            <tr>
                <th><?php echo dgettext('coursecertificate','Name') ?></th>
                <th><?php echo dgettext('coursecertificate','Typ') ?></th>
                <th><?php echo dgettext('coursecertificate','Vorlagendatei') ?></th>
                <th><?php echo dgettext('coursecertificate','Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $t) : ?>
                <tr>
                    <td><?php echo htmlReady($t->name) ?></td>
                    <td><?php echo htmlReady($t->type) ?></td>
                    <td><?php echo htmlReady($t->filename) ?></td>
                    <td>
                        <a href="<?php echo $controller->link_for('templates/edit', $t->id, ['data-dialog' => true]) ?>">
                            <?php echo Icon::create('edit') ?>
                        </a>
                        <a href="<?php echo $controller->link_for('templates/delete', $t->id) ?>">
                            <?php echo Icon::create('trash') ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif;
