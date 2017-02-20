<? Navigation::activateItem('/tools/coursecert') ?>
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
                    <? foreach ($templates as $key => $template): ?>
                        <option value="<?= $template['path'] ?>" <?= $template['selected'] ?>><?= $template['name'] ?></option>
                    <? endforeach; ?>
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
    <? foreach ($semester as $key => $courses): ?>
        <h3><?= $key ?></h3>
        <? foreach ($courses as $course): ?>
            <input type="checkbox" name="whitelist[]" value="<?= $course['seminar_id'] ?>" checked><?= $course['VeranstaltungsNummer'] ? $course['VeranstaltungsNummer'].' '.$course['Name'] : $course['Name'] ?> <br />
        <? endforeach; ?>
    <? endforeach; ?>
</form>
