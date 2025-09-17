
<?php

class HomeController extends RenderView {
    public function index() {
        $args = [
            'title' => 'Home',
        ];
        $this->loadView('partials/header', ['title' => $args['title']]);
        $this->loadView('home', $args);
        $this->loadView('partials/footer', []);
    }

}