<?php declare(strict_types=1);

namespace atkdataupdatenotification;

use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;

/**
 * This class represents a message for logged in users. The main concept is to display unread messages on login to
 * inform each individual user about updates, usually in a modal.
 */
class UpdateNotification extends Model
{

    use ModelWithMToMTrait;
    use CreatedDateAndLastUpdatedTrait;

    public $table = 'update_notification';

    public $caption = 'Benachrichtigung';

    protected string $userModel = '';


    protected function init(): void
    {
        parent::init();
        //Message title, e.g. "UI Update"
        $this->addField(
            'title',
        );
        //is text HTML?
        $this->addField(
            'is_html',
            [
                'type' => 'boolean',
                'caption' => 'Content is HTML code'
            ]
        );
        //HTML or Text Content of the message
        $this->addField(

            'text', //todo rename to content
            [
                'type' => 'text',
                'caption' => 'Content of the update notification'
            ]
        );

        //can be used by UI to force user to click "I have read it!" button instead of just closing the modal
        $this->addField(

            'needs_user_confirm',
            [
                'type' => 'boolean',
                'caption' => 'Must be confirmed by user as read'
            ]
        );
        //if a date filter is applied, this makes the date filter ignore this message. Useful for e.g. "Welcome new User"
        $this->addField(

            'never_invalid',
            [
                'type' => 'integer',
                'caption' => 'Ignore date filtering'
            ]
        );

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
        $this->addMToMReferenceAndDeleteHook(
            UpdateNotificationToUser::class,
            '',
            ['their_field' => 'update_notification_id'],
            ['userModel' => $this->userModel]
        );

        //show older messages first if there is more than one to show to user
        $this->setOrder(['created_date' => 'ASC']);
    }

    public function addConditionToLoadOnlyUnreadByUser(Model $user): void
    {
        $this->addCondition(
            $this->refLink(UpdateNotificationToUser::class)
                ->addCondition('user_id', $user->getId())
                ->action('count'),
            '<',
            1
        );
    }

    public function addNewerThanCondition(\DateTimeInterface $maxInPast): void
    {
        $this->addCondition(
            Model\Scope::createOr(
                ['created_date', '>=', $maxInPast],
                ['never_invalid', 1]
            )
        );
    }

    /**
     * mark  message as read for the passed user.
     */
    public function markAsReadForUser(Model $user): UpdateNotificationToUser
    {
        return $this->addMToMRelation(
            new UpdateNotificationToUser($this->persistence, ['userModel' => $this->userModel]),
            $user
        );
    }

    public function isReadByUser(Model $user): bool
    {
        return $this->hasMToMRelation(
            new UpdateNotificationToUser($this->persistence, ['userModel' => $this->userModel]),
            $user
        );
    }
}