<?php

namespace h3tech\databaseGenerator\controllers;

use h3tech\databaseGenerator\Module;
use yii\web\Controller;
use yii\filters\VerbFilter;
use Yii;

class GenerationController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'run' => ['post'],
                ],
            ],
        ];
    }

    public function actionRun()
    {
        $accessToken = Yii::$app->request->post('access_token', null);

        /** @var Module $module */
        $module = $this->module;
        $moduleAccessToken = $module->accessToken;

        if ((empty($moduleAccessToken) || $moduleAccessToken === $accessToken)) {
            if ($module->autogenerate) {
                echo 'Not generating manually as automatic generating is enabled.';
            } else {
                if (empty($module->generateDatabase())) {
                    echo 'Database structure already up-to-date.';
                } else {
                    echo 'Database structure updated.';
                }
            }
        } else {
            echo 'Invalid access token!';
        }
    }
}
