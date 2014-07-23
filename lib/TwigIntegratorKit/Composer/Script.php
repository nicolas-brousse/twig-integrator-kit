<?php

namespace TwigIntegratorKit\Composer;

use Composer\Script\Event;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Script
{
    public static function install(Event $event)
    {
        $io  = $event->getIO();
        $dir = realpath(__DIR__ . '/../../../');

        $filesystem = new Filesystem();
        $finder     = new Finder();

        self::chmod($dir . '/web/css', 0777);
        self::chmod($dir . '/web/js', 0777);
        self::chmod($dir . '/app/cache', 0777);

        $io->write('<info>Update twig-integrator web/* chmod</info>');

        // Temporary Fix
        // TODO: choose between /assets symlink or /assets/* symlinks
        self::symlink($dir . '/integration/public', $dir . '/web/assets');

        $finder->directories()
            ->depth('== 0')
            ->in($dir . '/integration/public')
            ->exclude('css')
            ->exclude('js')
        ;

        foreach ($finder as $file) {
            self::symlink($file->getRealpath(), $dir . '/web/' . $file->getRelativePathname());
        }

        $io->write('<info>Generate twig-integrator symlinks</info>');
    }

    private static function chmod($dir, $mode)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->chmod($dir, $mode);
        }
        catch (\Exception $e) {}
    }

    private static function symlink($origin, $target)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->symlink($origin, $target);
        }
        catch (\Exception $e) {}
    }
}
