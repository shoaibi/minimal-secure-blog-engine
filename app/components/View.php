<?php
namespace GGS\Components;

/**
 * View component to handle presentation of output
 * Class View
 * @package GGS\Components
 */
class View extends ApplicationComponent
{
    /**
     * @var View
     */
    private static $instance;

    /**
     * @var null
     */
    protected $path     = null;

    /**
     * @var null
     */
    protected $layout   = null;

    /**
     * @var null
     */
    protected $ext      = null;

    /**
     * @inheritdoc
     */
    public static function getInstance(array $config)
    {
        if (!isset(static::$instance))
        {
            $path   = null;
            $ext    = null;
            $layout = null;
            extract($config);
            static::$instance   = new static($path, $ext, $layout);
        }
        return static::$instance;
    }

    /**
     * Bootstrap view component
     * @param null $path
     * @param null $ext
     * @param null $layout
     */
    protected function __construct($path = null, $ext = null, $layout = null)
    {
        $this->path     = (isset($path)) ? $path : $this->getDefaultPath();
        $this->ext      = (isset($ext))? $ext : $this->getDefaultExt();
        $this->layout   = (isset($layout)) ? $layout : $this->getDefaultLayoutName();
    }

    /**
     * Render view with its layout provided its name and data
     * @param $viewName
     * @param array $viewData
     * @param bool $returnOutput
     * @return string
     */
    public function render($viewName, $viewData = array(), $returnOutput = false)
    {
        return $this->renderAndLoadViewFile($viewName, $viewData, true, $returnOutput);
    }

    /**
     * Render view without layout
     * @param $viewName
     * @param array $viewData
     * @param bool $returnOutput
     * @return string
     */
    public function renderPartial($viewName, $viewData = array(), $returnOutput = false)
    {
        return $this->renderAndLoadViewFile($viewName, $viewData, false, $returnOutput);
    }

    /**
     * Render view provided its name and data
     * @param $viewName
     * @param array $viewData
     * @param bool $applyLayout
     * @param bool $returnOutput
     * @return string
     */
    protected function renderAndLoadViewFile($viewName, $viewData = array(), $applyLayout = true, $returnOutput = false)
    {
        // setup the view variables
        if(!empty($viewData))
        {
            extract($viewData);
        }

        // render the view itself
        ob_start();
        require($this->path . DIRECTORY_SEPARATOR . $viewName . $this->ext);
        $content    = ob_get_contents();
        ob_end_clean();

        // do we need to apply layout?
        if ($applyLayout)
        {
            // render the layout
            ob_start();
            require($this->path . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->layout . $this->ext);
            $content        = ob_get_contents();
            ob_end_clean();
        }
        // shall we echo the output or return it?
        if ($returnOutput)
        {
            return $content;
        }
        echo $content;
    }

    /**
     * Get the default path for application views
     * @return string
     */
    protected function getDefaultPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * Get default layout name
     * @return string
     */
    protected function getDefaultLayoutName()
    {
        return 'default';
    }

    /**
     * get default layout extension
     * @return string
     */
    protected function getDefaultExt()
    {
        return '.tpl';
    }
}