<?

namespace studio451\yii2jsonrpc2api\actions;

use Yii;
use yii\base\Model;
use studio451\yii2jsonrpc2api\Exception;

class CreateAction extends Action
{
    public $scenario = Model::SCENARIO_DEFAULT;

    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new Exception('Failed to create the object for unknown reason.');
        }        
        throw new Exception('Failed to create the object', Exception::INTERNAL_ERROR ,$model->getErrors());
    }
}
