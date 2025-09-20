<?php

namespace App\Http\Controllers;

use App\Events\SurveyCreated;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Models\SurveyQuestion;
use App\Notifications\SurveyCreatedNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isArray;
use App\Enums\QuestionType;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $surveys = Survey::where('user_id', $user->id)->paginate();
        return SurveyResource::collection($surveys);
    }

    public function store(StoreSurveyRequest $request)
    {
        $data = $request->validated();
        if(isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }
        $survey = Survey::create($data);

        foreach ($data['questions'] as $question) {
            $question['survey_id'] = $survey->id;
            $this->createQuestion($question);
        }

        event(new SurveyCreated($survey));
        return new SurveyResource($survey);
    }

    private function saveImage($image)
    {
        \Log::info('Image data:', [$image]);

        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $image = substr($image, strpos($image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Invalid image type');
            }

            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('Base64 decode failed');
            }
        } else {
            throw new \Exception('Invalid image data');
        }

        $dir = 'images/';
        $file = $dir . Str::random() . '.' . $type;
        Storage::disk('public')->put($file, $image);

        return $file;
    }

    public function show(Survey $survey, Request $request)
    {
        $user = $request->user();

        if($user->id !== $survey->user_id) {
            return abort(403, 'Unauthorized action.');
        }
        return new SurveyResource($survey);
    }

    public function showForGuest(Survey $survey)
    {
        return new SurveyResource($survey);
    }

    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $data = $request->validated();
        if(isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }

        if($survey->image) {
            $absolutePath = public_path($survey->image);
            File::delete($absolutePath);
        }

        $survey->update($data);

        $exisingIds = $survey->questions->pluck('id')->toArray();
        $newIds = Arr::pluck($data['questions'], 'id');

        $toDelete = array_diff($exisingIds, $newIds);
        $toAdd = array_diff($newIds, $exisingIds);

        // delete old questions
        SurveyQuestion::destroy($toDelete);

        // add new questions
        foreach ($data['questions'] as $question)
        {
            if(in_array($question['id'], $toAdd)) {
                $question['survey_id'] = $survey->id;
                $this->createQuestion($question);
            }
        }


        // update existing questions
        $questionMap = collect($data['questions'])->keyBy('id');
        foreach ($survey->questions as $question)
        {
            if(isset($questionMap[$question->id])) {
                $this->updateQuestion($questionMap[$question->id], $question);
            }
        }

        $survey->refresh()->load('questions');
        return new SurveyResource($survey);
    }

    public function destroy(Survey $survey, Request $request)
    {
        $user = $request->user();
        if($user->id !== $survey->user_id) {
            return abort(403, 'Unauthorized action.');
        }
        $survey->delete();
        return  response([
            'message' => 'Survey deleted successfully.'
        ],200);
    }

    private function createQuestion($data)
    {
        if(is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'question' => 'required|string',
            'type' => ['required',
                Rule::in(array_column(QuestionType::cases(), 'value'))
            ],
            'description' => 'nullable|string',
            'data' => 'present',
            'survey_id' => 'exists:App\Models\Survey,id'
        ]);

        return SurveyQuestion::create($validator->validated());
    }

    private function updateQuestion($data, SurveyQuestion $question)
    {
        if(is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }
        $validator = Validator::make($data, [
            'id' => 'exists:App\Models\SurveyQuestion,id',
            'question' => 'required|string',
            'type' => ['required',
                Rule::in(array_column(QuestionType::cases(), 'value'))
            ],
            'description' => 'nullable|string',
            'data' => 'present'
        ]);

        return $question->update($validator->validated());
    }


    // generate new question with ai/ollama
    public function generateAiQuestion(Request $request)
    {
        $prompt = $request->input('prompt');

        $response = Http::post('http://127.0.0.1:11434/api/chat', [
            'model' => 'mistral',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Sen bir anket asistanısın. Kullanıcının verdiği konuya uygun kısa ve net anket soruları üret.'
                ],
                [
                    'role' => 'user',
                    'content' => "Konu: {$prompt}. Bu konu hakkında 1 adet anket sorusu üret."
                ]
            ],
            'stream' => false
        ]);

        if($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI servisi yanıt vermedi'
            ], 500);
        }

        $content = $response->json('message.content');

        return response()->json([
            'status' => 'success',
            'question' => trim($content)
        ]);
    }

    public function setUsedAi(Request $request)
    {
        $user = $request->user();
        if($user) {
            $user->is_used_ai = true;
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'AI hakkınız kullanıldı.'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Kullanıcı bulunamadı.'
        ]);

    }
}
