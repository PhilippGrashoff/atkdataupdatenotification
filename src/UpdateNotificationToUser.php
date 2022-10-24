<?php declare(strict_types=1);

namespace atkdataupdatenotification;

use mtomforatk\MToMModel;


class UpdateNotificationToUser extends MToMModel
{

    public $table = 'update_notification_to_user';

    public string $userModel = '';

    protected function init(): void
    {
        $this->fieldNamesForReferencedClasses = [
            'update_notification_id' => UpdateNotification::class,
            'user_id' => $this->userModel
        ];

        parent::init();
    }
}