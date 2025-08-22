<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\FixturesTrait;
use RonasIT\Support\Traits\MockTrait;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use ReflectionClass;

class TestCase extends BaseTestCase
{
    use FixturesTrait;
    use MockTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
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

                        if (in_array('mail', $notification['channels'])) {
                            $notificationTemplate = view($mailMessage->view, $mailMessage->viewData)->render();

                            $this->assertFixtureWithoutType("mail_notifications/{$fixtureName}_{$key}_template.html", $notificationTemplate, $exportMode);
                        }

                        $notification['notification'] = $this->getObjectAttributes($notification['notification']);
                        $notification['subject'] = $mailMessage->subject;
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
}
