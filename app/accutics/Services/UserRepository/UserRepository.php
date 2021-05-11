<?php
namespace Accutics\Services\UserRepository;

use Accutics\Core\Models\User;

interface UserRepository {
    function findByEmail(string $email): ?User;
    function findByNameLike(string $fragment): array;
}
