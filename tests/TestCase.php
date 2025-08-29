<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\TestingTrait;
use RonasIT\TelescopeExtension\Tests\Support\MailsMockTrait;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use ReflectionClass;

class TestCase extends BaseTestCase
{
    use TestingTrait;
    use MailsMockTrait { MailsMockTrait::assertFixture insteadof TestingTrait; }

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();
    }

    protected function mockEnvironment(string $environment): void
    {
        $this->app->detectEnvironment(fn () => $environment);
    }

    protected function getPackageProviders($app): array
    {
        return [
            TelescopeExtensionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $config = $app->get('config');

        $config->set('logging.default', 'errorlog');

        $config->set('database.default', 'testbench');

        $config->set('telescope.storage.database.connection', 'testbench');

        $config->set('queue.batching.database', 'testbench');

        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give('testbench');
    }

    protected function defineEnvironment($app): void
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    protected function assertNotificationSent(string $fixtureName, bool $exportMode = false): void
    {
        $notifications = Notification::sentNotifications();

        $actualData = [];

        foreach ($notifications as $modelClassName => $notifiableIDs) {
            foreach ($notifiableIDs as $notifiableId => $modelNotifications) {
                foreach ($modelNotifications as $notificationClassName => $modelNotification) {
                    foreach ($modelNotification as $key => $notification) {
                        $mailMessage = $notification['notification']->toMail($notification['notifiable']);

                        $notification['notification'] = $this->getObjectAttributes($notification['notification']);
                        $notification['subject'] = $mailMessage->subject;
                        $notification['mailable'] = $mailMessage;
                        unset($notification['notification']['id']);

                        $actualData[$modelClassName][$notifiableId][$notificationClassName][] = $notification;
                    }
                }
            }
        }

        $preparedActualData = json_decode(json_encode($actualData), true);

        $this->assertEqualsFixture("notifications/{$fixtureName}", $preparedActualData, $exportMode);
    }

    public function assertFixtureWithoutType(string $fixtureName, $data, bool $exportMode = false): void
    {
        if ($exportMode || $this->globalExportMode) {
            $this->exportContentWithoutType($data, $fixtureName);
        }

        $this->assertEquals($this->getFixture($fixtureName), $data);
    }

    protected function exportContentWithoutType($content, string $fixtureName): void
    {
        file_put_contents($this->getFixturePath($fixtureName), $content);
    }

    protected function getObjectAttributes(object $object): array
    {
        $result = [];

        $properties = (new ReflectionClass($object))->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            $result[$property->getName()] = $value;
        }

        return json_decode(json_encode($result), true);
    }

    protected function getProtectedProperty(object $object, string $propertyName)
    {
        $reflector = new ReflectionClass($object);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    protected function assertScheduledEventEquals(Event $event, string $command, string $cron): void
    {
        $this->assertTrue(Str::endsWith($event->command, $command));

        $this->assertEquals($cron, $event->getExpression());
    }

    protected function assertScheduledEventExecuted(Event $event, bool $isExecuted): void
    {
        $filters = $this->getProtectedProperty($event, 'filters');
        $filterClosure = Arr::first($filters);

        $this->assertEquals($isExecuted, $filterClosure());
    }
}
