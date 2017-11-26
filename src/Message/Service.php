<?php

namespace Orange\Message;

use Orange\Protocol\Packet;

abstract class Service
{
    const SUCCESS     = 0;
    const ERROR_PARAM = 1000;

    protected $startTimestamp;
    protected $request;
    protected $response;
    protected $conn;

    public function __construct(Packet $packet, Connection $conn)
    {
        $this->request = $packet->getData();
        $this->response = clone $packet;
        $this->conn = $conn;
        $this->startTimestamp = microtime(true);
    }

    public function __invoke()
    {
        // 参数验证
        $checkRet = $this->checkRules();

        if ($checkRet === true) {
            try {
                return $this->invoke();
            } catch (\Exception $e) {
                app('logger')->error('invoke fail', [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);

                return $this->send($e->getCode());
            }
        } else {
            $this->response->setData([
                'message' => $checkRet[0],
            ]);
            return $this->send(self::ERROR_PARAM);
        }
    }

    public function send($errorCode = 0)
    {
        $this->response->setCode($errorCode);
        $this->response->setFlag(Packet::FLAG_RESPONSE);

        $this->conn->setData($this->response);
        $this->conn->send();

        // 返回数据
        app('logger')->debug('返回协议', [$this->response->desc()]);
        return true;
    }

    /**
     * 验证接口参数
     *
     * $this->rules
     * @return array|bool
     */
    protected function checkRules()
    {
        $params = $this->request;

        if (empty($this->rules)) {
            return true;
        }

        $errors = array();

        foreach ($this->rules as $key => $rule) {
            //解析规则
            $rule_list = explode('|', $rule);

            $rule = [
                'type' => 'string', //类型默认为字符串
            ];

            foreach ($rule_list as $rule_item) {
                if ($rule_item === 'required') {
                    $rule['required'] = true;
                } elseif ($rule_item === 'int') {
                    $rule['type'] = 'int';
                } elseif ($rule_item == 'array') {
                    $rule['type'] = 'array';
                } elseif (substr($rule_item, 0, 4) === 'min:') {
                    $rule['min'] = (int) substr($rule_item, 4);
                } elseif (substr($rule_item, 0, 4) === 'max:') {
                    $rule['max'] = (int) substr($rule_item, 4);
                } elseif (substr($rule_item, 0, 8) === 'default:') {
                    $rule['default'] = substr($rule_item, 8);
                }
            }

            //必须项
            if (!empty($rule['required']) && !isset($params[$key])) {
                $errors[] = $key . ' 参数丢失';
                break;
            }

            if (empty($rule['required']) && !isset($params[$key])) {
                // 目前必须显示使用default，否则老的isset判断会失效
                if (isset($rule['type']) && isset($rule['default'])) {
                    $rule['default'] = isset($rule['default']) ? $rule['default'] : '';
                    if ($rule['type'] == 'int') {
                        $params[$key] = (int) $rule['default'];
                    } elseif ($rule['type'] == 'string') {
                        $params[$key] = (string) $rule['default'];
                    }
                }
                continue;
            }

            //数值型
            if ($rule['type'] == 'int') {
                if (!is_int($params[$key])) {
                    $errors[] = $key . ' 必须是数值型';
                    break;
                }
                if (empty($rule['min']) && empty($rule['max'])) {
                    continue;
                }
                if (!empty($rule['max'])) {
                    if ($params[$key] > $rule['max']) {
                        $errors[] = $key . ' 不能大于' . $rule['max'];
                        break;
                    }
                }
                if (!empty($rule['min'])) {
                    if ($params[$key] < $rule['min']) {
                        $errors[] = $key . ' 不能小于' . $rule['min'];
                        break;
                    }
                }
            } else {
                //不是必须项，如果为空不继续判断
                if (empty($params[$key])) {
                    continue;
                }
            }
            //字符串
            if ($rule['type'] == 'string') {
                if (!is_string($params[$key])) {
                    $errors[] = $key . ' 必须是字符串';
                    break;
                }
                if (empty($rule['min']) && empty($rule['max'])) {
                    continue;
                }
                if (!empty($rule['max'])) {
                    if (mb_strlen($params[$key]) > $rule['max']) {
                        $errors[] = $key . ' 长度不能大于' . $rule['max'];
                        break;
                    }
                }
                if (!empty($rule['min'])) {
                    if (mb_strlen($params[$key]) < $rule['min']) {
                        $errors[] = $key . ' 长度不能小于' . $rule['min'];
                        break;
                    }
                }
            }
        }

        if (empty($errors)) {
            $this->request->setData($params);
            return true;
        }

        return (array) $errors;
    }

    abstract public function invoke();

    public function __destruct()
    {
        //监控组件-增加完成统计
        //$duration = microtime(true) - $this->startTimestamp;
    }
}
