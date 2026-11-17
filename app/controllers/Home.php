<?php

class Home extends Controller {
    public function index() {
        AuthHelper::redirectBasedOnRole();
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }
}
