<form class="default"
      action="<?php echo $controller->link_for('templates/store', $template->isNew() ? null : $template->id) ?>"
      method="post" enctype="multipart/form-data">
    <section>
        <label for="name">
            <span class="required">
                <?php echo dgettext('coursecertificate', 'Name') ?>
            </span>
        </label>
        <input type="text" name="name" id="name" size="75" maxlength="255"
               value="<?php echo htmlReady($template->name) ?>"
               placeholder="<?php echo dgettext('coursecertificate',
                   'Geben Sie einen Namen für die Vorlage ein.') ?>">
    </section>
    <section>
        <label for="file">
            <span class="required">
                <?php echo dgettext('coursecertificate', 'Vorlagendatei') ?>
            </span>
        </label>
        <input type="file"
               accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document;application/pdf">
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('coursecertificate', 'Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(dgettext('coursecertificate', 'Abbrechen'), 'cancel',
            ['data-dialog-close' => true]) ?>
    </footer>
</form>
