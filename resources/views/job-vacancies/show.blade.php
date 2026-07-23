<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Job Details - {{ $jobVacancy->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            {{-- Back to jobs --}}
            <a href="{{ route('dashboard') }}"
                class="text-white hover:underline p-2 bg-blue-500 rounded-lg inline-block mb-6">
                Back to jobs
            </a>

            <div class="border-b border-white/10 pb-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-white">
                            <span class="border-b-2 border-blue-500"> Job Title</span> : {{ $jobVacancy->title }}
                        </h1>
                        <a href="{{ route('job-vacancies.apply', $jobVacancy->id) }}" class="justify-center bg-gradient-to-r from-indigo-500 to-rose-500 px-4 py-2 text-white rounded-lg transition hover:from-indigo-600 hover:to-rose-600">
                            Apply Now
                        </a>
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

            <div class="grid grid-cols-3 gap-4 w-full mt-6">
                <div class="col-span-2">
                    <h3 class="text-2xl font-bold text-white mb-3"> Job Description </h3>
                    <p class="text-gray-400"> {{ $jobVacancy->description }} </p>
                </div>

                <div class="col-span-1">
                    <h3 class="text-2xl font-bold text-white mb-3"> Job Overwiew </h3>
                    <div class="bg-gray-900 rounded-lg p-6 space-y-2">
                        <div>
                            <p class="text-gray-400"> Published Date </p>
                            <p class="text-white"> {{ $jobVacancy->created_at->format('d M, Y') }} </p>
                        </div>
                        <div>
                            <p class="text-gray-400"> Company </p>
                            <p class="text-white"> {{ $jobVacancy->company->name }} </p>
                        </div>
                        <div>
                            <p class="text-gray-400"> Location </p>
                            <p class="text-white"> {{ $jobVacancy->location }} </p>
                        </div>
                        <div>
                            <p class="text-gray-400"> Salary </p>
                            <p class="text-white"> {{ '$' . number_format($jobVacancy->salary) }} </p>
                        </div>
                        <div>
                            <p class="text-gray-400"> Type </p>
                            <p class="text-white"> {{ $jobVacancy->type }} </p>
                        </div>
                        <div>
                            <p class="text-gray-400"> Ctagory </p>
                            <p class="text-white"> {{ $jobVacancy->jobCategory->name }} </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>
