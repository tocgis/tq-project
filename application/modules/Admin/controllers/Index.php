<?php

namespace App\Admin;

use Qh\Mvc\Controller\WebApp;

/**
 * Index
 *
 * @return {[type]
 */
class IndexController extends WebApp
{

    public function indexAction()
    {
        echo 'This is Admin module.';
        return false;
    }


}
