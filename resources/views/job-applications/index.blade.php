<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ 'My Applications' }}
        </h2>
    </x-slot>

    {{-- Succcess Messages --}}
    @if ( session('success') )
        <div class="w-full text-white bg-indigo-600 p-4 rounded-md mb-2">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto space-y-4">
            @forelse ( $jobApplications as $jobApplication )
                <div class="bg-gray-900 p-4 rounded-md mb-2">
                    <h3 class="text-white text-lg font-bold mb-2">{{ $jobApplication->jobVacancy->title }}</h3>
                    <p class="text-white text-lg font-bold mb-2">{{ $jobApplication->jobVacancy->company->name }}</p>
                    <p class="text-xs">{{ $jobApplication->jobVacancy->location }}</p>

                    <div class="flex items-center justify-between mt-2">
                        <p class="text-sm"> {{ $jobApplication->created_at->format('d m, Y') }} </p>
                        <p class="px-3 py-1 bg-indigo-600 text-white rounded-md">{{ $jobApplication->jobVacancy->type }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span> Applied with: </span>
                        <a href="{{ Storage::disk('cloud')->url($jobApplication->resume->fileUri) }}" target="_blank"
                            class="hover:text-indigo-400 hover:underline">
                            <span>{{ $jobApplication->resume->fileName }} </span>
                        </a>
                    </div>
                    <div class="flex flex-col flex-start gap-1 mt-3">
                        <div class="flex items-center gap-2">
                            @php
                                $status = $jobApplication->status;
                                $statusClass = match($status) {
                                    'pending' => 'bg-yellow-500',
                                    'accepted' => 'bg-green-500',
                                    'rejected' => 'bg-red-500',
                                };
                            @endphp
                            <p class="{{ $statusClass }} w-fit px-2 py-1 rounded-md"> Status: {{ $jobApplication->status }} </p>
                            <p class="bg-indigo-500 w-fit px-2 py-1 rounded-md"> Score: {{ $jobApplication->aiGeneratedScore }} </p>
                        </div>
                        <h4 class="text-md"> AI Feedback </h4>
                        <p class="text-sm"> {{ $jobApplication->aiGeneratedFeedback }} </p>
                    </div>

                </div>
            @empty
            <h1 class="text-xs font-sans "></h1>
                <h2 class="text-center"> No Applications foud. Go to
                    <a href="{{ route('dashboard') }}" class="underline text-blue-500">
                        jobs
                    </a> and get your dream job.
                </h2>
            @endforelse

            <div>
                {{ $jobApplications->links() }}
            </div>
        </div>

    </div>



</x-app-layout>
