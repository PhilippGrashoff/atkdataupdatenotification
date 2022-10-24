<?php

declare(strict_types=1);

namespace atkdataupdatenotification\tests\testclasses;

use atk4\data\Model;
use atkdataupdatenotification\UpdateNotificationToUser;
use mtomforatk\ModelWithMToMTrait;

class OtherUserModel extends Model
{

    use ModelWithMToMTrait;

    public $table = 'other_user';

    protected function init(): void
    {
        parent::init();
        $this->addField(
            'role',
            ['type' => 'string']
        );

        $this->addMToMReferenceAndDeleteHook(UpdateNotificationToUser::class, '', [], ['userModel' => __CLASS__]);
    }
}