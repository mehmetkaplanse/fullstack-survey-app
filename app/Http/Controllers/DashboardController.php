<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $total = Survey::query()->where('user_id', $user->id)->count();

        $latest = Survey::query()->where('user_id', $user->id)->latest('created_at')->first();



        return [
            'totalSurveys' => $total,
            'latestSurvey' => $latest ?? null
        ];
    }
}
