<?php

namespace Swoft\Admin\Scaffold;

use Swoft\App;

class RepositoryCreator
{
    protected $path = '@root/app/Admin/Repositories';

    /**
     * @param string $controllerClass
     * @param string $modelClass
     * @return string
     */
    public function create(string $controllerClass, string $modelClass)
    {
        $baseController = class_basename($controllerClass);
        $controller = str_replace('Controller', '', $baseController);

        $model = class_basename($modelClass);

        $path = "{$this->path}/{$controller}.php";
        if (is_file($path)) {
            return t("Repository(%s) already exists!", 'admin.scaffold', ['App\\Admin\\Repositories\\'.$controller]);
        }

        $files = \filesystem();

        $content = $files->get($this->stub());

        $files->put(APP::getAlias($path), str_replace([
            '{controllerClass}',
            '{baseController}',
            '{controller}',
            '{modelClass}',
            '{model}',
        ], [
            $controllerClass,
            $baseController,
            $controller,
            $modelClass,
            $model
        ], $content));

        return $path;
    }

    protected function stub()
    {
        return __DIR__.'/stubs/repository.stub';
    }
}
