<?php

namespace bariew\nodeTree\actions;

use yii\base\Action;

class TreeUpdateAction extends Action
{
    public function run($id)
    {
        $model = $this->controller->findModel($id);
        if(($model->attributes = @$_POST['attributes']) && !$model->save()){
            throw new \yii\web\HttpException(400, "Model not saved");
        }
        echo json_encode($model->nodeAttributes());
    }
}