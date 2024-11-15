<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    //退会用のsoftdeletesトレイトの追加
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    //論理削除カラムが日付型であることを宣言する
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    //ユーザーの編集可能項目はここに入れる（これ以外がユーザーが編集できないように保護する）
    protected $fillable = [
        'name',
        'email',
        'postal_code',
        'address',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorite_products()
    {
        //withTimestampsは中間テーブルで自動更新されないcreated_atとupdated_atを更新する
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
