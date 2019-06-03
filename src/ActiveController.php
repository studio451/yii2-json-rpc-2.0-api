<?

namespace studio451\yii2jsonrpc2api;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

class ActiveController extends  \studio451\yii2jsonrpc2api\Controller {

    public $modelClass = null;
    public $dataFilter = null;
    
    public $updateScenario = Model::SCENARIO_DEFAULT;
    public $createScenario = Model::SCENARIO_DEFAULT;

    public function init() {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        $bodyParams = Yii::$app->getRequest()->getBodyParams();
        Yii::$app->getRequest()->setBodyParams($bodyParams['params']);
    }

    public function actions() {
        $parentActions = parent::actions();

        return array_replace_recursive($parentActions, [
            'list' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\ListAction',
                'modelClass' => $this->modelClass,
                'dataFilter' => ['class' => 'yii\data\ActiveDataFilter', 'searchModel' => $this->dataFilter],
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'delete-all' => [
                'class' => '\studio451\yii2jsonrpc2api\actions\DeleteAllAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ]);
    }

    public function checkAccess($action, $model = null, $params = []) {
        
    }
}
