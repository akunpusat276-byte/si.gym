<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $member = $user->member()->firstOrFail();
        $membership = $member->memberships()
            ->with('package')
            ->latest('end_date')
            ->latest()
            ->first();
        $payments = $user->payments()
            ->latest('payment_date')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.member', compact('member', 'membership', 'payments'));
    }
}
