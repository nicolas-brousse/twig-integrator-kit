<?php

namespace TwigIntegratorKit;

use Assetic;

use Silex;
use Silex\Application as BaseApplication;
use SilexAssetic;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class Application extends BaseApplication
{
    /**
     * Construct.
     * 
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this['debug']    = true;
        $this['root_dir'] = realpath(__DIR__ . '/../../');

        $this->registerTwig();
        $this->registerAssetic();
        $this->defineRoutes();
    }

    /**
     * Define routes for this application
     */
    private function defineRoutes()
    {
        $app = $this;

        $this->get('{path}', function($path) use ($app) {
            $path = trim($path, '/');

            if (empty($path)) {
                $path = 'index';
            }

            return $app['twig']->render($path . '.html.twig');
        })
        ->assert('path', '.*');
    }

    /**
     * Register the Assetic provider
     */
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

                $fm->set('lessphp',   new Assetic\Filter\LessphpFilter());

                return $fm;
            })
        );
    }

    /**
     * Register the Twig provider
     */
    private function registerTwig()
    {
        $this->register(new Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array(
                __DIR__ . '/Resources/views',
                $this['root_dir'] . '/integration/views',
            ),
            'twig.options' => array(
                'cache' => $this['root_dir'] . '/web/cache/twig',
            ),
        ));

        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addExtension(new TwigExtension($app));

            return $twig;
        }));
    }
}
