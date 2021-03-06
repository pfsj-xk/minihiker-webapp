<?php

namespace backend\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Class TestController.
 * Visual feedback and GUI controls for some backend maintenance tasks.
 * @package backend\controllers
 */
class TestController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['admin']
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
//                    'logout' => ['post'],
                ],
            ],
        ];
    }


    /**
     * Run a few tests on the app and display the results on-screen.
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
