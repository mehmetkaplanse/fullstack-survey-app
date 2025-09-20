<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $total = Survey::query()->where('user_id', $user->id)->count();
        $latest = Survey::query()->where('user_id', $user->id)->latest('created_at')->first();
        $total_questions = \DB::table('survey_questions')
            ->join('surveys', 'survey_questions.survey_id', '=', 'surveys.id')
            ->where('surveys.user_id', $user->id)
            ->count();




        return [
            'totalSurveys' => $total,
            'latestSurvey' => $latest ?? null,
            'totalQuestions' => $total_questions
        ];
    }
}
