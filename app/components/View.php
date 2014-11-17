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
            static::$instance   = new static($config['path'], $config['ext'], $config['layout']);
        }
        return static::$instance;
    }

    protected function __construct($path, $ext, $layout)
    {
        $this->path     = $path;
        $this->ext      = $ext;
        $this->layout   = $layout;
    }

    public function render($viewName, $viewData = array())
    {
        $this->renderAndLoadViewFile($viewName, $viewData, true);
    }

    public function renderPartial($viewName, $viewData = array())
    {
        $this->renderAndLoadViewFile($viewName, $viewData, false);
    }

    protected function renderAndLoadViewFile($viewName, $viewData = array(), $applyLayout = true)
    {
        if(!empty($viewData))
        {
            extract($viewData);
        }
        $path       = static::getPath();
        ob_start();
        require_once($path. DIRECTORY_SEPARATOR . $viewName . $this->getExt());
        $content    = ob_get_contents();
        ob_end_clean();
        if ($applyLayout)
        {
            $content = $this->applyLayout($content, $path);
        }
        echo $content;
    }

    protected function applyLayout($content, $path)
    {
        $layoutName     = $this->getLayoutName();
        ob_start();
        require_once($path . DIRECTORY_SEPARATOR . $layoutName . $this->getExt());
        $wrappedContent = ob_get_contents();
        ob_end_clean();
        return $wrappedContent;
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