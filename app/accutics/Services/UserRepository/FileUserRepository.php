<?php

namespace Accutics\Services\UserRepository;

use Accutics\Core\Models\User;

class FileUserRepository implements UserRepository {

    function findByEmail(string $email): ?User {
        $users = $this->readAllUsers();

        $withSameEmail = array_values(array_filter($users, fn($it) => $it->email === $email));

        return !empty($withSameEmail) ? $this->fromStorageToDomain($withSameEmail[0]) : null;
    }

    function findByNameLike(string $fragment): array {
        $users = $this->readAllUsers();

        $withNameLike = array_values(array_filter($users, fn($it) => str_contains($it->name, $fragment)));

        return array_map(fn($it) => $this->fromStorageToDomain($it), $withNameLike);
    }

    private function readAllUsers() {
        return json_decode(file_get_contents('storage/users.json'));
    }

    private function fromStorageToDomain(\stdClass $user): User {
        return new User($user->name, $user->email, []);
    }

    private function fromDomainToStorage(User $user): \stdClass {
        return (object)[
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
