<?php

namespace Holerito\controllers;

abstract class Controller
{
      protected function render(string $template, array $data = [])
      {
            extract($data);

            ob_start();
            require __DIR__ . "/../templates/{$template}.template.php";
            return ob_get_clean();
      }
}
