<?php

namespace TwigIntegratorKit\Composer;

use Symfony\Component\Filesystem\Filesystem;

class Script
{
    public static function install()
    {
        $filesystem = new Filesystem();
        $filesystem->chmod('web/css', 0777);
        $filesystem->chmod('web/js', 0777);
        $filesystem->chmod('web/cache', 0777);
        $filesystem->symlink('../integration/public', 'web/assets');
    }
}
