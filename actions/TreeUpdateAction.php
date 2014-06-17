<?php

namespace bariew\nodeTree\actions;

class TreeUpdateAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->getModel($id);
        if(($model->attributes = @$_POST['attributes']) && !$model->save()){
            throw new \yii\web\HttpException(400, "Model not saved");
        }
        echo json_encode($model->getBehavior('nodeTree')->nodeAttributes());
    }
}