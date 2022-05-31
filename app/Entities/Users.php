<?php



namespace App\Entities;

use CodeIgniter\Entity;

class Users extends Entity
{
    public function setPassword(string $pass)
    {
        $this->attributes['password'] = password_hash($pass, PASSWORD_DEFAULT);
        return $this;
    }
}
