<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PasswordOtpController extends Controller
{
    /**
     * Mostrar formulario para solicitar código (correo).
     */
    public function showRequestForm(): InertiaResponse
    {
        return Inertia::render('Auth/ForgotPasswordOtp');
    }

    /**
     * Enviar código al correo.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = (string) $request->input('email');

        // Verificar que exista usuario con ese correo
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No existe una cuenta con ese correo.',
            ]);
        }

        // Borrar códigos anteriores de ese correo
        PasswordOtp::where('email', $email)->delete();

        // Generar código de 6 dígitos
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar OTP con expiración 10 minutos
        PasswordOtp::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Enviar correo
        Mail::to($email)->send(new PasswordOtpMail($code));

        // Podrías guardar el email en sesión para no pedirlo cada rato
        session(['password_otp_email' => $email]);

        return redirect()
            ->route('password.otp.verify.form')
            ->with('status', 'Te enviamos un código de verificación a tu correo.');
    }

    /**
     * Mostrar formulario para capturar código.
     */
    public function showVerifyForm(Request $request): InertiaResponse
    {
        return Inertia::render('Auth/VerifyOtp', [
            'email' => $request->session()->get('password_otp_email'),
        ]);
    }

    /**
     * Verificar código.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'digits:6'],
        ]);

        $email = (string) $request->input('email');
        $code  = (string) $request->input('code');

        $otp = PasswordOtp::where('email', $email)
            ->where('code', $code)
            ->valid()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return back()->withErrors([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        // Guardar flag en sesión para permitir el cambio de contraseña
        session([
            'password_otp_email'    => $email,
            'password_otp_verified' => true,
        ]);

        return redirect()
            ->route('password.otp.reset.form')
            ->with('status', 'Código verificado, ahora puedes cambiar tu contraseña.');
    }

    /**
     * Mostrar formulario para nueva contraseña.
     */
    public function showResetForm(Request $request): InertiaResponse
    {
        $email   = $request->session()->get('password_otp_email');
        $verified = $request->session()->get('password_otp_verified', false);

        // Si alguien entra directo sin verificar código
        if (! $email || ! $verified) {
            return redirect()->route('password.otp.request.form')
                ->withErrors(['email' => 'Debes verificar un código primero.']);
        }

        return Inertia::render('Auth/ResetPasswordOtp', [
            'email' => $email,
        ]);
    }

    /**
     * Cambiar la contraseña usando email + código.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => ['required', 'email'],
            'code'                  => ['required', 'digits:6'],
            'password'              => ['required', 'confirmed', 'min:8'],
        ]);

        $email    = (string) $request->input('email');
        $code     = (string) $request->input('code');
        $password = (string) $request->input('password');

        // Verificar OTP válido otra vez por seguridad
        $otp = PasswordOtp::where('email', $email)
            ->where('code', $code)
            ->valid()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return back()->withErrors([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No existe una cuenta con ese correo.',
            ]);
        }

        // Cambiar contraseña
        $user->password = Hash::make($password);
        $user->save();

        // Borrar los OTP de ese correo
        PasswordOtp::where('email', $email)->delete();

        // Limpiar la sesión relacionada al OTP
        $request->session()->forget(['password_otp_email', 'password_otp_verified']);

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña se cambió correctamente. Ya puedes iniciar sesión.');
    }
}
