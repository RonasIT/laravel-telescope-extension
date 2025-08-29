<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Illuminate\Mail\Mailable;

trait MailsMockTrait
{
    protected function assertFixture(array $expectedMailData, Mailable $mail, bool $exportMode = false): void
    {
        $mailContent = view($mail->content()->view, $mail->viewData)->render();

        $globalExportMode = $this->globalExportMode ?? false;

        if ($exportMode || $globalExportMode) {
            $this->exportContent($mailContent, $expectedMailData['fixture']);
        }

        $fixture = $this->getFixture($expectedMailData['fixture']);

        $this->assertEquals(
            expected: $fixture,
            actual: $mailContent,
            message: "Fixture {$expectedMailData['fixture']} does not equals rendered mail.",
        );
    }
}
