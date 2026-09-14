<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AvatarColor;
use App\Enums\AvatarFrame;
use App\Enums\AvatarSymbol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The only account allowed to manage vacations.
     */
    public const ADMIN_EMAIL = 'koensnelten@gmail.com';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_symbol',
        'avatar_color',
        'avatar_frame',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'avatar_symbol' => AvatarSymbol::class,
            'avatar_color' => AvatarColor::class,
            'avatar_frame' => AvatarFrame::class,
        ];
    }

    /**
     * De avatar zoals hij getekend moet worden. Wie nog niets gekozen heeft
     * krijgt zijn initialen op een kleur die uit zijn e-mailadres volgt, zodat
     * iedereen meteen een herkenbare avatar heeft.
     */
    public function avatarSymbol(): AvatarSymbol
    {
        return $this->avatar_symbol ?? AvatarSymbol::Initials;
    }

    public function avatarColor(): AvatarColor
    {
        return $this->avatar_color ?? AvatarColor::defaultFor($this);
    }

    public function avatarFrame(): AvatarFrame
    {
        return $this->avatar_frame ?? AvatarFrame::None;
    }

    /**
     * De initialen: eerste en laatste woord, niet de eerste twee. Anders wordt
     * "Anne de Vries" tot "AD" in plaats van "AV".
     */
    public function initials(): string
    {
        $words = Str::of($this->name)->squish()->explode(' ')->filter()->values();

        if ($words->isEmpty()) {
            return '';
        }

        return Str::upper($words->count() > 1
            ? Str::substr($words->first(), 0, 1).Str::substr($words->last(), 0, 1)
            : Str::substr($words->first(), 0, 1));
    }

    public function isAdmin(): bool
    {
        return $this->email === self::ADMIN_EMAIL;
    }

    public function vacations(): BelongsToMany
    {
        return $this->belongsToMany(Vacation::class, 'vacation_user');
    }
}
