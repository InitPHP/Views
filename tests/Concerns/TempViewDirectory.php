<?php

declare(strict_types=1);

namespace InitPHP\Views\Tests\Concerns;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * Creates throwaway directories under the system temp path and removes them
 * after each test, so file-system backed adapters can be exercised for real.
 */
trait TempViewDirectory
{
    /** @var list<string> */
    private array $tempDirs = [];

    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('initphp_views_', true);
        mkdir($dir, 0777, true);
        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function writeView(string $dir, string $relativePath, string $contents): string
    {
        $full = $dir . DIRECTORY_SEPARATOR . $relativePath;
        $parent = \dirname($full);
        if (!is_dir($parent)) {
            mkdir($parent, 0777, true);
        }
        file_put_contents($full, $contents);

        return $full;
    }

    /**
     * @after
     */
    protected function removeTempDirs(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->deleteRecursively($dir);
        }
        $this->tempDirs = [];
    }

    private function deleteRecursively(string $path): void
    {
        if (is_dir($path)) {
            foreach (scandir($path) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $this->deleteRecursively($path . DIRECTORY_SEPARATOR . $entry);
            }
            rmdir($path);

            return;
        }
        unlink($path);
    }
}
