<?php
namespace GGS\Components;

class View extends ApplicationComponent
{
    /**
     * @var Database
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

    protected function __construct($path = null, $ext = null, $layout = null)
    {
        $this->path     = $path;
        $this->ext      = $ext;
        $this->layout   = $layout;
    }

    public function render($viewName, $viewData = array(), $returnOutput = false)
    {
        return $this->renderAndLoadViewFile($viewName, $viewData, true);
    }

    public function renderPartial($viewName, $viewData = array(), $returnOutput = false)
    {
        return $this->renderAndLoadViewFile($viewName, $viewData, false, $returnOutput);
    }

    protected function renderAndLoadViewFile($viewName, $viewData = array(), $applyLayout = true, $returnOutput = false)
    {
        if(!empty($viewData))
        {
            extract($viewData);
        }
        $path       = static::getPath();

        ob_start();
        require($path. DIRECTORY_SEPARATOR . $viewName . $this->getExt());
        $content    = ob_get_contents();
        ob_end_clean();

        if ($applyLayout)
        {
            $layoutName     = $this->getLayoutName();
            ob_start();
            require($path . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layoutName . $this->getExt());
            $content        = ob_get_contents();
            ob_end_clean();
        }
        if ($returnOutput)
        {
            return $content;
        }
        echo $content;
    }

    protected function getPath()
    {
        if (empty($this->path))
        {
            $this->path = $this->getDefaultPath();
        }
        return $this->path;
    }

    protected function getDefaultPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views';
    }

    protected function getLayoutName()
    {
        if (empty($this->layout))
        {
            $this->layout   = $this->getDefaultLayoutName();
        }
        return $this->layout;
    }

    protected function getDefaultLayoutName()
    {
        return 'default';
    }

    protected function getExt()
    {
        if (empty($this->ext))
        {
            $this->ext  = $this->getDefaultExt();
        }
        return $this->ext;
    }

    protected function getDefaultExt()
    {
        return '.tpl';
    }
}