<?php declare(strict_types=1);

namespace atkdataupdatenotification\tests\testclasses;

use Atk4\Data\Model;
use atkdataupdatenotification\UpdateNotificationToUser;
use mtomforatk\ModelWithMToMTrait;

class UserModel extends Model
{
    use ModelWithMToMTrait;

    public $table = 'user';

    protected function init(): void
    {
        parent::init();
        $this->addField(
            'role',
            [
                'type' => 'string'
            ]
        );

        $this->addMToMReferenceAndDeleteHook(UpdateNotificationToUser::class, '', [], ['userModel' => __CLASS__]);
    }
}