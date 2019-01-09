<form class="default" method="post">
    <section>
        <label for="<?= $quicksearch->getId() ?>">
            Benutzer
        </label>
        <?= $quicksearch->render() ?>
    </section>
    <section>
        <label for="certificate">
            Zertifikat
        </label>
        <select name="certificate" size="1">
            <?php foreach ($templates as $key => $template): ?>
                <option value="<?= $template['path'] ?>" <?= $template['selected'] ?>><?= $template['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </section>
    <div data-dialog-buttons>
        <?= Studip\Button::create(_('Anzeigen')) ?>
        <?= Studip\Button::create(_('Erstellen'), "create") ?>
    </div>
    <?php $i = 0; foreach ($semester as $key => $courses): ?>
        <table class="default">
            <caption>
                <input type="checkbox" data-proxyfor=":checkbox.semester_<?= $i ?>">
                <?= $key ?>
            </caption>
            <colgroup>
                <col width="16">
                <col>
            </colgroup>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="semester_<?= $i ?>" name="whitelist[]" value="<?= $course['seminar_id'] ?>"
                            <?= $course['status'] == 'autor' ? 'checked' : 'disabled' ?>>
                    </td>
                    <td>
                        <a href="<?= URLHelper::getURL('dispatch.php/course/overview', ['cid' => $course['Seminar_id']]) ?>">
                            <?= $course['VeranstaltungsNummer'] ?
                            htmlReady($course['VeranstaltungsNummer'].' '.$course['Name']) :
                            htmlReady($course['Name']) ?>
                            (<?= date('d.m.Y', $course['start']) ?><?=
                                date('d.m.Y', $course['start']) != date('d.m.Y', $course['end']) ?
                                ' - ' . date('d.m.Y', $course['end']) :
                                '' ?>, <?= htmlReady($course['dauer']) ?>)
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php $i++; endforeach ?>
</form>
