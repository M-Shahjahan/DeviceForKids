<?php

namespace app\controllers;

use app\models\emailInfo;
use app\models\Instagram;
use app\models\instagrampost;
use app\models\instagramuser;
use app\models\NominationForm;
use app\models\SendEmail;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;


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
        $instaUser = instagramuser::find()->where(['id' => 1])->one();
        $instPosts= instagrampost::find()->all();
        return $this->render('nominationForm',['model'=>$model,'instaUser'=>$instaUser,'instaPosts'=>$instPosts]);
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
    public function actionInstagram(){
        $accessToken = $_REQUEST['accessToken'];
        $metaData=Instagram::fetchUserMetaData($accessToken);
        $mediaData = Instagram::fetchMediaMetaData($metaData['media']['data'],$metaData['media_count'],$accessToken);
        $value = instagramuser::find()->where(['id' => 1])->one();
        $value->name=$metaData['name'];
        $value->username=$metaData['username'];
        $value->posts=$metaData['media_count'];
        $value->followers=$metaData['followers_count'];
        $value->biography=$metaData['biography'];
        $value->profile_pic_url=$metaData['profile_picture_url'];
        $value->insta_url="https://www.instagram.com/$value->username";
        $value->last_full_update= date('Y-m-d H:i:s');
        $count=0;
        if($value->validate() && $value->save()){
           $count+=1;
        }
        $insta= instagrampost::find()->where(['id'=>1])->one();
        $lastPartialUpdate=$insta->last_partial_update;
        instagrampost::deleteAll();
        for($i=0;$i<$metaData['media_count'];++$i){
            $counter=$i+1;
            $instaPost=new instagrampost();
            $instaPost->id=$counter;
            $instaPost->post_url=$mediaData[$i]['media_url'];
            $instaPost->short_url=$mediaData[$i]['permalink'];
            $instaPost->caption=$mediaData[$i]['caption'];
            $instaPost->media_type=$mediaData[$i]['media_type'];
            $instaPost->likes=$mediaData[$i]['like_count'];
            $instaPost->comments=$mediaData[$i]['comments_count'];
            $instaPost->last_partial_update=$lastPartialUpdate;
            $instaPost->last_full_update= date('Y-m-d H:i:s');
            if($instaPost->validate() && $instaPost->save()){
                $count+=1;
            }
        }
        if($count==$metaData['media_count']+1){
            return 0;
        }
        else{
            return 1;
        }
    }
    public function actionFacebook(){
        return $this->render('facebook');
    }
    public function actionInsta(){
        $accessToken = "IGQVJYM0xGdmJ2WGxrX1dnNVNLbmIxQVhBWlFsSUkxRVlqbkdZAeDIyZA3N3UGVBLVdXX3B2WXV2VFBZAYUt5bEJPREZATcmNib1ppYmRKRFRNYU54WFJLQmIzZAUlQcmpMRXgzaHFHTlUyTTZAPbXVjNTMyWQZDZD";
        $metaData=Instagram::fetchBasicUserMetaData($accessToken);
        $mediaData=Instagram::fetchBasicMediaMetaData($accessToken);
        $value = instagramuser::find()->where(['id' => 1])->one();
        $value->username=$metaData->username;
        $value->posts=$metaData->media_count;
        $value->insta_url="https://www.instagram.com/$value->username";
        $value->last_partial_update= date('Y-m-d H:i:s');
        if($value->validate() && $value->save()){
            echo "Record Updated<br>";
        }
        $data= instagrampost::find()->max('id');
        if($data==0){
            $EmailBody=$this->render('instaError');
            $objEmailInfo                          = new EmailInfo();
            $objEmailInfo->_FromName               = "Device For Kids";
            $objEmailInfo->_FromEmailAddress       = "info@expressestateagency.co.uk";
            $objEmailInfo->_ToEmailAddress         = "shahjahan.mehmood.mirza@dynamologic.com";
            $objEmailInfo->_EmailSubject           = "Devices For Kids Basic API Error";
            /*$objEmailInfo->_CCList                 = ["shahjahan.mehmood.mirza@dynamologic.com"];*/
            $objEmailInfo->_EmailBody              = $EmailBody;
            $response                              = SendEmail::sendMail($objEmailInfo);
            if($response){
                echo "An Error Occurred Kindly Refer To The Email That Is Sent.";
            }

        }
        else{
            for($index=0;$index<$value->posts;++$index){
                $i=$index+1;
                $instaPost= instagrampost::find()->where(['id'=>$data-$index])->one();
                $instaPost->post_url=$mediaData->data[$data-$i]->media_url;
                $instaPost->short_url=$mediaData->data[$data-$i]->permalink;
                $instaPost->caption=$mediaData->data[$data-$i]->caption;
                $instaPost->media_type=$mediaData->data[$data-$i]->media_type;
                $instaPost->last_partial_update= date('Y-m-d H:i:s');
                if($instaPost->validate() && $instaPost->save()){
                    echo "Post No ".$i." Updated<br>";
                }
                if($data-$i==0){
                    echo "Updating Completed";
                    break;
                }
            }
        }

        exit;
    }
}
