<?php

namespace Alaska\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Alaska\Domain\Article;
use Alaska\Domain\User;
use Alaska\Form\Type\ArticleType;
use Alaska\Form\Type\CommentType;
use Alaska\Form\Type\UserType;

class AdminController {

    /**
     * Admin articles page controller.
     *
     * @param Application $app Silex application
     */
    public function articleAction(Application $app) {
        $articles = $app['dao.article']->findAll();

        return $app['twig']->render('articles.html.twig', array(
            'articles' => $articles));
    }

    /**
     * Admin comments page controller.
     *
     * @param Application $app Silex application
     */
    public function commentAction(Application $app) {
        $comments = $app['dao.comment']->findAll();

        return $app['twig']->render('comments.html.twig', array(
            'comments' => $comments));

    }

    /**
     * Admin users page controller.
     *
     * @param Application $app Silex application
     */
    public function userAction(Application $app) {
        $users = $app['dao.user']->findAll();

        return $app['twig']->render('users.html.twig', array(
            'users' => $users));
    }

    /**
     * Admin flagged comments page controller.
     *
     * @param Application $app Silex application
     */
    public function flaggedAction(Application $app) {
        $commentsFlagged = $app['dao.comment']->findAllFlagged();

        return $app['twig']->render('flagged.html.twig', array(
            'commentsFlagged' => $commentsFlagged));

    }

    /**
     * Add article controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addArticleAction(Request $request, Application $app) {
        $article = new Article();
        $articleForm = $app['form.factory']->create(ArticleType::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $app['dao.article']->save($article);
            $app['session']->getFlashBag()->add('success', "L'article a été crée avec succès.");
        }
        return $app['twig']->render('article_form.html.twig', array(
            'title' => 'New article',
            'articleForm' => $articleForm->createView()));
    }

    /**
     * Edit article controller.
     *
     * @param integer $id Article id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editArticleAction($id, Request $request, Application $app) {
        $article = $app['dao.article']->find($id);
        $articleForm = $app['form.factory']->create(ArticleType::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $app['dao.article']->save($article);
            $app['session']->getFlashBag()->add('success', "L'article a été modifié avec succès.");
        }
        return $app['twig']->render('article_form.html.twig', array(
            'title' => 'Edit article',
            'articleForm' => $articleForm->createView()));
    }

    /**
     * Delete article controller.
     *
     * @param integer $id Article id
     * @param Application $app Silex application
     */
    public function deleteArticleAction($id, Application $app) {
        // Delete all associated comments
        $app['dao.comment']->deleteAllByArticle($id);
        // Delete the article
        $app['dao.article']->delete($id);
        $app['session']->getFlashBag()->add('success', "L'article a été supprimé avec succès.");
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin_articles'));
    }

    /**
     * Edit comment controller.
     *
     * @param integer $id Comment id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editCommentAction($id, Request $request, Application $app) {
        $comment = $app['dao.comment']->find($id);
        $commentForm = $app['form.factory']->create(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $app['dao.comment']->save($comment);
            $app['session']->getFlashBag()->add('success', "Le commentaire a été modifié avec succès.");
        }
        return $app['twig']->render('comment_form.html.twig', array(
            'comment' => $comment,
            'title' => 'Edit comment',
            'commentForm' => $commentForm->createView()));
    }

    /**
     * Delete comment controller.
     *
     * @param integer $id Comment id
     * @param Application $app Silex application
     */
    public function deleteCommentAction($id, Application $app) {
        $app['dao.comment']->deleteWithChildren($id);
        $app['session']->getFlashBag()->add('success', "Le commentaire a été supprimé avec succès.");
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin_comments'));
    }

    public function flagCommentAction($articleId, $commentId, Application $app) {
        $comment = $app['dao.comment']->find($commentId);
        $comment->setFlagged(1);
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', "Le commentaire a été signalé avec succès.");
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate(
            'article',
            array('id' => $articleId)
        ));
    }

    public function unflagCommentAction($articleId, $commentId, Application $app) {
        $comment = $app['dao.comment']->find($commentId);
        $comment->setFlagged(0);
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', "Le commentaire a été approuvé avec succès.");
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate(
            'article',
            array('id' => $articleId)
        ));
    }

    /**
     * Add user controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addUserAction(Request $request, Application $app) {
        $user = new User();
        $userForm = $app['form.factory']->create(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            // generate a random salt value
            $salt = substr(md5(time()), 0, 23);
            $user->setSalt($salt);
            $plainPassword = $user->getPassword();
            // find the default encoder
            $encoder = $app['security.encoder.bcrypt'];
            // compute the encoded password
            $password = $encoder->encodePassword($plainPassword, $user->getSalt());
            $user->setPassword($password);
            $app['dao.user']->save($user);
            $app['session']->getFlashBag()->add('success', "Le membre a été crée avec succès.");
        }
        return $app['twig']->render('user_form.html.twig', array(
            'title' => 'New user',
            'userForm' => $userForm->createView()));
    }

    /**
     * Edit user controller.
     *
     * @param integer $id User id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editUserAction($id, Request $request, Application $app) {
        $user = $app['dao.user']->find($id);
        $userForm = $app['form.factory']->create(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $plainPassword = $user->getPassword();
            // find the encoder for the user
            $encoder = $app['security.encoder_factory']->getEncoder($user);
            // compute the encoded password
            $password = $encoder->encodePassword($plainPassword, $user->getSalt());
            $user->setPassword($password);
            $app['dao.user']->save($user);
            $app['session']->getFlashBag()->add('success', "Le membre a été modifié avec succès.");
        }
        return $app['twig']->render('user_form.html.twig', array(
            'title' => 'Edit user',
            'userForm' => $userForm->createView()));
    }

    /**
     * Delete user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function deleteUserAction($id, Application $app) {
        // Delete the user
        $app['dao.user']->delete($id);
        $app['session']->getFlashBag()->add('success', "Le commentaire a été supprimé avec succès.");
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin_users'));
    }
}
