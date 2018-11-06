<?php

namespace Swoft\Admin\Scaffold;

use Swoft\App;
use Swoft\Support\Filesystem;

class ControllerCreator
{
    use GridCreator, FormCreator, ShowCreator;

    /**
     * Controller full name.
     *
     * @var string
     */
    protected $name;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * ControllerCreator constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $base = class_basename($name);
        if (strpos($base, 'Controller') === false) {
            $name .= 'Controller';
        }
        $this->name = $name;

        $this->files = filesystem();
    }

    /**
     * 获取路由前缀
     *
     * @return string
     */
    protected function getRoutePrefix()
    {
        $controller = str__slug(str_replace('Controller', '', class_basename($this->name)));

        $prefix = config('admin.route.prefix');

        return $prefix ? "/{$prefix}/$controller" : "/$controller";
    }

    /**
     * 创建控制器
     *
     * @param $model
     * @param string $primaryKey
     * @param array $fields
     * @param $timestamps
     * @param bool $preview
     * @return string
     * @throws \Exception
     */
    public function create($model, string $primaryKey, array $fields, $timestamps, bool $preview)
    {
        $path = $this->getpath($this->name);

        if ($this->files->exists($path)) {
            throw new \Exception("Controller [$this->name] already exists!");
        }

        $stub = $this->files->get($this->getStub());

        $code = $this->replace($stub, $this->name, $model, $primaryKey, $fields, $timestamps);

        if ($preview) {
            return $code;
        }
        $this->files->put($path, $code);

        return $path;
    }

    /**
     * @param string $stub
     * @param string $name
     * @param string $model
     * @param string $primaryKey
     * @param array $fields
     * @param bool $timestamps
     * @return string
     */
    protected function replace($stub, $name, $model, $primaryKey, array $fields, $timestamps)
    {
        $stub = $this->replaceClass($stub, $name);

        $controller = str_replace('Controller', '', class_basename($this->name));

        return str_replace(
            [
                '{modelNamespace}',
                '{model}',
                '{route}',
                '{controller}',
                '{controller-slug}',
                '{grid}',
                '{show}',
                '{form}',
                '{inserting}',
                '{updating}',
            ],
            [
                $model,
                class_basename($model),
                $this->getRoutePrefix(),
                $controller,
                str__slug($controller),
                $this->createGridPrint($primaryKey, $fields),
                $this->createShowPrint($primaryKey, $fields),
                $this->createFormPrint($primaryKey, $fields, $timestamps),
                $this->createIserting($timestamps),
                $this->createUpdating($timestamps),
            ],
            $stub
        );
    }

    /**
     * @param $timestamps
     * @return string
     */
    protected function createIserting($timestamps)
    {
        if (!$timestamps) {
            return '';
        }

        return <<<EOF
        
            ->saving(function (Form \$form) {
                \$date = date('Y-m-d H:i:s');

                // 由于swoft实体没有自动更新created_at字段的功能,所以新增或编辑时需要手动加
                \$form->input('created_at', \$date);
                \$form->input('updated_at', \$date);
            })
EOF;


    }

    /**
     * @param $timestamps
     * @return string
     */
    protected function createUpdating($timestamps)
    {
        if (!$timestamps) {
            return '';
        }

        return <<<EOF
        
            ->saving(function (Form \$form) {
                \$date = date('Y-m-d H:i:s');

                // 由于swoft实体没有自动更新created_at字段的功能,所以新增或编辑时需要手动加
                \$form->input('updated_at', \$date);
            })
EOF;


    }

    /**
     * Get controller namespace from giving name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace(['{class}', '{namespace}'], [$class, $this->getNamespace($name)], $stub);
    }

    /**
     * Get file path from giving controller name.
     *
     * @param $name
     *
     * @return string
     */
    public function getPath($name)
    {
        $segments = explode('\\', $name);

        array_shift($segments);

        return App::getAlias('@root/app/'.implode('/', $segments)).'.php';
    }

    /**
     * Get stub file path.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__.'/stubs/controller.stub';
    }
}
