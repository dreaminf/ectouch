文档说明
==========

目前进度
------------
*  [开发LOG](todo.md)

配置
-----------
*  [二次开发](develop.md)
*  [数据库](database.md)
*  [API](api.md)

```php

$this->links[$linkNum] = new PDO($config['dsn'], $config['username'], $config['password'], $params);
                
$this->setModes($this->links[$linkNum], $config);

```

```php

    /**
     * Set the modes for the connection.
     *
     * @param  PDO  $connection
     * @param  array  $config
     * @return void
     */
    protected function setModes(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            $this->setCustomModes($connection, $config);
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare($this->strictMode())->execute();
            } else {
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    /**
     * Set the custom modes on the connection.
     *
     * @param  PDO  $connection
     * @param  array  $config
     * @return void
     */
    protected function setCustomModes(PDO $connection, array $config)
    {
        $modes = implode(',', $config['modes']);
        $connection->prepare("set session sql_mode='{$modes}'")->execute();
    }

    /**
     * Get the query to enable strict mode.
     *
     * @return string
     */
    protected function strictMode()
    {
        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }

```