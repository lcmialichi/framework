<?php

namespace App\Helpers;

class ClassLoader
{
    private $unload = [];

    public function __construct(
        private string $namespace,
        private bool $recursive = false
    ) {
    }

    public function namespaceToDir()
    {
        return str_replace("\\", "/", $this->namespace());
    }

    public function namespace ()
    {
        return $this->namespace;
    }

    public function appRoot()
    {
        return ROOT_PATH;
    }

    public function load(): array
    {
        if (!is_dir($path = $this->getNamespaceClass())) {
            throw new \Exception("Directory for the given namespace is invalid!");
        }

        $mappedClasses = [];
        foreach (scandir($path) as $item) {
            if (in_array($item, [".", ".."])) {
                continue;
            }

            $filePath = $path . "/" . $item;
            if (is_dir($filePath) && $this->recursive()) {
                $this->loadDirectory($this->namespace() . "\\" . $item, $mappedClasses);

            }
            if (file_exists($filePath)) {
                $this->loadClass($this->buildClassNamespace($item), $mappedClasses);
            }
        }

        return $mappedClasses;
    }

    private function loadDirectory(string $namespace, array &$mappedClasses): void
    {
        if (!$this->namespaceIsAllowed($namespace) || !$namespace) {
            return;
        }

        $loaded = self::createRecursive($namespace);
        if ($loaded && count($loaded) > 0) {
            array_push($mappedClasses, ...$loaded);
        }
    }

    private function loadClass(string $class, array &$mappedClasses): void
    {
        if (!$this->namespaceIsAllowed($class) || !$class) {
            return;
        }

        $reflection = new \ReflectionClass($class);
        if ($reflection->isInstantiable()) {
            $mappedClasses[] = $class;
        }
    }

    private function buildClassNamespace(string $class): false|string
    {
        if (!endsWith(".php", $class)) {
            return false;
        }

        $namespace = $this->namespace() . "\\" . pascalCase(str_replace(".php", "", $class));
        if (!class_exists($namespace)) {
            return false;
        }

        return $namespace;

    }

    private function psr4LoadedClasses()
    {
        return composer("autoload.psr-4");
    }

    private function getNamespaceClass()
    {
        $psr4 = $this->psr4LoadedClasses();
        $realPath = explode("\\", $this->namespace());
        $root = array_shift($realPath);
        return $realPath = $this->appRoot() . "/" . $psr4[$root . "\\"] . implode("/", $realPath);
    }

    private function recursive()
    {
        return $this->recursive;
    }

    public static function createRecursive(string $namespace)
    {
        return (new self($namespace, true))->load();
    }

    public function unload(array $classes = [])
    {
        $this->unload = $classes;
    }

    public function namespaceIsAllowed(string $class)
    {
        return !isset(array_flip($this->unload)[$class]);
    }
}
