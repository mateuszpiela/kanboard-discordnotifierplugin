<h3><img width="24" height="24" style="margin-bottom: -6px;" src="<?= $this->url->dir() ?>plugins/DiscordNotifier/Asset/discord-icon.svg"/> Discord Notifier</h3>
<div class="panel">
    <?= $this->form->label(t('Webhook URL'), 'discordnotifier_webhook_url') ?>
    <?= $this->form->text('discordnotifier_webhook_url', $values) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>