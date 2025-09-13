<?php
require_once "model/mUser.php";

class cUser {
    private $model;
    public function __construct() {
        $this->model = new mUser();
    }

    public function getUserById($id) {
        return $this->model->getUserById($id);
    }
}
