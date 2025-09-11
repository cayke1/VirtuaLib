<?php

class HomeController extends RenderView {
    public function index() {

        $users = new UserModel();

        $args = [
            'title' => 'Home',
            'users' => $users->getAll(),
        ];
        $this->loadView('partials/header', ['title' => $args['title']]);
        $this->loadView('home', $args);
        $this->loadView('partials/footer', []);
    }
}