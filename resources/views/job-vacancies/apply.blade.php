<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Apply Job - {{ $jobVacancy->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            {{-- Back to jobs --}}
            <a href="{{ route('job-vacancies.show', $jobVacancy->id) }}"
                class="text-white hover:underline p-2 bg-blue-500 rounded-lg inline-block mb-6">
                Back to job details
            </a>

            <div class="border-b border-white/10 pb-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-white">
                            <span class="border-b-2 border-blue-500"> Job Title</span> : {{ $jobVacancy->title }}
                        </h1>
                    </div>
                    <p class="text-gray-400"> <span class="border-b-2 border-blue-500"> Company Name</span> :
                        {{ $jobVacancy->company->name }}
                    </p>
                    <p class="text-gray-400"> <span class="border-b-2 border-blue-500"> Location</span> :
                        {{ $jobVacancy->location }}
                    </p>
                    <p class="text-gray-400"><span class="border-b-2 border-blue-500"> Salary</span> :
                        {{ '$' . number_format($jobVacancy->salary) }}
                    </p>
                    <p class="text-gray-400"> <span class="border-b-2 border-blue-500"> Type</span> :
                        {{ $jobVacancy->type }}
                    </p>
                </div>
            </div>

            <form action="{{ route('job-vacancies.processApplication', $jobVacancy->id) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf

                {{-- Resume Seclection --}}
                <div>
                    <h3 class="text-xl font-semibold text-white mb-4"> Choose Your Resume </h3>

                    <div class="mb-6">
                        <x-input-label for="resume" value="Select form your existing resumes" class="mb-5" />
                        {{-- List of resumes --}}
                        <div class="space-y-2">
                            @forelse ( $userResumes as $resume )
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="resume_option" id="{{ $resume->id }}" value="{{ $resume->id }}"
                                        @error('resume_option')
                                            class="border-red-500"
                                        @else
                                            class="border-gray-500"
                                        @enderror
                                    />
                                    <x-input-label class="text-white cursor-pointer ">
                                        {{ $resume->fileName }}
                                        <span class="text-gray-400 text-sm"> (Created at:  {{ $resume->created_at->format('d M, Y') }})  </span>
                                    </x-input-label>

                                </div>
                            @empty
                                <span class="text-gray-400 text-sm"> No resumes found. </span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Uplode new resume --}}
                <div x-data="{ fileName: '', hasError: {{ $errors->has('resume_file') ? 'true' : 'false' }} }">
                    <div class="flex justify-items-center">
                        <input x-ref="newResumeRadio" type="radio" name="resume_option" id="new_resume" value="new_resume"
                            @error('resume_option')
                                class="border-red-500"
                            @else
                                class="border-gray-500"
                            @enderror
                        />
                        <x-input-label for="new_resume" value="Upload a new resume" class="font-semibold mb-3 ml-2 text-white cursor-pointer" />
                    </div>
                    <div class="felx items-center">
                        <dvi class="flex-1">
                            <label for="new_resume_file" class="block text-white cursor-pointer">
                                <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-blue-500 transiton"
                                    :class="{ 'border-blue-500': fileName, 'border-red-500':  hasError}">
                                    <input @change="fileName = $event.target.files[0].name; $refs.newResumeRadio.checked=true" type="file" name="resume_file" id="new_resume_file" accept=".pdf" class="hidden">
                                    <div class="text-center">
                                        <template x-if="!fileName">
                                            <p class="text-gray-400"> Click to upload PDF file (Max 5MB) </p>
                                        </template>

                                        <template x-if="fileName">
                                            <div>
                                                <p x-text="fileName" class="mt-2 text-blue-400"></p>
                                                <p class="text-gray-400 text-sm mt-1"> Click to change file </p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </label>
                        </dvi>
                    </div>
                </div>

                @if ( $errors->any() )
                    <div>
                        <h2 class="text-red-600 text-center">
                            {{ $errors->first() }}
                        </h2>
                    </div>
                @endif

                {{-- Submit Button --}}
                <div class="flex items-center justify-center">
                    <x-primary-button class="w-full">
                        Apply Now
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>

</x-app-layout>
