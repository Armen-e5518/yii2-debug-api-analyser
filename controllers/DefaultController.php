<?php

namespace oneGit\yii2DebugApisAlyser\controllers;

use Yii;
use yii\web\Response;

/**
 * Class DefaultController
 * @package oneGit\yii2DebugApisAlyser\controllers
 */
class DefaultController extends \yii\debug\controllers\DefaultController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        $this->module = Yii::$app->modules['debug'];
        return parent::actions();
    }

    /**
     * @var array db queries info extracted to array as models, to use with data provider.
     */
    private $_models;

    /**
     * @param $path
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    protected function getStat($path)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Load latest request
        $data = $this->getManifest();

        //Find tag
        $tag = null;
        foreach ($data as $value) {
//            $info = parse_url($value['url']);
            if (strpos($value['url'], $path) !== false) {
                $tag = $value['tag'];
                break;
            }
        }

        //Get panel model
        if ($tag === null) {
            return [
                'status' => 0,
            ];
        }
        $this->loadData($tag);
        $activePanel = $this->module->panels['db'];
        if ($activePanel->hasError()) {
            return [
                'status' => 0,
                'error' => $activePanel->getError(),
            ];
        }

        //Get info
        $models = $this->getModels($activePanel);
        $sumDuplicates = $activePanel->sumDuplicateQueries($models);

        $timings = $activePanel->calculateTimings();
        $queryCount = count($timings);
        $queryTime = number_format($this->getTotalQueryTime($timings) * 1000);

        return [
            'status' => 1,
            'data' => [
                'tag' => $tag,
                'queryCount' => $queryCount,
                'queryTime' => $queryTime,
                'timings' => $timings,
                'models' => $models,
                'sumDuplicates' => $sumDuplicates,
            ],
        ];
    }

    /**
     * Returns total query time.
     *
     * @param array $timings
     * @return int total time
     */
    protected function getTotalQueryTime($timings)
    {
        $queryTime = 0;

        foreach ($timings as $timing) {
            $queryTime += $timing['duration'];
        }

        return $queryTime;
    }

    /**
     * Returns an  array of models that represents logs of the current request.
     * Can be used with data providers such as \yii\data\ArrayDataProvider.
     * @return array models
     */
    protected function getModels($activePanel)
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = $activePanel->calculateTimings();
            $duplicates = $activePanel->countDuplicateQuery($timings);

            foreach ($timings as $seq => $dbTiming) {
                $this->_models[] = [
                    'type' => $this->getQueryType($dbTiming['info']),
                    'query' => $dbTiming['info'],
                    'duration' => ($dbTiming['duration'] * 1000), // in milliseconds
                    'trace' => $dbTiming['trace'],
                    'timestamp' => ($dbTiming['timestamp'] * 1000), // in milliseconds
                    'seq' => $seq,
                    'duplicate' => $duplicates[$dbTiming['info']],
                ];
            }
        }

        return $this->_models;
    }

    /**
     * Returns database query type.
     *
     * @param string $timing timing procedure string
     * @return string query type such as select, insert, delete, etc.
     */
    protected function getQueryType($timing)
    {
        $timing = ltrim($timing);
        preg_match('/^([a-zA-z]*)/', $timing, $matches);

        return count($matches) ? mb_strtoupper($matches[0], 'utf8') : '';
    }
}
