<?php

class NotFoundController extends RenderView
{

  public function index()
  {

    $this->loadView('partials/header', [
      "title" => "Página não encontrada",
    ]);
    $this->loadView('404', []);
    $this->loadView('partials/footer', []);
  }
}