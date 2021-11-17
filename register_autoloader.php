<?php

spl_autoload_register(function (string $className): void {
    static $classMap;

    if (empty($classMap)) {
        $classMap = require_once __DIR__ . '/src/bundle/Resources/mappings/class-map.php';
    }

    if (!empty($classMap[$className])) {
        class_exists($classMap[$className]);
    }
});
