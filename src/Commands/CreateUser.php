<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class CreateUser
 *
 * This command allows the developer to create a new user manually via the CLI.
 * It prompts for a name, email, and password, and stores the user in the database.
 */
class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildora:user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user in the users table';

    /**
     * Execute the console command.
     *
     * Prompts for name, email, and password. Checks for uniqueness of the email,
     * hashes the password, and stores the user in the database.
     *
     * @return void
     */
    public function handle(): void
    {
        $name = $this->ask('What is the name of the user?');
        $email = $this->ask('What is the email address?');
        $password = $this->secret('Enter a password');

        // Validate email uniqueness
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");
            return;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("User {$name} has been successfully created with the email {$email}.");
    }
}
