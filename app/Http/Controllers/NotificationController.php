<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\JsonResponseTrait;

class NotificationController extends Controller
{
        use JsonResponseTrait;

    /**
     * Get authenticated user's notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('sent_at')
            ->paginate(10);   
            
        return $this->success($notifications->items(), 'Notifications retrieved successfully');
       
    }
}
