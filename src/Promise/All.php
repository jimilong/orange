<?php

namespace Orange\Promise;


trait All
{
    private static $resolvedValue = [];
    public static function all($promises)
    {
        $all = Promise::deferred();
        $cnt = count($promises);
        for ($i = 0; $i < $cnt; $i++) {
            $promise = $promises[$i];
            $promise->then(function ($value) use ($all, $i, $cnt) {
                self::$resolvedValue[$i] = $value;
                if (count(self::$resolvedValue) == $cnt) {
                    ksort(self::$resolvedValue);
                    $all->resolve(self::$resolvedValue);
                }
            })->eCatch(function ($value) use ($all) {
                $all->reject($value);
            });
        }
    }
}


//class All
//{
//    private $arrived = false;
//    private $resolvedValue = [];
//    private $promises;
//    private $result;
//
//    public function __construct($promises)
//    {
//        if (is_array($promises))
//            $this->promises = $promises;
//        else
//            $this->promises = [];
//    }
//
//    public function then($callback)
//    {
//        if ($this->promises == []) {
//            //todo
//            $value = call_user_func($callback, $this->resolvedValue);
//            return new Promise(function ($resolve, $reject) use($value) {
//                if ($value instanceof RejectedResult) {
//                    $reject($value->getValue());
//                } else if ($value instanceof ResolvedResult) {
//                    $resolve($value->getValue());
//                } else {
//                    $resolve($value);
//                }
//            });
//        }
//
//        return new Promise(function ($resolve, $reject) use($callback) {
//            for ($i = 0; $i < count($this->promises); $i++) {
//                $promise = $this->promises[$i];
//                if ($promise instanceof Promise) {
//                    $promise->then(function ($value) use ($resolve, $i, $callback) {
//                        $this->resolvedValue[$i] = $value;
//                        if (count($this->resolvedValue) == count($this->promises)) {
//                            if ($this->arrived === false) {
//                                $this->arrived = true;
//                                ksort($this->resolvedValue);
//                                //todo
//                                $value = call_user_func($callback, $this->resolvedValue);
//                                $resolve($value);
//                            }
//                        }
//                    })->eCatch(function ($value) use ($reject) {
//                        if ($this->arrived === false) {
//                            $this->arrived = true;
//                            $reject($value);
//                        }
//                    });
//                } else if ($promise instanceof RejectedResult) {
//                    if ($this->arrived === false) {
//                        $this->arrived = true;
//                        $reject($promise->getValue());
//                        return;
//                    }
//                } else {
//                    if ($promise instanceof ResolvedResult) {
//                        $promise = $promise->getValue();
//                    }
//                    $this->resolvedValue[$i] = $promise;
//                    if (count($this->resolvedValue) == count($this->promises)) {
//                        if ($this->arrived === false) {
//                            $this->arrived = true;
//                            ksort($this->resolvedValue);
//                            //todo
//                            $value = call_user_func($callback, $this->resolvedValue);
//                            $resolve($value);
//                        }
//                    }
//                }
//            }
//        });
//
//    }
//
//    public function eCatch($callback)
//    {
//        if ($this->promises == []) {
//            return new Promise(function ($resolve, $reject) {
//                $resolve([]);
//            });
//        }
//
//        return new Promise(function ($resolve, $reject) use ($callback) {
//            for ($i = 0; $i < count($this->promises); $i++) {
//                $promise = $this->promises[$i];
//                if ($promise instanceof Promise) {
//                    $promise->eCatch(function ($value) use ($callback, $resolve, $reject) {
//                        if ($this->arrived === false) {
//                            $this->arrived = true;
//                            $value = $callback($value);
//                            if ($value instanceof RejectedResult) {
//                                $reject($value);
//                            } else {
//                                if ($value instanceof ResolvedResult) {
//                                    $value = $value->getValue();
//                                }
//                                $resolve($value);
//                            }
//                        }
//                    })->then(function ($value) use ($resolve, $i) {
//                        $this->resolvedValue[$i] = $value;
//                        if (count($this->resolvedValue) == count($this->promises)) {
//                            if ($this->arrived === false) {
//                                $this->arrived = true;
//                                ksort($this->resolvedValue);
//                                $resolve($this->resolvedValue);
//                            }
//                        }
//                    });
//                } else if ($promise instanceof RejectedResult) {
//                    if ($this->arrived === false) {
//                        $this->arrived = true;
//                        $value = $callback($promise->getValue());
//                        if ($value instanceof RejectedResult) {
//                            $reject($value);
//                        } else {
//                            if ($value instanceof ResolvedResult) {
//                                $value = $value->getValue();
//                            }
//                            $resolve($value);
//                        }
//                    }
//                } else {
//                    if ($promise instanceof ResolvedResult) {
//                        $promise = $promise->getValue();
//                    }
//                    $this->resolvedValue[$i] = $promise;
//                    if (count($this->resolvedValue) == count($this->promises)) {
//                        if ($this->arrived === false) {
//                            $this->arrived = true;
//                            ksort($this->resolvedValue);
//                            $resolve($this->resolvedValue);
//                        }
//                    }
//                }
//            }
//        });
//    }
//}