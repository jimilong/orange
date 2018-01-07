<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/12/23
 * Time: 下午12:37
 */
namespace Orange\Promise;

class Promise
{
    use Race,All;

    private $result = null;

    private $stack = null;

    public function __construct(callable $callback = null)
    {
        $this->stack = new \SplStack();
        if (!empty($callback)) {
            $callback([$this, "resolve"], [$this, "reject"]);
        }
    }

    public function resolve($value = null)
    {
        if ($this->stack->isEmpty()) {
            $this->result = new ResolvedResult($value);
            return;
        }

        $state = State::FULFILLED;
        while (!$this->stack->isEmpty()) {
            $callback = $this->stack->shift();
            if ($callback->getState() == $state) {
                if ($value instanceof Result) {
                    $value = $value->getValue();
                }
                try {
                    $value = call_user_func($callback->getExecutor(), $value);
                } catch (\Exception $e) {
                    $value = new RejectedResult($e);
                }
                if ($value instanceof RejectedResult) {
                    $state = State::REJECTED;
                } else {
                    $state = State::FULFILLED;
                }
            }

            if ($value instanceof Result) {
                $this->result = $value;
            } else {
                $this->result = new ResolvedResult($value);
            }
        }
    }

    public function reject($value = null)
    {
        if ($this->stack->isEmpty()) {
            $this->result = new RejectedResult($value);
            return;
        }

        $state = State::REJECTED;
        while (!$this->stack->isEmpty()) {
            $callback = $this->stack->shift();
            if ($callback->getState() == $state) {
                if ($value instanceof Result) {
                    $value = $value->getValue();
                }
                try {
                    $value = call_user_func($callback->getExecutor(), $value);
                } catch (\Exception $e) {
                    $value = new RejectedResult($e);
                }
                if ($value instanceof RejectedResult) {
                    $state = State::REJECTED;
                } else {
                    $state = State::FULFILLED;
                }
            }

            if ($value instanceof Result) {
                $this->result = $value;
            } else {
                $this->result = new ResolvedResult($value);
            }
        }
    }

    public function then(callable $resolve)
    {
        if ($this->result instanceof ResolvedResult) {
            $value = $resolve($this->result->getValue());
            if ($value instanceof Result) {
                $this->result = $value;
            } else {
                $this->result = new ResolvedResult($value);
            }
        } elseif ($this->result == null) {
            $this->stack->push(new Callback(State::FULFILLED, $resolve));
        }

        return $this;
    }

    public function eCatch(callable $reject)
    {
        if ($this->result instanceof RejectedResult) {
            $value = $reject($this->result->getValue());
            if ($value instanceof Result) {
                $this->result = $value;
            } else {
                $this->result = new RejectedResult($value);
            }
        } else if ($this->result == null) {
            $this->stack->push(new Callback(State::REJECTED, $reject));
        }

        return $this;
    }

    public static function deferred()
    {
        return new Promise(null);
    }
}