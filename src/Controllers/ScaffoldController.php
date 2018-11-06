<?php

namespace Swoft\Admin\Controllers;

use Swoft\Admin\Admin;
use Swoft\Admin\Layout\Content;
use Swoft\Admin\Scaffold\ControllerCreator;
use Swoft\Admin\Scaffold\LangCreator;
use Swoft\Admin\Scaffold\MigrationCreator;
use Swoft\Admin\Scaffold\ModelCreator;
use Swoft\Admin\Scaffold\RepositoryCreator;
use Swoft\Admin\Widgets\Card;
use Swoft\Admin\Widgets\Code;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Exception\ForbiddenException;
use Swoft\Migrations\Config;
use Swoft\Migrations\Console\Application;
use Swoft\Support\Input;
use Swoft\Support\SessionHelper;
use Swoft\Support\Url;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use Swoft\Admin\Auth\Database\Menu;

/**
 *
 * @Controller()
 */
class ScaffoldController
{
    /**
     * @RequestMapping("/admin-scaffold", method=RequestMethod::GET)
     * @return mixed
     */
    public function index()
    {
        // 访问权限
        $this->access();

        return Admin::content(function (Content $content) {
            $dbTypes = &MigrationCreator::$types;
            $action  = Url::current();
            $controllerNamespace = "App\\Controllers\\Admin\\";
            $modelNamespace = "App\\Models\\Entity\\";
            $codeId  = 'code-prev';

            $code = $this->getPreviewCode($codeId);

            $content->row(
                new Card(
                    t('Form', 'admin.scaffold'),
                    blade('admin::scaffold.index', compact('dbTypes', 'action', 'controllerNamespace', 'modelNamespace', 'code', 'codeId'))
                )
            );

            $content->breadcrumb(t('Scaffold', 'admin.scaffold'));

        })->header(t('Scaffold', 'admin.scaffold'))
          ->description(t('Build code', 'admin.scaffold'))
          ->response();
    }

    /**
     * @RequestMapping("/admin-scaffold", method=RequestMethod::POST)
     * @return mixed
     */
    public function create()
    {
        // 访问权限
        $this->access();

        $input = Input::make();
        
        $paths = [];
        $message = '';

        try {
            $tablePrefix = $input->post('table_prefix');
            if ($tablePrefix && strpos($tablePrefix, '_') === false) {
                $tablePrefix .= '_';
            }

            $table = $input->post('table_name');
            $controller = $input->post('controller_name');
            $model = $this->normalizeModelName($table, $input->post('model_name')) ?: "App\\Models\\Entity\\".ucfirst(camel__case($table));
            $fields = $input->post('fields');
            $primaryKey = $input->post('primary_key');
            $timestamps = $input->post('timestamps') === 'on';
            $softDeletes = $input->post('soft_deletes') === 'on';
            $preview = $input->post('preview');
            
            $createActions = $input->post('create');

            $table = $tablePrefix.$table;

            $database = array_get(Config::getDatabase(), 'name');

            $fields = array_filter($fields, function ($field) {
                return isset($field['name']) && !empty($field['name']);
            });

            if (
                (
                    in_array('controller', $createActions) ||
                    in_array('migration', $createActions) ||
                    in_array('lang', $createActions)
                )
                && empty($fields)
            ) {
                throw new \Exception(t('Table fields can\'t be empty', 'admin.scaffold'));
            }

            if (in_array('controller', $createActions)) {
                $paths['controller'] = (new ControllerCreator($controller))
                    ->create(
                        $model,
                        $primaryKey,
                        $fields,
                        $timestamps,
                        $preview
                    );

                if ($preview) {
                    // 预览代码
                    $this->setPreviewCode($paths['controller']);
                    flash_input($input->all());
                    return redirect_to(Url::to('/admin-scaffold'));
                }

                $paths['repository'] = (new RepositoryCreator)->create(
                    $controller,
                    $model
                );
            }

            if (in_array('migration', $createActions)) {
                $paths['migration'] = (new MigrationCreator($table))->create(
                    $primaryKey,
                    $fields,
                    $timestamps,
                    $softDeletes
                );
            }

            if (in_array('migrate', $createActions)) {
                $paths['create'] = Application::call('migrate');
            }

            if (in_array('lang', $createActions)) {
                $langCreator = new LangCreator($fields);

                $paths['translation'] = $langCreator->create($controller);
            }

            if (in_array('model', $createActions)) {
                $modelCreator = new ModelCreator($table);

                $paths['model'] = $modelCreator->create($model, $database, $tablePrefix);
            }
        } catch (\Throwable $exception) {
            return $this->backWithException($exception);
        }
        return $this->backWithSuccess($paths, $message);

    }

    protected function setPreviewCode(string $code)
    {
        $session = SessionHelper::wrap();
        if (!$session) {
            return null;
        }
        $session->put('preview__code', $code);
    }

    /**
     * 获取预览代码
     *
     * @param mixed $codeId
     * @return mixed
     */
    protected function getPreviewCode($codeId)
    {
        $session = SessionHelper::wrap();
        if (!$session) {
            return null;
        }
        if (!$code = $session->pull('preview__code')) {
            return null;
        }

        return new Card(null, (new Code($code))->attribute('id', $codeId));
    }

    /**
     * 访问权限控制
     *
     * @throws ForbiddenException
     */
    protected function access()
    {
        // 非debug环境请勿使用代码生成器
        if (!Admin::isDebug()) {
            throw new ForbiddenException("Permission deny");
        }
    }

    /**
     * @param string $table
     * @param string $model
     * @return string
     */
    protected function normalizeModelName(string $table, string $model)
    {
        $model = explode("\\", $model);
        array_pop($model);

        return implode("\\", $model) . "\\" . ucfirst(camel__case($table));
    }

    protected function backWithException(\Exception $exception)
    {
        $cls = get_class($exception);

        flash_input(Input::all());
        admin_error(t('Error', 'admin'),  "[{$cls}] {$exception->getMessage()} | {$exception->getFile()}({$exception->getLine()})");

        return redirect_back();
    }

    protected function backWithSuccess($paths, $message)
    {
        $messages = [];

        foreach ($paths as $name => $path) {
            $messages[] = ucfirst($name).": $path";
        }

        $messages[] = "<br />$message";

        admin_success(t('Success', 'admin'), implode('<br />', $messages));

        return redirect_back();
    }

}
