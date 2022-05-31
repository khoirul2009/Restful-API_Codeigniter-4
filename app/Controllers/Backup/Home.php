<?php

namespace App\Controllers;

use App\Models\UsersModel;

class Home extends BaseController
{
    protected $model;
    public function __construct()
    {
        $this->model = new UsersModel();
    }
    public function index()
    {
        echo '
        <a href="<?=base_url();?>/users"></a>';
    }
}
