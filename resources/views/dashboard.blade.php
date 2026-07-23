<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <h3 class="text-white text-2xl font-bold mb-5">
                {{ 'Welcome Back,' }} <span class="text-blue-400"> {{ Auth::user()->name }} </span>!
            </h3>

            {{-- Search & Filters --}}
            <div class="flex items-center justify-between">
                {{-- Search Bar--}}
                <div>
                    <form action="{{ route('dashboard') }}" method="GET" class="flex items-center justify-center">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="max-w-full pr-12 p-2 rounded-l-lg bg-gray-800 text-white" placeholder="Search for a job">
                        <button type="submit" class="bg-indigo-500 text-white p-2 rounded-r-lg border-indigo-500">
                            Search
                        </button>

                        {{-- To send the filter data with this search section to achive  --}}
                        @if ( request('filter') )
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                        @endif

                        {{-- To remove the search and remain filters --}}
                        @if ( request('search') )
                            <a href="{{ route('dashboard', ['filter' => request('filter')]) }}"
                                class="ml-4 max-w-fit text-white p-2 rounded-lg flex items-center border border-rose-600">
                                <img src="{{ asset('img/filter.png') }}" width="20px" alt="❌" >
                                <span class="text-white pr-6">
                                    CLear
                                </span>
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Filters --}}
                <div class="flex space-x-2">
                    <a href="{{ route('dashboard', ['filter' => 'Full-Time', 'search' => request('search')]) }}"
                        class="bg-indigo-500 text-white p-2 rounded-lg">
                        Full-Time
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'Remote', 'search' => request('search')]) }}"
                        class="bg-indigo-500 text-white p-2 rounded-lg">
                        Remote
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'Hybrid', 'search' => request('search')]) }}"
                        class="bg-indigo-500 text-white p-2 rounded-lg">
                        Hybrid
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'Contract', 'search' => request('search')]) }}"
                        class="bg-indigo-500 text-white p-2 rounded-lg">
                        Contract
                    </a>

                    @if ( request('filter') )
                        <a href="{{ route('dashboard', ['search' => request('search')]) }}"
                            class="text-white p-2 rounded-lg flex items-center border border-rose-600">
                            <img src="{{ asset('img/filter.png') }}" width="20px" alt="❌" >
                            <span class="text-white">
                                CLear
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Job List --}}
            <div class="space-y-4 mt-6">
                @forelse ( $jobs as $job )
                    {{-- Job Item --}}
                    <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                        <div>
                            <a href="{{ route('job-vacancies.show', $job->id) }}" class="text-lg font-semibold text-blue-400 hover:underline">
                                {{ $job->title }}
                            </a>
                            <p class="text-sm text-white"> {{ $job->company->name }} - {{ $job->location }} </p>
                            <p class="text-sm text-white"> {{ '$' . number_format( $job->salary) }} / Year </p>
                        </div>
                        <span class="bg-blue-500 rounded-lg p-2 text-white"> {{ $job->type }} </span>
                    </div>
                @empty
                    <p class="text-white text-2xl font-semibold flex justify-center"> No jobs found! </p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $jobs->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
