<?php
namespace GGS\Controllers;
use GGS\Components\WebApplication;
use GGS\Components\Controller;
use GGS\Helpers\CsrfUtils;
use GGS\Helpers\FormUtils;
use GGS\Helpers\StringUtils;
use GGS\Models;

class Post extends Controller
{
    public function actionList()
    {
        $limit          = WebApplication::$request->getQueryStringParameter('limit', static::MAX_RECORDS_PER_PAGE);
        $page           = WebApplication::$request->getQueryStringParameter('page', 1);
        $offset         = ($page-1) * $limit;
        $posts          = Models\Post::getAll($limit, $offset);
        $pageTitle      = 'Show all posts';
        if (WebApplication::$request->isAjaxRequest())
        {
            WebApplication::$view->renderPartial('post/_list', compact('posts', 'page'));

        }
        else
        {
            WebApplication::$view->render('post/list', compact('posts', 'page', 'pageTitle'));
        }
    }

    public function actionShow()
    {
        $page                   = 1;
        $post                   = static::getModelByRequest('Post');
        $pageTitle              = $post->title;
        $comments               = Models\Comment::getByCriteria(array('postId' => $post->getPkValue()), static::MAX_RECORDS_PER_PAGE);
        $commentForm            = new Models\Comment();
        $commentForm->postId    = $post->getPkValue();
        $formName               = StringUtils::getNameWithoutNamespaces(get_class($commentForm));
        $token                  = CsrfUtils::getNewToken(__FUNCTION__);
        $this->handleCommentAddition($commentForm);
        WebApplication::$view->render('post/show', compact('post', 'comments', 'page', 'commentForm', 'formName', 'token', 'pageTitle'));
    }

    public function actionComments()
    {
        $postId         = WebApplication::$request->getQueryStringParameter('postId');
        if (!isset($postId) || !is_numeric($postId))
        {
            static::exitWithException(new \Exception("Invalid post id supplied to load comments of.", 400));
        }
        $limit          = WebApplication::$request->getQueryStringParameter('limit', static::MAX_RECORDS_PER_PAGE);
        $page           = WebApplication::$request->getQueryStringParameter('page', 1);
        $offset         = ($page-1) * $limit;
        $comments       = Models\Comment::getByCriteria(array('postId' => $postId), $limit, $offset);
        if (WebApplication::$request->isAjaxRequest())
        {
            WebApplication::$view->renderPartial('comment/_list', compact('comments', 'page'));

        }
        else
        {
            //WebApplication::$view->renderPartial('comment/list', compact('comments', 'page'));
            static::exitWithException(new \Exception('Invalid Request.', 400));
        }

    }

    public function actionCreate()
    {
        $post           = new Models\Post();
        $pageTitle      = 'Add Post';
        $token          = CsrfUtils::getNewToken(__FUNCTION__);
        $this->_renderPostEdit($post, $pageTitle, $token);
    }

    public function actionEdit()
    {
        $model          = static::getModelByRequest('Post');
        $pageTitle      = 'Edit Post';
        $token          = CsrfUtils::getNewToken(__FUNCTION__);
        $this->_renderPostEdit($model, $pageTitle, $token);
    }

    public function _renderPostEdit(\GGS\Models\Post $model, $pageTitle, $token)
    {
        $formName       = StringUtils::getNameWithoutNamespaces(get_class($model));
        if (WebApplication::$request->isPostRequest() && $attributes = WebApplication::$request->getPostParameter($formName))
        {
            $model->setAttributes($attributes);
            if ($model->validate())
            {
                if ($pk = $model->save())
                {
                    $redirectUrl = static::createUrl('post', 'show', array('id' => $pk));
                    static::redirect($redirectUrl);
                }
                else
                {
                    static::exitWithException('Failed to save Post record.');
                }
            }
        }
        WebApplication::$view->render('post/create', compact('model', 'formName', 'token', 'pageTitle'));
    }

    protected function handleCommentAddition(\GGS\Models\Comment $commentForm)
    {
        $formName       = StringUtils::getNameWithoutNamespaces(get_class($commentForm));
        if (WebApplication::$request->isAjaxRequest() && WebApplication::$request->isPostRequest() &&
                $attributes = WebApplication::$request->getPostParameter($formName))
        {
            $response       = array();
            $commentForm->setAttributes($attributes);
            if ($commentForm->validate())
            {
                $response['status']     = 'success';
                $response['message']    = 'Comment successfully added.';
                if ($pk = $commentForm->save())
                {
                    $response['id'] = $pk;

                }
                else
                {
                    $response['status']     = 'error';
                    $response['message']    = 'Failed to save Comment record.';
                }


            }
            else
            {
                $response['errors']     = $commentForm->getQualifiedErrorMessageWithInputIds();
                $response['status']     = 'error';
                $response['message']    = 'Please check form for invalid data.';
            }
            echo json_encode($response);
            exit;
        }
    }
}