<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\JsonResponseTrait;

class PatientController extends Controller
{
        use JsonResponseTrait;

    /**
     * Get all patients with full details
     * Doctor only
     */
    public function index(Request $request)
    {
        // Optional safety check (can be replaced by middleware)
        if ($request->user()->role !== 'doctor') {
            return $this->error("Unauthorized",403);
        }

        $patients = User::with('patient')
            ->where('role', 'patient')
            ->get();
                    
            return $this->success($patients, 'user retrived successfully', 200);
        
    }
}
