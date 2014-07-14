<?php
namespace bariew\nodeTree\actions;

class TreeCreateAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->findModel();
        $model->scenario = 'nodeTree';
        $post = ["Item" => \Yii::$app->request->post()['attributes']];
        if($model->load($post) && $model->getBehavior('nodeTree')->move($id)){
            echo json_encode($model->getBehavior('nodeTree')->nodeAttributes());
        }
    }
}