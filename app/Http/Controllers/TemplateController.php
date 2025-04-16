<?php
namespace App\Http\Controllers;

use App\Models\SaasSettings;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function createTemplate(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'application_name' => 'required|string|max:255|unique:templates,application_name',
                'domain' => 'required|string|max:255|unique:templates,domain',
                'front_replicas' => 'nullable|integer|min:1',
                'back_replicas' => 'nullable|integer|min:1',
                'env' => 'required|file',
            ]);

            $envFile = $request->file('env');
            $this->decryptFile($envFile);

            $appName = Str::slug($request->input('application_name'), '_');
            $dbName = $appName . '_db';
            $dbUser = $appName . '_user';
            $dbPass = $this->generateRandomPassword();

            // Build database creation command
            $this->BuildCommand($dbName, $dbUser, $dbPass);

            // Run Helm installation
            $this->runHelmInstall($request->input('application_name'), $request->input('domain'));

            // Store template in database
            $template = Template::create([
                'application_name' => $request->input('application_name'),
                'domain' => $request->input('domain'),
                'front_replicas' => $request->input('front_replicas', 1),
                'back_replicas' => $request->input('back_replicas', 1),
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_pass' => encrypt($dbPass),
            ]);

            $app_settings=SaasSettings::create([
                'application_name' => $request->input('application_name'),
            ]);

            DB::commit();

            return $this->respondOk($template);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create template: ' . $e->getMessage());

            return $this->respondError(null, 'Failed to create template. Please try again.');
        }
    }

    private function decryptFile($envFile)
    {
        $encryptedContent = file_get_contents($envFile->getRealPath());
        $decodedContent = base64_decode($encryptedContent);

        if ($decodedContent === false) {
            Log::error('Failed to decode the file content.');
            throw new \Exception('Failed to decode the file content.');
        }

        $path = base_path('helm/files/.env');

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $decodedContent);
        Log::info('File decrypted and saved to: ' . $path);
    }

    private function generateRandomPassword()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < 12; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }

    private function BuildCommand($dbName, $dbUser, $dbPass)
    {
        $scriptPath = base_path('helm/files/db_create.sh');

        $command = [
            $scriptPath,
            '--host',
            env('ADMIN_DB_HOST'),
            '--port',
            env('ADMIN_DB_PORT'),
            '--admin-user',
            env('ADMIN_DB_USERNAME'),
            '--admin-pass',
            env('ADMIN_DB_PASSWORD'),
            '--db-name',
            $dbName,
            '--new-user',
            $dbUser,
            '--new-pass',
            $dbPass,
        ];

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run(function ($type, $buffer) {
            if ($type === Process::OUT) {
                Log::info('[DB_CREATE STDOUT] ' . $buffer);
            } else {
                Log::error('[DB_CREATE STDERR] ' . $buffer);
            }
        });

        if (!$process->isSuccessful()) {
            Log::error('Helm chart installation failed', [
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput(),
                'exit_code' => $process->getExitCode(),
                'command' => implode(' ', $command),
            ]);
            throw new \Exception('Failed to run DB creation script.');
        }

        Log::info('Database created successfully.');
    }

    private function runHelmInstall(string $appName, string $domain)
    {
        $scriptPath = base_path('helm/files/helm_install.sh');

        if (!file_exists($scriptPath)) {
            Log::error('Helm install script not found.');
            throw new \Exception('Helm install script not found.');
        }

        if (!is_executable($scriptPath)) {
            Log::error('Helm install script is not executable.');
            throw new \Exception('Helm install script is not executable.');
        }

        $command = [
            '/bin/bash',
            $scriptPath,
            $appName,
            $domain,
        ];

        $env = [
            'KUBECONFIG' => '/home/alhagni/.kube/config'
        ];

        $process = new Process($command, null, $env);
        $process->setTimeout(180);

        $outputBuffer = '';
        $errorBuffer = '';

        $process->run(function ($type, $buffer) use (&$outputBuffer, &$errorBuffer) {
            if ($type === Process::OUT) {
                $outputBuffer .= $buffer;
                Log::info('[HELM INSTALL STDOUT] ' . $buffer);
            } else {
                $errorBuffer .= $buffer;
                Log::error('[HELM INSTALL STDERR] ' . $buffer);
            }
        });

        Log::info("Exit Code: " . $process->getExitCode());

        if (!$process->isSuccessful()) {
            Log::error('Helm install script failed', [
                'exit_code' => $process->getExitCode(),
                'error_output' => $errorBuffer,
            ]);
            throw new \Exception('Helm install failed.');
        }

        Log::info('Helm installed successfully.');
    }
}
