<?php
namespace weapp\controllers;

use Yii;
use common\models\FormId;
use common\components\ResponseComponent;
use yii\web\BadRequestHttpException;


/**
 * Form Id controller
 */
class FormIdController extends AuthController {

    /**
     * @param string form_id
     * @param string type
     * @throws BadRequestHttpException
     * @return array
     */
    public function actionSave(){
        $params = Yii::$app->request->rawBody;
        $params = json_decode($params, true);
        if (!$params || !$params['form_id']){
            throw new BadRequestHttpException("form id must be require;", 1);
        }
        $type = $params['form_id'] || 'form';
        $form = (new FormId())->saveFormId(Yii::$app->user->id,$params['form_id'],$type);
        return ResponseComponent::success($form);
    }


}