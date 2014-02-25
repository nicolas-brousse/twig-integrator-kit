<?php

namespace TwigIntegratorKit\Composer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Script
{
    public static function install()
    {
        $dir = realpath(__DIR__ . '/../../../');

        $filesystem = new Filesystem();
        $finder     = new Finder();

        try {
            $filesystem->chmod($dir . '/web/css', 0777);
            $filesystem->chmod($dir . '/web/js', 0777);
            $filesystem->chmod($dir . '/app/cache', 0777);
        }
        catch (\Exception $e) {}

        try {
            $finder->directories()
                ->depth('== 0')
                ->in($dir . '/integration/public')
                ->exclude('css')
                ->exclude('js')
            ;

            foreach ($finder as $file) {
                $filesystem->symlink($file->getRealpath(), 'web/' . $file->getRelativePathname());
            }
        }
        catch (\Exception $e) {}
    }
}
