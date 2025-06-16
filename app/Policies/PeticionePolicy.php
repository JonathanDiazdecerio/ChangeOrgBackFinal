<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Peticione;

class PeticionePolicy
{
    public function view(User $user, Peticione $peticione): bool
    {
        return true;
    }
   
    public function create(User $user): bool
    {
        return true;
    }
    
    public function update(User $user, Peticione $peticione): bool
    {
        if ($user->role_id == 1) {
            return true;
        }

        if ($user->role_id == 2 && $peticione->user_id == $user->id) {
            return true;
        }

        return false;
    }
   
    public function delete(User $user, Peticione $peticione): bool
    {
        if ($user->role_id == 1) {
            return true;
        }

        if ($user->role_id == 2 && $peticione->user_id == $user->id) {
            return true;
        }

        return false;
    }

    public function cambiarEstado(User $user, Peticione $peticione): bool
    {
        return $user->role_id == 1;
    }

    public function firmar(User $user, Peticione $peticione): bool
    {
        if ($user->role_id != 2 && $user->role_id != 1) {
            return false;
        }

        return !$peticione->firmas()->where('user_id', $user->id)->exists();
    }
}
