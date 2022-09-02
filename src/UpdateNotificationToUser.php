<?php declare(strict_types=1);

namespace atkdataupdatenotification;

use mtomforatk\MToMModel;


class UpdateNotificationToUser extends MToMModel
{

    public $table = 'update_notification_to_user';

    protected string $userModel = '';

    protected function init(): void
    {
        $this->fieldNamesForReferencedClasses = [
            'user_message_id' => UpdateNotification::class,
            'user_id' => $this->userModel
        ];

        parent::init();
    }
}