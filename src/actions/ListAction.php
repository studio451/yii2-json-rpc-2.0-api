<?

namespace studio451\yii2jsonrpc2api\actions;

use Yii;
use yii\data\ActiveDataProvider;
use studio451\yii2jsonrpc2api\Exception;

class ListAction extends Action {
    
    public $prepareDataProvider;
    public $dataFilter;

    public function run() {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        return $this->prepareDataProvider();
    }

    protected function prepareDataProvider() {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    throw new Exception('Failed data filter. ' . implode( ". ",$this->dataFilter->getErrors('filter')),Exception::INTERNAL_ERROR,$this->dataFilter->getErrors('filter'));
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }

        $modelClass = $this->modelClass;

        $query = $modelClass::find();
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        
        $provider = Yii::createObject([
                    'class' => ActiveDataProvider::className(),
                    'query' => $query,
                    'pagination' => $requestParams['pagination']?$requestParams['pagination']:[],                    
                    'sort' => $requestParams['sort']?$requestParams['sort']:[],                    
        ]);

        return [
            'models' => $provider->getModels(),
            'count' => $provider->getCount(),
            'totalCount' => $provider->getTotalCount(),
        ];
    }

}
