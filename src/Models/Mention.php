<?php

namespace gaurav\tagging\Models;

use Illuminate\Database\Eloquent\Model;
use gaurav\tagging\Exceptions\CannotFindPool;
use gaurav\tagging\Collections\MentionCollection;

class Mention extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'recipient_type',
        'recipient_id'
    ];

    protected $reference;

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new MentionCollection($models);
    }

    /**
     * Gets the recipient model.
     *
     * @return Model
     */
    public function recipient()
    {
        return $this->recipient_type::findOrFail($this->recipient_id);
    }

    /**
     * Notify the mentioned model.
     *
     * @return any
     */
    public function notify($notify_class = '')
    {
        $model = $this->recipient();
        $pool = $this->pool($model);

        $notify_class = (is_string($notify_class) && $notify_class) ? $notify_class : $pool->notification;

        if (method_exists($this->reference, 'getMentionNotification')) {
            $notify_class = $this->reference->getMentionNotification($model);
        }

        if (class_exists($notify_class)) {
            $model->notify(new $notify_class($this->reference));
        }

        return $this;
    }

    /**
     * Gets the pool config for the given model.
     *
     * @return void
     */
    public static function pool(Model $model = null)
    {
        $model = is_null($model) ? $this : $model;
        $name = get_class($model);

        foreach (config('mentions.pools') as $key => $pool) {
            if ($pool['model'] == $name) {
                $result = (object)$pool;
                $result->key = $key;
                return $result;
            }
        }

        throw CannotFindPool::create($name);
    }

    /**
     * Sets the reference model for this mention.
     *
     * @return this
     */
    public function setReference(Model $model)
    {
        $this->reference = $model;

        return $this;
    }
}
