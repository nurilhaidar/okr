<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role && $user->role->name === 'Admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find employee by email
        $employee = Employee::with('role')->where('email', $request->email)->first();

        if (!$employee) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        }

        // Check if employee is active
        if (!$employee->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Your account has been deactivated. Please contact your administrator.',
                ]);
        }

        // Verify password
        if (!Hash::check($request->password, $employee->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        }

        // Login the employee
        Auth::login($employee, $request->boolean('remember'));

        // Regenerate session
        $request->session()->regenerate();

        return $this->authenticated($request, $employee);
    }

    /**
     * Handle redirect after successful authentication.
     */
    protected function authenticated(Request $request, $employee)
    {
        // Check if user has admin role
        if ($employee->role && $employee->role->name === 'Admin') {
            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $employee->name . '!');
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Welcome back, ' . $employee->name . '!');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
