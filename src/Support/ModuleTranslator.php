<?php

namespace Dcat\Admin\Support;

use Dcat\Admin\Admin;
use Illuminate\Support\Str;

class ModuleTranslator extends Translator
{
    protected string $modulePath;
    protected string $topModule;

    protected function module(): string
    {
        if (!isset($this->modulePath)) {
            $controller = request()->route()->getControllerClass();
            if ($modules = Helper::getControllerModules($controller)) {
                if (count($modules) > 0) {
                    $this->topModule = strtolower($modules[0]);
                    $this->modulePath = Helper::getModulePath($modules);
                } else {
                    $this->topModule = '';
                    $this->modulePath = '';
                }
            }
        }

        return $this->modulePath;
    }

    protected function moduleKey($key): string
    {
        return "$this->topModule/common.$key";
    }

    protected function parseTrans(): ?string
    {
        $request = request();
        $trans = $request->input('_trans_');
        if (!$trans && $request->routeIs('dcat.admin.dcat-api.form') && $payload = $request->input('_payload_')) {
            $payload = json_decode($payload, true);
            if (isset($payload['_trans_'])) {
                $trans = $payload['_trans_'];
            }
        }

        if ($trans) {
            $this->path = $trans;
            $path = explode('/', $trans);
            if (count($path) > 1) {
                array_splice($path, -1);
                $this->modulePath = implode('/', $path);
                $this->topModule = $path[0];
            }
            return $this->path;
        }

        return null;
    }

    public function getPath(): ?string
    {
        if (!$this->path && $this->parseTrans() === null) {
            $this->path = Admin::context()->translation ?: $this->module() . '/' . admin_controller_slug();
        }

        return $this->path;
    }

    public function transOption($optionValue, $field, $replace = [], $locale = null)
    {
        return $this->trans("{$this->getPath()}.options.{$field}.$optionValue", $replace, $locale);
    }

    public function trans($key, array $replace = [], $locale = null)
    {
        dump($key);
        $method = $this->getTranslateMethod();

        if ($this->translator->has($key)) {
            return $this->translator->$method($key, $replace, $locale);
        }

        $arr = explode('.', Str::after($key, '/'));
        if (count($arr) > 1) {
            unset($arr[0]);
            $newKey = implode('.', $arr);

            if ($this->module()) {
                $moduleKey = $this->moduleKey($newKey);
                if ($this->translator->has($moduleKey)) {
                    return $this->translator->$method($moduleKey, $replace, $locale);
                }
            }
            if (mb_strpos($newKey, 'global.') !== 0) {
                $globalKey = "global.$newKey";

                if ($this->translator->has($globalKey)) {
                    return $this->translator->$method($globalKey, $replace, $locale);
                }
            }
        }

        return last(explode('.', $key));
    }
}
