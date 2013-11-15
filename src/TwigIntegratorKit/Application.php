<?php

namespace TwigIntegratorKit;

use scssc;
use scss_compass;

use Silex;
use Silex\Application as BaseApplication;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class Application extends BaseApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this['debug']    = true;
        $this['root_dir'] = __DIR__ . '/../../';

        $this->registerTwig();
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $app = $this;

        // Route for files .scss
        $this->get('assets/{filename}', function($filename) use ($app) {

            $filepath = $app['root_dir'] . '/integration/public/' . $filename;

            // Check if file exists
            if (! file_exists($filepath)) {
                throw new NotFoundHttpException(sprintf("Scss file '%s' not found", basename($filepath)));
            }

            $scss = new scssc();
            new scss_compass($scss);
            return new Response($scss->compile(file_get_contents($filepath)), 200, array(
                'Content-Type' => 'text/css',
            ));
        })
        ->assert('filename', '.*\.scss$');

        // Route for views
        $this->get('/', function() use ($app) {
            return $app['twig']->render('index.html.twig');
        });

        // Route for public

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
