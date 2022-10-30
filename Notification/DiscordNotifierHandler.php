<?PHP

namespace Kanboard\Plugin\DiscordNotifier\Notification;
use Exception;
use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\CommentModel;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\TaskModel;

class DiscordNotifierHandler extends Base implements NotificationInterface {
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyUser(array $user, $event_name, array $event_data) {
        throw new Exception("Only for project");
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data) {
        $webhook = $this->projectMetadataModel->get($project['id'], 'discordnotifier_webhook_url', $this->configModel->get('discordnotifier_webhook_url'));

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $project, $event_name, $event_data);
        }
    }


        /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     * @return array
     */
    public function getMessage(array $project, $eventName, array $eventData)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $avasize = 256;
            
            if(key_exists("avatar_path", $this->userSession->getAll())) {
                $avapath = $this->userSession->getAll()["avatar_path"];
                $avamd5 = md5($avapath . $avasize);
            }
            
            $ac_title = $this->notificationModel->getTitleWithAuthor($author, $eventName, $eventData);
        } else {
            $ac_title = $this->notificationModel->getTitleWithoutAuthor($eventName, $eventData);
        }

        $symbol = $this->getIcon($eventName, $eventData) ?? "";

        $title_embed = $symbol['icon'] . "  [" . $eventData['task']['project_name'] . "] - #" 
                        . $eventData['task']["id"] . " " 
                        . $eventData['task']["title"];

        $attachment = [];
        if ($this->configModel->get('application_url') !== '') {
            $attachment_link = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $eventData['task']['id'], 'project_id' => $project['id']), '', true);
            $board_lin = $this->helper->url->to('BoardViewController', 'show', array('project_id' => $project['id']), '', true);

            if(!empty($avamd5)) {
                $avatar_link = $this->helper->url->to('AvatarFileController', 'image', array('user_id' => $this->helper->user->getId(), 'hash' => $avamd5, 'size' => $avasize), '', true);
            }
        }

        $author_em = array(
            'name' => t('Open board'),
            'url' => $board_lin ?? ""
        );


        $thumbnail = array(
            "url" => $avatar_link ?? ""
        );

        $embed = array(
            'title' => $title_embed,
            'type' => 'rich',
            'description' => $ac_title,
            'url' => $attachment_link ?? "",
            'thumbnail' => $thumbnail,
            'author' => $author_em,
            'color' => $symbol['color'] ?? 0
        );

        return array(
            'embeds' => array($embed),            
        );
    }

    private function getIcon($eventName, $eventData) {
        $symbol = "";
        $color = "";

        $description_events = array(TaskModel::EVENT_CREATE, TaskModel::EVENT_UPDATE, TaskModel::EVENT_USER_MENTION, TaskModel::EVENT_MOVE_COLUMN);
        $subtask_events = array(SubtaskModel::EVENT_CREATE, SubtaskModel::EVENT_UPDATE, SubtaskModel::EVENT_DELETE);
        $comment_events = array(CommentModel::EVENT_UPDATE, CommentModel::EVENT_CREATE, CommentModel::EVENT_DELETE, CommentModel::EVENT_USER_MENTION);

        $subtask_status = $eventData['subtask']['status'];

        if (in_array($eventName, $subtask_events))
        {
            switch($subtask_status)
            {
                case SubtaskModel::STATUS_DONE:
                    $symbol = ':white_check_mark:';
                    break;
                case SubtaskModel::STATUS_TODO:
                    $symbol = ':notepad_spiral:';
                    break;
                case SubtaskModel::STATUS_INPROGRESS:
                    $symbol = ':tools:';
                    break;
            }
            $color = 16776960;
        }
        elseif (in_array($eventName, $description_events))  
        {
            switch($eventName) {
                case TaskModel::EVENT_CREATE:
                    $symbol = ':pencil:';
                    break;
                case TaskModel::EVENT_UPDATE:
                    $symbol = ':arrow_double_up:';
                    break;
                case TaskModel::EVENT_USER_MENTION:
                    $symbol = ':person_bowing:';
                    break;
                case TaskModel::EVENT_MOVE_COLUMN:
                    $symbol = ':outbox_tray:';
                    break;
            }
            $color = 11027200;
        }
        elseif (in_array($eventName, $comment_events)) 
        {
            $symbol = ":speech_left:";
            $color = 3426654;
        }

        return array(
            "icon" => $symbol,
            "color" => $color
        );
    }

    /**
     * Send message to Discord
     *
     * @access protected
     * @param  string    $webhook
     * @param  string    $channel
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    protected function sendMessage($webhook, array $project, $eventName, array $eventData)
    {
        $payload = $this->getMessage($project, $eventName, $eventData);

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}