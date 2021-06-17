<?php

namespace app\controllers;

use app\models\emailInfo;
use app\models\Instagram;
use app\models\InstagramUser;
use app\models\NominationForm;
use app\models\SendEmail;
use app\models\UserMetaData;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    /*public function actionIndex()
    {
        return $this->render('index');
    }*/

    /**
     * Login action.
     *
     * @return Response|string
     */
//    public function actionLogin()
//    {
//        if (!Yii::$app->user->isGuest) {
//            return $this->goHome();
//        }
//
//        $model = new LoginForm();
//        if ($model->load(Yii::$app->request->post()) && $model->login()) {
//            return $this->goBack();
//        }
//
//        $model->password = '';
//        return $this->render('login', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Logout action.
     *
     * @return Response
     */
//    public function actionLogout()
//    {
//        Yii::$app->user->logout();
//
//        return $this->goHome();
//    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
/*    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
/*    public function actionAbout()
    {
        return $this->render('about');
    }*/
    public function actionIndex(){

        $model=new NominationForm;
        return $this->render('nominationForm',['model'=>$model]);
    }
    public function actionSubmit(){
        $model = new NominationForm;
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            switch($model->connection){
                case 0:
                    $model->connection="Parent";
                    break;
                case 1:
                    $model->connection="Teacher";
                    break;
                case 2:
                    $model->connection="Governor";
                    break;
                default:
                    echo "Hy";
            }
            $EmailBody=$this->render('email',['model'=>$model]);
            $objEmailInfo                          = new EmailInfo();
            $objEmailInfo->_FromName               = "Device For Kids";
            $objEmailInfo->_FromEmailAddress       = "info@expressestateagency.co.uk";
            $objEmailInfo->_ToEmailAddress         = "hello@devicesforkids.co.uk";
            $objEmailInfo->_EmailSubject           = "Nomination ".$model->school;
            $objEmailInfo->_CCList                 = ["anum.shahzadi@dynamologic.com","shahjahan.mehmood.mirza@dynamologic.com"];
            $objEmailInfo->_EmailBody              = $EmailBody;
            $response                              = SendEmail::sendMail($objEmailInfo);

            return $response;
        }
        else{
            return 0;
        }
    }
}
