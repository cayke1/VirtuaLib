<?php

class RenderView
{
  public function loadView($view, $args)
  {
    $filename = __DIR__ . "/../views/$view.php";
    if (file_exists($filename)) {
      $this->render($view, $args);
    } else {
      echo "View not found: $view";
    }
  }

  public function render($view, $args) {
    extract($args);
    require_once __DIR__ . "/../views/$view.php";
  }
}