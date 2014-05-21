<?php

class TreeUpdateAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->getModel($id);
        if(($model->attributes = @$_POST['attributes']) && !$model->save()){
            throw new CHttpException(400, "Model not saved");
        }
		echo json_encode($model->tree->nodeAttributes());
    }
}