<?php

namespace Accutics\Core\Cases;

use Accutics\Core\Errors\MissingInput;
use Accutics\Core\Errors\ModelExists;
use Accutics\Core\Errors\ModelNotFound;
use Accutics\Core\Models\Campaign;
use Accutics\Core\Models\Input;
use Accutics\Services\CampaignRepository\CampaignRepository;
use Accutics\Services\UserRepository\UserRepository;

function create_campaign(
    UserRepository $userRepository,
    CampaignRepository $campaignRepository,
    ?string $user_email,
    ?string $name,
    ?string $source,
    ?string $channel,
    ?string $targetUrl
): Campaign {
    $author = $userRepository->findByEmail($user_email);

    if (!$author) {
        throw new ModelNotFound();
    }

    if (!$name || !$source || !$channel || !$targetUrl) {
        throw new MissingInput();
    }

    $inputs = [
        new Input(Input::NAME, $name),
        new Input(Input::SOURCE, $source),
        new Input(Input::CHANNEL, $channel),
        new Input(Input::TARGET_URL, $targetUrl)
    ];

    $existingCampaign = $campaignRepository->whereHasInputs($inputs);

    if ($existingCampaign) {
        throw new ModelExists();
    }

    $campaign = new Campaign($author, $inputs);

    $campaignRepository->save($campaign);

    return $campaign;
}

function get_campaigns(CampaignRepository $campaignRepository, int $page = 1, int $size = 100): array {
    return $campaignRepository->get($page, $size);
}
