<?php
namespace Qh;

/**
 * 自动加载
 */
class Loader
{
    protected $_eventsManager   = null;
    protected $_foundPath       = null;
    protected $_checkedPath     = null;
    protected $_extensions      = ["php"];
    protected $_directories     = [];
    protected $_namespaces      = [];
    protected $_classes         = [];
    protected $_files           = [];
    protected $_registered      = false;
    public static    $_instance;

    /**
     * 获取单例
     *
     * @method getInstance
     * @return [type]
     */
    public static function getInstance() {
        if ( is_null(self::$_instance) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    /**
     * 注册自动加载路径
     *
     * @method registerDirs
     * @param  array        $directories
     * @param  boolean      $merge
     * @return [type]
     */
    public function registerDirs(array $directories, $merge = false)
    {
        if ($merge) {
            $this->_directories = array_merge($this->_directories, $directories);
        } else {
            $this->_directories = $directories;
        }

    }

    /**
     * Returns the directories currently registered in the autoloader
     *
     * @method getDirs
     * @return array
     */
    public function getDirs() : array
    {
        return $this->_directories;
    }

    /**
     * 注册名称空间
     *
     * @method registerNamespaces
     * @param  [type]             $argv
     * @return [type]
     */
    public function registerNamespaces(array $namespaces, $merge = false)
    {
        $preparedNamespaces = $this->prepareNamespace($namespaces);

        if ($merge) {

            foreach ($prepareNamespace as $name=>$paths) {
                if (!isset ($this->_namespaces[$name])) {
                    $this->_namespaces[$name] = [];
                }

                $this->_namespaces[$name] = array_merge($this->_namespaces[$name], $paths);
            }
        } else {
            $this->_namespaces = $preparedNamespaces;
        }

        return $this;
    }

    /**
     * prepareNamespace
     * @method prepareNamespace
     * @param  array            $namespace
     * @return array
     */
    protected function prepareNamespace(array $namespace) : array
    {
        $prepared = [];
        foreach ($namespace as $name=>$paths) {
            if (is_array($paths) == FALSE) {
                $localPaths = [$paths];
            } else {
                $localPaths = $paths;
            }

            $prepared[$name] = $localPaths;
        }

        return $prepared;
    }

    /**
     * Returns the namespaces currently registered in the autoloader
     */
    public function getNamespaces() : array
    {
        return $this->_namespaces;
    }


    /**
     * Autoloads the registered classes
     *
     * @method autoload
     * @param  string   $className
     * @return boolean
     */
    public function autoload($className)
    {

        $eventsManager  = $this->_eventsManager;
        $classes        = $this->_classes;

        if (isset($classes[$className])) {
            $filePath = $classes[$className];
            require $filePath;
            return true;
        }

        $extensions     = $this->_extensions;

        $ds = DIRECTORY_SEPARATOR;
        $ns = "\\";

        $namespaces = $this->_namespaces;

        foreach ($namespaces as $nsPrefix=>$directories) {

            /**
             * The class name must start with the current namespace
             */
            if (!strpos($className, $nsPrefix) === 0) {
                continue;
            }

            /**
             * Append the namespace separator to the prefix
             */
            $fileName = substr($className, strlen($nsPrefix . $ns));
            $fileName = str_replace($ns, $ds, $fileName);

            if (!$fileName) {
                continue;
            }

            foreach ($directories as $directory) {
                /**
                 * Add a trailing directory separator if the user forgot to do that
                 */
                $fixedDirectory = rtrim($directory, $ds) . $ds;

                foreach ($extensions as $extension) {
                    $filePath = $fixedDirectory . $fileName . "." . $extension;

                    if (file_exists($filePath)) {

                        require $filePath;
                        return true;
                    }
                }

            }

        }

        /**
         * Change the namespace separator by directory separator too
         */
        $nsClassName = str_replace("\\", $ds, $className);

        /**
         * Checking in directories
         */
        $directories = $this->_directories;

        foreach ($directories as $directory) {

            /**
             * Add a trailing directory separator if the user forgot to do that
             */
            $fixedDirectory = rtrim($directory, $ds) . $ds;

            foreach ($extensions as $extension) {

                /**
                 * Create a possible path for the file
                 */
                $filePath = $fixedDirectory . $nsClassName . "." . $extension;

                /**
                 * Check in every directory if the class exists here
                 */
                if (file_exists($filePath)) {

                    require $filePath;

                    return true;
                }
            }
        }

        return false;

    }

    /**
     * Register the autoload method
     */
    public function register()
    {
        if ($this->_registered === false) {
            /**
             * Loads individual files added using Loader->registerFiles()
             */
            $this->loadFiles();

            /**
             * Registers directories & namespaces to PHP's autoload
             */
            spl_autoload_register([$this, "autoLoad"],false, true);

            $this->_registered = true;
        }
        return $this;
    }

    /**
     * Unregister the autoload method
     */
    public function unregister()
    {
        if ($this->_registered === true) {
            spl_autoload_unregister([$this, "autoLoad"]);
            $this->_registered = false;
        }
        return $this;
    }

    /**
     * Checks if a file exists and then adds the file by doing virtual require
     */
    public function loadFiles()
    {

        foreach ($this->_files as $filePath) {
            /**
             * Check if the file specified even exists
             */
            if (file_exists($filePath)) {

                require $filePath;
            }
        }
    }

}
