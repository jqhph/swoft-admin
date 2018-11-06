<?php

namespace Swoft\Admin\Scaffold;

use Swoft\App;
use Swoft\Support\Translator;

class LangCreator
{
    protected $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * 生成语言包
     *
     * @param string $controller
     * @return string
     */
    public function create(string $controller)
    {
        $controller = str_replace('Controller', '', class_basename($controller));

        list($filename, $show) = $this->getLangPath($controller);
        if (is_file($filename)) {
            return t("Language-pack(%s) already exists!", 'admin.scaffold', [$show]);
        }

        $content = [
            'labels' => [
                $controller => $controller,
            ],
            'fields' => [],
            'options' => [],
        ];
        foreach ($this->fields as $field) {
            $content['fields'][$field['name']] = $field['lang'] ?? '';
        }

        if (filesystem()->put($filename, export_array_php($content))) {
            return t("Language-pack(%s) save succeeded!", 'admin.scaffold', [$show]);
        }

        return t("Language-pack(%s) save failed!", 'admin.scaffold', [$show]);
    }

    /**
     * 获取语言包路径
     *
     * @param string $controller
     * @return array
     */
    protected function getLangPath(string $controller)
    {
        $file = Translator::make()->currentPath() . '/' . str__slug($controller) . '.php';

        return [$file, str_replace(alias('@app'), '', $file)];
    }

}