<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ChMessage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ch_messages';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'from_id',
        'to_id',
        'body',
        'attachment',
        'seen',
    ];

    protected $casts = [
        'id' => 'string',
        'from_id' => 'integer',
        'to_id' => 'integer',
        'seen' => 'boolean',
    ];

    // Relation avec l'expéditeur
    public function sender()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    // Relation avec le destinataire
    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_id');
    }
}
