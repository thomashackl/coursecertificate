<?php Navigation::activateItem('/tools/coursecert') ?>
<form  method="POST">
    <table>
        <tr>
            <td>Benutzer</td>
            <td><?= $quicksearch ?></td>
        </tr>
        <tr>
            <td>Zertifikat</td>
            <td>    
                <select name="certificate" size="1">
                    <?php foreach ($templates as $key => $template): ?>
                        <option value="<?= $template['path'] ?>" <?= $template['selected'] ?>><?= $template['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= Studip\Button::create(_('Anzeigen')) ?>
                <?= Studip\Button::create(_('Erstellen'), "create") ?>
            </td>
        </tr>
    </table>
    <?php foreach ($semester as $key => $courses): ?>
        <h3><?= $key ?></h3>
        <?php foreach ($courses as $course): ?>
            <?php if ($course['status'] == 'user') : ?>
                <span style="color: #999999; padding-left: 23px;">
            <?php endif ?>
            <?php if ($course['status'] == 'autor') : ?>
                <input type="checkbox" name="whitelist[]" value="<?= $course['seminar_id'] ?>" checked>
            <?php endif ?>
            <?= $course['VeranstaltungsNummer'] ?
                htmlReady($course['VeranstaltungsNummer'].' '.$course['Name']) :
                htmlReady($course['Name']) ?>
            (<?= date('d.m.Y', $course['start']) ?><?=
                date('d.m.Y', $course['start']) != date('d.m.Y', $course['end']) ?
                    ' - ' . date('d.m.Y', $course['end']) :
                    '' ?>, <?= htmlReady($course['dauer']) ?>)
            <?php if ($course['status'] == 'user') : ?>
                </span>
            <?php endif ?>
            <br>
        <?php endforeach; ?>
    <?php endforeach; ?>
</form>
