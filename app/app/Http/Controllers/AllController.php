<?php

namespace App\Http\Controllers;

use Accutics\Core\Errors\ModelNotFound;
use Accutics\Services\CampaignRepository\CampaignRepository;
use Accutics\Services\UserRepository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Accutics\Core\Errors\MissingInput;
use Accutics\Core\Errors\ModelExists;
use function Accutics\Core\Cases\create_campaign;
use function Accutics\Core\Cases\get_campaigns;

class AllController extends Controller {
    public function create_campaign(Request $request, UserRepository $userRepository, CampaignRepository $campaignRepository) {
        try {
            $campaign = create_campaign(
                $userRepository,
                $campaignRepository,
                $request->input('user_email'),
                $request->input('name'),
                $request->input('source'),
                $request->input('channel'),
                $request->input('target_url')
            );

            return response()->json($campaign);
        } catch (MissingInput) {
            return response('Missing input', Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFound) {
            return response('User not found', Response::HTTP_NOT_FOUND);
        } catch (ModelExists) {
            return response('Campaign exists', Response::HTTP_CONFLICT);
        } catch (\Exception) {
            return response('Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_campaigns(Request $request, CampaignRepository $campaignRepository) {
        try {
            $campaigns = get_campaigns(
                $campaignRepository,
                $request->input('page'),
                $request->input('size')
            );

            return response()->json($campaigns);
        } catch (\Exception) {
            return response('Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
