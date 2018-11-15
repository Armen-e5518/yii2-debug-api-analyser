Yii2 debug api analyser
-----
This package help analyze database requests in yii2

To use it, do the next steps:

1 Activate yii2 debug module [see here](https://www.yiiframework.com/extension/yiisoft/yii2-debug).
To check it for working go to the next url

```
[YOUR_SITE]/debug
```

2 Create controller in the same environment (admin.* or backend.*) there debug module has bee activated

```php
<?php

namespace backend\controllers;

use oneGit\yii2DebugApisAlyser\controllers\DefaultController;

/**
 * Class DebugApiController
 * @package backend\controllers
 */
class DebugApiController extends DefaultController
{
    /**
     * @param string $path
     * @return array
     */
    protected function actionStat($path) {
        return $this->getStat($path);
    }
}

```
3 Get your result here
```
[YOUR_SITE]/debug-api/stat?path=[URL]
```
What is [URL]? For example if you open page 
```
[YOUR_SITE]/site/index
```
and then want to see stats of this page, you ca see it here

```
[YOUR_SITE]/debug-api/stat?path=site/index
```