<?php
namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) === 11 && ($phone[0] === '8' || $phone[0] === '7')) {
            $phone = '+7' . substr($phone, 1);
        } elseif (strlen($phone) === 10) {
            $phone = '+7' . $phone;
        }

        $driver = Driver::where('phone_number', $phone)->first();

        if (!$driver || !Hash::check($data['password'], $driver->password)) {
            return back()->withErrors(['phone' => 'Неверный телефон или пароль'])->withInput();
        }

        session(['driver_id' => $driver->id_driver, 'is_admin' => $driver->id_driver === 1]);

        if ($driver->id_driver === 1) {
            return redirect('/');
        }

        return redirect('/profile');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) === 11 && ($phone[0] === '8' || $phone[0] === '7')) {
            $phone = '+7' . substr($phone, 1);
        } elseif (strlen($phone) === 10) {
            $phone = '+7' . $phone;
        }

        if (Driver::where('phone_number', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Телефон уже зарегистрирован'])->withInput();
        }

        $driver = Driver::create([
            'full_name' => $data['full_name'],
            'phone_number' => $phone,
            'password' => Hash::make($data['password']),
        ]);

        session(['driver_id' => $driver->id_driver, 'is_admin' => false]);

        return redirect('/profile');
    }

    public function logout()
    {
        session()->forget(['driver_id', 'is_admin']);
        return redirect('/login');
    }
}
