<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobVacancyRequest;
use App\Models\JobApplication;
use OpenAI;
use Gemini\Enums\ModelVariation;
use Gemini\GeminiHelper;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Services\ResumeAnalysisServics;
use Exception;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    protected $resumeAnalysisService;

    public function __construct( ResumeAnalysisServics $resumeAnalysisService ) {
        $this->resumeAnalysisService = $resumeAnalysisService;
    }


    public function show( string $id ) {
        $jobVacancy = JobVacancy::findOrFail( $id);

        return view('job-vacancies.show', compact('jobVacancy'));
    }

    public function apply ( string $id  ) {
        $jobVacancy = JobVacancy::findOrFail( $id);

        $userResumes = auth()->user()->resumes;

        return view('job-vacancies.apply', compact('jobVacancy', 'userResumes'));
    }

    public function processApplication(ApplyJobVacancyRequest $request, string $id ) {

        $resumeId = null;
        $extractedInfo = null;
        $jobVacancy = JobVacancy::findOrFail( $id );

        if ( $request->input('resume_option') === 'new_resume' ) {
            $file = $request->file('resume_file');
            $extension = $file->guessClientExtension();
            $originalFileName = $file->getClientOriginalName();
            $fileName = 'resume_' . time() . '.' . $extension;   //  resume_678686867676.pdf

            // Store in Cloud flare
            $path = $file->storeAs('resumes', $fileName, 'cloud');

            $fileUrl = config('filesystems.disks.cloud.url') . '/' . $path;

            // TODO Extract inforamtion form resume
            $extractedInfo = $this->resumeAnalysisService->extractResumeInformation( $fileUrl );

            $resume = Resume::create([
                'fileName' => $originalFileName,
                'fileUri' => $path,
                'userId' => auth()->user()->id,
                'contactDetails' => json_encode([
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ]),
                'education' => $extractedInfo['education'],
                'summary' => $extractedInfo['summary'],
                'skills' => $extractedInfo['skills'],
                'experience' => $extractedInfo['experience'],
            ]);

            $resumeId = $resume->id;
        } else {
            $resumeId = $request->input('resume_option');
            $resume = Resume::findOrFail( $resumeId );
        }

        // TODO Evluate Job Application
        // Use The $exrtactedInfo to evluate the job application
        $evaluation = $this->resumeAnalysisService->analyzeResume( $jobVacancy, $extractedInfo );

        JobApplication::create([
            'status' => 'pending',
            'jobVacancyId' => $id,
            'resumeId' => $resumeId,
            'userId' => auth()->user()->id,
            'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
            'aiGeneratedScore' => $evaluation['aiGeneratedScore'],
        ]);

        return redirect()->route('job-applications.index', $id)->with('success', 'Application submitted successfuly');
    }

    public function testGemini() {
        try {
            // استخدام الموديل الأحدث والديناميكي من قائمتك
            $modelName = 'models/gemini-2.5-pro';
            $apiKey = env('GEMINI_API_KEY');

            // بناء الرابط المباشر
            $url = "https://generativelanguage.googleapis.com/v1beta/{$modelName}:generateContent?key={$apiKey}";

            // إرسال الطلب باستخدام لارافيل HTTP
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'اي الاخبار عامل ايه؟']
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // استخراج الرد النصي من هيكل JSON الخاص بجوجل
                $aiText = $result['candidates'][0]['content']['parts'][0]['text'];

                echo $aiText;

                return response()->json([
                    'status' => 'success',
                    'message' => 'الربط تم بنجاح! 🚀',
                    'model_used' => $modelName,
                    'ai_response' => $aiText
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'حدث خطأ أثناء التوليد',
                    'details' => $response->json()
                ], $response->status());
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini Final Test Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في النظام',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }
    // public function testOpenAI(): void {
    //     $result = OpenAI::chat()->create(parameters: [
    //         'model' => 'gpt-4o-mini',
    //         'messages' => [
    //             ['role' => 'system', 'content' => 'You are an HR manager.'],
    //             ['role' => 'user', 'content' => 'Hello!'],
    //         ],
    //     ]);

    //     echo $result->choices[0]->message->content; // Hello! How can I assist you today?
    // }

    public function testGroq() {
        try{
            $client = OpenAI::factory()
                ->withApiKey(env('GROQ_API_KEY'))
                ->withBaseUri('https://api.groq.com/openai/v1')
                ->make();

            $response = $client->chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an HR manager.'
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Hello! Introduce yourself in one short sentence.'
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 100,
            ]);

            return response()->json([
                'status' => 'success',
                'model' => 'llama-3.3-70b-versatile',
                'response' => $response->choices[0]->message->content,
            ]);
        } catch ( Exception $ex ) {
            throw $ex;
        }
    }
}
