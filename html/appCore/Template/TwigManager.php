<?php

namespace appCore\Template;

/* ======================================================================== \
  |   FORMA - The E-Learning Suite                                            |
  |                                                                           |
  |   Copyright (c) 2013 (Forma)                                              |
  |   http://www.formalms.org                                                 |
  |   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
  |                                                                           |
  |   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
  |   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
  \ ======================================================================== */

use appCore\Template\Extenstions\FormExtension;
use appCore\Template\Extenstions\GetExtension;
use appCore\Template\Extenstions\LangExtension;
use appCore\Template\Extenstions\LayoutExtension;
use appCore\Template\Extenstions\UiFeedbackExtension;
use appCore\Template\Extenstions\UtilExtension;
use appCore\Template\Extenstions\YuiExtension;
use appCore\Template\Services\ClientService;
use Twig\Extension\OptimizerExtension;
use Twig\Extensions\ArrayExtension;
use Twig\Extensions\DateExtension;
use Twig\Extensions\I18nExtension;
use Twig\Extensions\IntlExtension;
use Twig\Extensions\TextExtension;

class TwigManager
{

    private static $instance = null;
    private $twig = null;

    /**
     * Singleton class, the constructor is private
     */
    private function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader();
        $debug = \Get::cfg('twig_debug', false);
        $this->twig = new \Twig\Environment($loader, array(
            'cache' => $debug ? false : _files_ . '/cache/twig',
            'debug' => $debug
        ));
        $this->addDefaultPaths();
        $this->twig->addExtension(new ArrayExtension());
        $this->twig->addExtension(new DateExtension());
        $this->twig->addExtension(new FormExtension());
        $this->twig->addExtension(new GetExtension());
        //$this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new I18nExtension());
        $this->twig->addExtension(new LangExtension());
        $this->twig->addExtension(new LayoutExtension());
        $this->twig->addExtension(new UiFeedbackExtension());
        $this->twig->addExtension(new UtilExtension());
        $this->twig->addExtension(new YuiExtension());
        $this->twig->addExtension(new TextExtension());

        $this->twig->addFunction(new \Twig\TwigFunction('evalPhp', function ($phpCode, $args = array()) {
            return call_user_func_array($phpCode, $args);
        }, array(
            'is_safe' => array('html')
        )));
        $this->twig->addGlobal('clientConfig', (new ClientService())->getConfig());
        $this->twig->addGlobal('GLOBALS', $GLOBALS);
        if ($debug) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
    }

    /**
     * Get the TwigManager instance
     *
     * @param string $mvc_name
     * @return TwigManager
     * @throws Exception
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    private function addDefaultPaths()
    {
        $defaultPaths = [
            _adm_ . '/views',
            _lms_ . '/views',
            _lms_ . '/admin/views',
            _templates_ . '/' . getTemplate() . '/layout'
        ];

        foreach ($defaultPaths as $path) {
            $this->addPathInLoader($path);
        }
    }

    public function addPathInLoader($view_path)
    {

        $this->twig->getLoader()->addPath($view_path);
    }

    public function render($view_name, $data_for_view, $view_path = null)
    {
        if ($view_path == null) {
            throw new \Exception('mvc_name cannot be null!');
        }

        if (!$this->twig->getLoader()->exists($view_name)) {
            $this->twig->getLoader()->addPath($view_path);
        }

        return $this->twig->render($view_name, $data_for_view);
    }

    public static function getCacheDir()
    {
        return _files_ . '/cache/twig';
    }

}
