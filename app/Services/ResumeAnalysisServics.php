<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use OpenAI;

class ResumeAnalysisServics
{
    private function extractTextFromPdf( string $fileUrl ) {
        //--> 1 --> Reading the file from the cloud to local disk storage in the temp file

        $tempFile = tempnam(sys_get_temp_dir(), 'resume');
        // Create the empty temp file in the OS

        $filePath = parse_url( $fileUrl,  PHP_URL_PATH);
        // Check is a valid url or no
        if ( !$filePath ) {
            throw new Exception('Invalid file URL');
        }

        $fileName = basename( $filePath );
        // Return base file name with extention  -Form--> Shaghlni/resumes/aaaa.pdf -To--> aaaa.pdf

        $storagePath = "resumes/{$fileName}";
        if ( !Storage::disk('cloud')->exists( $storagePath ) ) {
            throw new Exception('File not found at Cloud disk');
        }

        $pdfContent = Storage::disk('cloud')->get( $storagePath );

        // dd ( $pdfContent );

        // Read the pdf file content form cloud - file stream
        if ( !$pdfContent ) {
            throw new Exception('Failed to read file');
        }

        file_put_contents( $tempFile, $pdfContent );

        //--> 2 --> Check if the pdf-to-text is installed
        $pdfToTextPath = ['/opt/homebrew/bin/pdftotext', '/usr/bin/pdftotext', 'usr/local/bin/pdftotext'];
        $pdfToTextAvailable = false;

        foreach ($pdfToTextPath as $path) {
            if ( file_exists( $path ) ) {
                $pdfToTextAvailable = true;
                break;
            }
        }

        if ( !$pdfToTextAvailable ) {
            throw new Exception('pdf-to-text is not installed');
        }

        //--> 3 --> Extract text from the pdf file
        $instance = new Pdf();
        $instance->setPdf( $tempFile );
        $text = $instance->text();

        // dd ( $text );

        unlink( $tempFile );
        // Clean up the temp file

        return $text;
    }

    /*
        Conect with ( AI MODEL ) to get the formated: 'summary', 'skills', 'experience', 'education'
        From the extracted text that comes from PDF extraction ( extractTextFromPdf - private function )
        and save this fields in DB -> Resume table
    */
    public function extractResumeInformation( string $fileUrl ) {
        try {
            // Extract raw text from the resume pdf file ( read pdf file, get the text )
            $rawText = $this->extractTextFromPdf( $fileUrl );

            Log::debug('Successfully extracted text form pdf file ' . strlen( $rawText ) . ' char');

            $client = OpenAI::factory()
                ->withApiKey(env('GROQ_API_KEY'))
                ->withBaseUri('https://api.groq.com/openai/v1')
                ->make();


            // Use ( AI API ) to organize the text into a structured format
            $response = $client->chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Youe are a percise resume parser. Extract information exactly as it appers in the resume without adding any interpretation or additional information'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Parse the following resume content and extract the information as a JSON Object with the exact keys: 'summary', 'skills', 'experience', 'education', The reusme content is: { $rawText }, Return empty string for key if not found"
                    ]
                ],
                'response_format' => [
                    'type' => 'json_object',
                ],
                'temperature' => 0.1
            ]);

            $result = $response->choices[0]->message->content;
            Log::debug( 'Model response: ' . $result );

            $parseResult = json_decode( $result, true );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                Log::error('Failed to parse model response: ' . json_last_error_msg());
                throw new Exception('Failed to pasre model response');
            }

            // Validate the parsed result
            $requiredKeys = ['summary', 'skills', 'experience', 'education'];
            $missingKeys = array_diff( $requiredKeys, array_keys( $parseResult ) );

            if ( count( $missingKeys ) > 0 ) {
                Log::error('Missing required keys: ' . implode( ',', $missingKeys ));
                throw new Exception('Missing required keys in the parsed result');
            }

            // Return the JSON object
            return [
                'education' => $parseResult['education'] ?? '',
                'summary' => $parseResult['summary'] ?? '',
                'skills' => $parseResult['skills'] ?? '',
                'experience' => $parseResult['experience'] ?? '',
            ];
        } catch ( Exception $e ) {
            Log::error('Error extracting resume information: ' . $e->getMessage());

            return [
                'education' => '',
                'summary' =>  '',
                'skills' => '',
                'experience' => '',
            ];
        }
    }

    /*
    بجيب الملف من الكلاود وبطلع النص منه
    باخد النص دا وبدية للموديل عشان يطلع الفيلدس اللي هخزنها في الداتا بيز
    هاخد الفيلدات دي وهقارنها مع الوظيقة اللي هو كان مقدم عليها عشان اطلع بالموديل فيدباك
    */

    public function analyzeResume( $jobVacancy, $resumeData ) {
        try{
            $jobDetails = json_encode([
                'job_title' => $jobVacancy->title,
                'job_description' => $jobVacancy->description,
                'job_salary' => $jobVacancy->salary,
                'job_location' => $jobVacancy->location,
                'job_type' => $jobVacancy->type,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

            $resumeDetails = json_encode( $resumeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

            $client = OpenAI::factory()
                ->withApiKey(env('GROQ_API_KEY'))
                ->withBaseUri('https://api.groq.com/openai/v1')
                ->make();

            $response = $client->chat()->create([
                'model'  => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an expert HR professional and job recruiter, you are given a job vacancy and a resume,
                                      Your task is to analyze the resume and determine if the candidate is a good fit for the job,
                                      The output shoud be in JSON format, Provide a score from 0 to 100 for the candidate's suitability
                                      for the job, and a detailed feedbacak.
                                      Respone should be only as a JSON format that has the following two keys exactly: 'aiGeneratedScore', 'aiGeneratedFeedback'.
                                      Aigenerate feedback should be detailed and specific to the job and the candidate's resume
                                      ai aenerted feedback key should be string - and ai generated scoure should be a floating number only",
                    ],
                    [
                        'role' => 'user',
                        'content' => "Please evalute this job application. Job Details: {$jobDetails} . Resume Details: {$resumeDetails} ",
                    ]
                ],
                'response_format' => [
                    'type' => 'json_object',
                ],
                'temperature' => 0.1
            ]);

            $result = $response->choices[0]->message->content;
            Log::debug('Mode responde: ' . $result);

            $parsedResult = json_decode( $result, true );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                Log::error('Failed tp parse AI Model response');
                throw new Exception('Failed to parse AI Model response');
            }

            // dd( $parsedResult );

            if ( !isset( $parsedResult['aiGeneratedScore']) || !isset( $parsedResult['aiGeneratedFeedback']) ) {
                Log::error('Missed required keys in the parsed result');
                throw new Exception('Missed required keys in the parsed result');
            }

            return $parsedResult;

        } catch( Exception $e ) {
            Log::error('Error analyzing resume: ' . $e->getMessage() . ' - ' . $jobVacancy->id);

            throw $e;

            // return [
            //     'aiGeneratedScoure' => 0,
            //     'aiGeneratedFeedback' => 'An error occurred while analyzing the resume. Please try again later.',
            // ];
        }
    }

}
