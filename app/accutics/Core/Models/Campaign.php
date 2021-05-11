<?php

namespace Accutics\Core\Models;

class Campaign {
    public function __construct(
        public ?User $author,
        public array $inputs,
    ) {
    }

    function getName(): Input {
        return $this->getInputByType(Input::NAME);
    }

    function getChannel(): Input {
        return $this->getInputByType(Input::CHANNEL);
    }

    function getSource(): Input {
        return $this->getInputByType(Input::SOURCE);
    }

    function getTargetUrl(): Input {
        return $this->getInputByType(Input::TARGET_URL);
    }

    private function getInputByType(string $inputType): Input {
        if (!in_array($inputType, Input::$allowedTypes)) {
            throw new \Exception("$inputType is not allowed as input type");
        }

        return array_values(array_filter($this->inputs, fn($it) => $it->type === $inputType))[0];
    }
}
