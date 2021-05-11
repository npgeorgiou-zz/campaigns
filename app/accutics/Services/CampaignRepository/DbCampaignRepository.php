<?php

namespace Accutics\Services\CampaignRepository;

use Accutics\Core\Models\Campaign;
use Accutics\Core\Models\Input;
use App\Models\Campaign as OrmCampaign;

class DbCampaignRepository implements CampaignRepository {

    function whereHasInputs(array $inputs): array {
        $query = OrmCampaign::query();

        foreach ($inputs as $input) {
            $query->where($input->type, $input->value);
        }

        $ormCampaigns = $query->get();

        $domainModelCampaigns = $ormCampaigns->map(function ($it) {
            return $this->fromStorageToDomain($it);
        })->toArray();

        return $domainModelCampaigns;
    }


    function get(int $page, int $size): array {
        $ormCampaigns = OrmCampaign::limit($size)->offset(($page -1) * $size)->get();

        $domainModelCampaigns = $ormCampaigns->map(function ($it) {
            return $this->fromStorageToDomain($it);
        })->toArray();

        return $domainModelCampaigns;
    }

    function save(Campaign $campaign): void {
        $ormCampaign = $this->fromDomainToStorage($campaign);
        $ormCampaign->save();
    }

    private function fromStorageToDomain(OrmCampaign $ormCampaign): Campaign {
        return new Campaign(null, [
            new Input(Input::CHANNEL, $ormCampaign->channel),
            new Input(Input::SOURCE, $ormCampaign->source),
            new Input(Input::NAME, $ormCampaign->name),
            new Input(Input::TARGET_URL, $ormCampaign->target_url),
        ]);
    }

    private function fromDomainToStorage(Campaign $campaign): OrmCampaign {
        return new OrmCampaign([
            'user_id' => $campaign->author->email,
            Input::CHANNEL => $campaign->getChannel()->value,
            Input::SOURCE => $campaign->getSource()->value,
            Input::NAME => $campaign->getName()->value,
            Input::TARGET_URL => $campaign->getTargetUrl()->value
        ]);
    }
}
