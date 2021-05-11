<?php
namespace Accutics\Services\CampaignRepository;

use Accutics\Core\Models\Campaign;

interface CampaignRepository {
    function whereHasInputs(array $keyValues): array;
    function get(int $page, int $size): array;
    function save(Campaign $campaign): void;
}
