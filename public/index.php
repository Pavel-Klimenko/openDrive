<?php
//TODO версию php 8.1 поставить
//phpinfo();
//exit();
use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
