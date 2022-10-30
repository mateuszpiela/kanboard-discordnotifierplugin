<?php

namespace Kanboard\Plugin\DiscordNotifier;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:project:integrations', 'discordnotifier:project/integration');

        $this->projectNotificationTypeModel->setType('discordnotifier', t('Discord Notifier'), '\Kanboard\Plugin\DiscordNotifier\Notification\DiscordNotifierHandler');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'Discord Notifier';
    }

    public function getPluginDescription()
    {
        return t('Send board notifications to discord via webhook');
    }

    public function getPluginAuthor()
    {
        return 'Mateusz Piela';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/mateuszpiela/kanboard-discordnotifierplugin';
    }

    public function getCompatibleVersion()
    {
        // Examples:
        // >=1.0.37
        // <1.0.37
        // <=1.0.37
        return '>=1.2.24';
    }
}

