<?php
namespace GGS\Controllers;
use GGS\Components\Application;
use GGS\Components\Controller;
use GGS\Models;

class Post extends Controller
{
    public function actionList()
    {
        $posts      = Models\Post::getAll();
        $pageTitle  = 'Show all posts';
        Application::$view->render('post/list', compact('posts', 'pageTitle'));
    }
}