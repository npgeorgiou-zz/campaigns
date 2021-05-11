<?php
namespace Accutics\Core\Models;

class User {
    public function __construct(
        public string $name,
        public string $email,
        public array $campaigns,
    ) {}
}
