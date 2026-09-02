<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function masterSeason(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // Dropdown With SeasonMaster

        $querySeason = $shop?->season()->select('id', 'name')->isActive()->orderByDesc('id');

        if ($request->has('searchSeasonMaster') && $request->searchSeasonMaster != '') {
            $search = $request->searchSeasonMaster;

            $querySeason->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        } else {
            $querySeason->take(5);
        }

        $seasonMasters = $querySeason->get();

        return response()->json([
            'seasonMasters' => $seasonMasters,
        ]);
    }
    public function masterAgent(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // Dropdown With SeasonMaster

        $queryAgent = $shop?->agent()->select('id', 'name','code')->isActive()->orderByDesc('id');

        if ($request->has('searchAgentMaster') && $request->searchAgentMaster != '') {
            $search = $request->searchAgentMaster;

            $queryAgent->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
            });
        } else {
            $queryAgent->take(5);
        }

        $agentMasters = $queryAgent->get();

        return response()->json([
            'agentMasters' => $agentMasters,
        ]);
    }
    public function masterTransport(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // Dropdown With SeasonMaster

        $queryTransport = $shop?->transport()->select('id', 'name','code')->isActive()->orderByDesc('id');

        if ($request->has('searchTransportMaster') && $request->searchTransportMaster != '') {
            $search = $request->searchTransportMaster;

            $queryTransport->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        } else {
            $queryTransport->take(5);
        }

        $transportMasters = $queryTransport->get();

        return response()->json([
            'transportMasters' => $transportMasters,
        ]);
    }

    public function masterdeliveryBy(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // Dropdown With SeasonMaster

        $querydeliveryBy = $shop?->deliveryBy()->select('id', 'name')->isActive()->orderByDesc('id');

        if ($request->has('searchDeliveryByMaster') && $request->searchDeliveryByMaster != '') {
            $search = $request->searchDeliveryByMaster;

            $querydeliveryBy->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        } else {
            $querydeliveryBy->take(5);
        }

        $deliveryByMasters = $querydeliveryBy->get();

        return response()->json([
            'deliveryByMasters' => $deliveryByMasters,
        ]);
    }
}
