<?php declare(strict_types=1);

namespace atkdataupdatenotification\tests;

use Atk4\Core\AtkPhpunit\TestCase;
use atk4\data\Exception;
use Atk4\Data\Persistence;
use Atk4\Schema\Migration;
use Atk4\Ui\App;
use atkdataupdatenotification\tests\testclasses\OtherUserModel;
use atkdataupdatenotification\tests\testclasses\UserModel;
use atkdataupdatenotification\UpdateNotification;
use atkdataupdatenotification\UpdateNotificationToUser;

class UpdateNotificationTest extends TestCase
{

    private $persistence;
    private $user;

    protected $sqlitePersistenceModels = [
        UserModel::class => [],
        OtherUserModel::class => [],
        UpdateNotification::class => ['userModel' => UserModel::class],
        UpdateNotificationToUser::class => ['userModel' => UserModel::class]
    ];

    protected function getSqliteTestPersistence(array $additionalClasses = [], App $app = null): Persistence
    {
        $allClasses = array_merge($this->sqlitePersistenceModels, $additionalClasses);
        $persistence = new Persistence\Sql('sqlite::memory:');
        $persistence->driverType = 'sqlite';

        $migration = new Migration($persistence);
        foreach ($allClasses as $className => $defaults) {
            $model = new $className($persistence, $defaults);
            $migration->setModel($model);
            $migration->dropIfExists()->create();
        }

        return $persistence;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->persistence = $this->getSqliteTestPersistence();
        $this->user = new UserModel($this->persistence);
        $this->user->save();
    }

    public function testIsReadByLoggedInUser()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message1->save();
        self::assertFalse($message1->isReadByUser($this->user));

        $message1->addMToMRelation(
            new UpdateNotificationToUser($this->persistence, ['userModel' => UserModel::class]),
            $this->user
        );
        self::assertTrue($message1->isReadByUser($this->user));
    }

    public function testMarkMessageAsRead()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message1->save();

        self::assertFalse($message1->isReadByUser($this->user));

        $message1->markAsReadForUser($this->user);
        self::assertTrue($message1->isReadByUser($this->user));
    }

    public function testExceptionMarkAsReadNotLoaded()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        self::expectException(Exception::class);
        $message1->markAsReadForUser($this->user);
    }

    public function testExceptionIsReadByUserNotLoaded()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        self::expectException(Exception::class);
        $message1->isReadByUser($this->user);
    }

    public function testExceptionMarkAsReadUserNotLoaded()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message1->save();
        self::expectException(Exception::class);
        $message1->markAsReadForUser(new UserModel($this->persistence));
    }

    public function testDateFilter()
    {
        $message1 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message1->save();

        $message2 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message2->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message2->save();

        $message3 = new UpdateNotification($this->persistence, ['userModel' => UserModel::class]);
        $message3->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message3->save();

        $message1->addNewerThanCondition((new \DateTime())->modify('-30 Days'));

        self::assertEquals(
            1,
            $message1->action('count')->getOne()
        );

        $message3->set('never_invalid', 1);
        $message3->save();

        self::assertEquals(
            2,
            $message1->action('count')->getOne()
        );
    }

    public function testWithOtherUserModel(): void
    {
        $otherUserModel = new OtherUserModel($this->persistence);
        $ref = $otherUserModel->refModel(UpdateNotificationToUser::class);
        self::assertSame(
            OtherUserModel::class,
            $ref->userModel
        );
    }
}