<?php

namespace App\Trait;

use Illuminate\Support\Facades\Auth;

trait BlameAble
{
    //
    public static function bootBlameAble(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by_user_id = Auth::id();
                $model->updated_by_user_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by_user_id = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by_user_id = Auth::id();
                $model->save();
            }
        });
    }
}
