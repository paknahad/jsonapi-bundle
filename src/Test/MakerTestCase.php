<?php

namespace Paknahad\JsonApiBundle\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\Test\MakerTestDetails;

class MakerTestCase extends TestCase
{
    protected function executeMakerCommand(MakerTestDetails $testDetails)
    {
        static $isFirst = true;

        if (!$testDetails->isSupportedByCurrentPhpVersion()) {
            $this->markTestSkipped();
        }

        $testEnv = MakerTestEnvironment::create($testDetails);

        // prepare environment to test
        $testEnv->prepare();

        // run tests
        $makerTestProcess = $testEnv->runMaker();
        $files = $testEnv->getGeneratedFilesFromOutputText();

        foreach ($files as $file) {
            $this->assertTrue($testEnv->fileExists($file));

            if ('.php' === substr($file, -4)) {
                $csProcess = $testEnv->runPhpCSFixer($file);

                $this->assertTrue($csProcess->isSuccessful(), sprintf('File "%s" has a php-cs problem: %s', $file, $csProcess->getOutput()));
            }
        }

        if (!$isFirst) {
            // run internal tests
            $internalTestProcess = $testEnv->runInternalTests();
            if (null !== $internalTestProcess) {
                $this->assertTrue($internalTestProcess->isSuccessful(), sprintf("Error while running the PHPUnit tests *in* the project: \n\n %s \n\n Command Output: %s", $internalTestProcess->getOutput(), $makerTestProcess->getOutput()));
            }
        }

        $isFirst = false;

        // checkout user asserts
        if (null == $testDetails->getAssert()) {
            $this->assertContains('Success', $makerTestProcess->getOutput(), $makerTestProcess->getErrorOutput());
        } else {
            ($testDetails->getAssert())($makerTestProcess->getOutput(), $testEnv->getPath());
        }
    }

    protected function assertContainsCount(string $needle, string $haystack, int $count)
    {
        $this->assertEquals(1, substr_count($haystack, $needle), sprintf('Found more than %d occurrences of "%s" in "%s"', $count, $needle, $haystack));
    }
}
