<?php

namespace TwigIntegratorKit;

use Assetic;

use scssc;
use scss_compass;

use Silex;
use Silex\Application as BaseApplication;
use SilexAssetic;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class Application extends BaseApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this['debug']    = true;
        $this['root_dir'] = realpath(__DIR__ . '/../../');

        $this->registerTwig();
        $this->registerAssetic();
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $app = $this;

        // Route for views
        $this->get('{path}', function($path) use ($app) {
            $path = trim($path, '/');

            if (empty($path)) {
                $path = 'index';
            }

            return $app['twig']->render($path . '.html.twig');
        })
        ->assert('path', '.*');
    }

    private function registerAssetic()
    {
        $this->register(new SilexAssetic\AsseticServiceProvider(), array(
            'assetic.options' => array(
                'debug'            => $this['debug'],
                'auto_dump_assets' => $this['debug'],
            ),
        ));

        $this['assetic.path_to_source'] = $this['root_dir'] . '/integration/public';
        $this['assetic.path_to_web']    = $this['root_dir'] . '/web';

        $this['assetic.filter_manager'] = $this->share(
            $this->extend('assetic.filter_manager', function($fm, $app) {
                $f = new Assetic\Filter\ScssphpFilter();
                $f->enableCompass();
                $fm->set('scssphp', $f);

                return $fm;
            })
        );
    }

    private function registerTwig()
    {
        $this->register(new Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array(
                __DIR__ . '/Resources/views',
                $this['root_dir'] . '/integration/views',
            ),
        ));

        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addExtension(new TwigExtension($app));

            return $twig;
        }));
    }
}
