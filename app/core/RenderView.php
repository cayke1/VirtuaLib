<?php

class RenderView {
    public function __construct() {
        // Construtor vazio ou com inicializações necessárias
    }
    
    public function loadView($view, $data = []) {
        $filename = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($filename)) {
            extract($data);
            require_once $filename;
        } else {
            echo "View não encontrada: $view";
        }
    }

    public function render($view, $data = []) {
        $this->loadView($view, $data);
    }
}
?>