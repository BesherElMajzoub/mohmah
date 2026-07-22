<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates the admin account interactively.
 *
 * Deliberately not a seeder: seeded credentials get committed, shared, and
 * left in place on production. Prompting means the password exists only in
 * the operator's hands and the database hash.
 */
class MakeAdmin extends Command
{
    protected $signature = 'make:admin';

    protected $description = 'إنشاء حساب مدير للوحة التحكم';

    public function handle(): int
    {
        $name = text('الاسم', required: true);
        $email = text('البريد الإلكتروني', required: true);

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $plain = password('كلمة المرور', required: true);

        $validator = Validator::make(['password' => $plain], [
            'password' => ['required', Password::min(12)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            $this->error('كلمة المرور ضعيفة: يجب ألا تقل عن 12 خانة وتتضمن حروفاً وأرقاماً.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plain),
            'is_admin' => true,
        ]);

        $this->info("تم إنشاء حساب المدير: {$email}");

        return self::SUCCESS;
    }
}
