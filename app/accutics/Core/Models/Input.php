<?php

namespace Accutics\Core\Models;

class Input {
    const CHANNEL = 'channel';
    const SOURCE = 'source';
    const NAME = 'name';
    const TARGET_URL = 'target_url';

    static array $allowedTypes = [self::CHANNEL, self::SOURCE, self::NAME, self::TARGET_URL];

    public function __construct(
        public string $type,
        public string $value,
    ) {
    }
}
