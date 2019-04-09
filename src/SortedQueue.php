<?php

namespace MS\Cache;

class SortedQueue extends Queue
{
    public function enqueue($values)
    {
        $nsName = $this->ns->apply($this->name);
        $values = array_map([$this, 'serialize'], func_get_args());

        foreach ($values as $value) {
            $this->redis->zIncrBy($nsName, 1, $value);
        }
    }

    public function peek($count = 1)
    {
        $nsName = $this->ns->apply($this->name);
        $values = $this->redis->zRevRange($nsName, 0, $count);
        $values = array_map([$this, 'deserialize'], $values);

        return $values;
    }

    /**
     * @param int $count
     *
     * @return mixed
     */
    public function dequeue($count = 1)
    {
        $nsName = $this->ns->apply($this->name);
        $values = $this->redis->zRevRange($nsName, 0, $count);

        foreach ($values as $value) {
            $this->redis->zAdd($nsName, 0, $value);
        }
        $this->redis->zRemRangeByScore($nsName, 0, 0);

        return $values;
    }
}
