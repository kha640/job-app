<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) {

        $query = JobVacancy::query();

        if ( $request->has('filter') && $request->has('search') ) {
            $query->where( function( $q ) use ( $request ) {
                $q->where('title', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('location', 'like', '%' . $request->input('search') . '%')
                    ->orWhereHas('company', function( $q ) use ( $request ) {
                        $q->where('name', 'like', '%' . $request->input('search') . '%');
                    });
            } )
            ->where('type', $request->input('filter'));
        }

        if ( $request->has('filter') && $request->input('search') === null ) {
            $query = $query->where('type', $request->input('filter'));
        }

        if ( $request->has('search') && $request->input('filter') === null  ) {
            $query = $query->where('title', 'like', '%' . $request->input('search') . '%')
                     ->orWhere('location', 'like', '%' . $request->input('search') . '%')
                     ->orWhereHas('company', function( $query ) use ( $request ) {
                        $query->where('name', 'like', '%' . $request->input('search') . '%');
                     });
        }

        $jobs = $query->select('id', 'title', 'location', 'salary', 'type', 'created_at', 'companyId')
                    ->orderByDesc('created_at')
                    ->orderBy('id')
                    ->cursorPaginate(10);

        return view('dashboard', compact('jobs'));
    }
}
