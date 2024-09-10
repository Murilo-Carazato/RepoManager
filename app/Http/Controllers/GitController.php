<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GitController extends Controller
{
    private array $repositories;
    private string $baseDir;

    public function __construct()
    {
        // $this->baseDir = 'C:\Users\Murilo Carazato\Documents\Flutter Projects\assis-ofertas';
        $this->baseDir = 'C:\Users\Murilo Carazato\Documents\Flutter Projects';
        $this->repositories = $this->getRepositories($this->baseDir);
    }

    public function index()
    {
        $this->updateRepositoryStatuses();

        return view('home', [
            'repositories' => $this->repositories,
            'baseDir' => $this->baseDir
        ]);
    }

    public function pull(Request $request)
    {
        $repoPath = $request->input('repo_path');

        $output = $this->executeGitCommand($repoPath, 'git pull');
        $this->storeMessageBasedOnOutput($output, $repoPath, 'Git pull no repositório ' .  basename($repoPath) . ' executado com sucesso!', 'Erro ao executar git pull');

        return redirect()->back();
    }

    public function toggleAutoRun(Request $request)
    {
        $repoPath = $request->input('repo_path');
        $newStatus = $this->toggleStatus($repoPath);

        $this->addMessage('success', "O auto run foi {$newStatus} no servidor: " . basename($repoPath));

        Cache::forget('auto_run_started');

        return redirect()->back();
    }

    //o autorun liga o mesmo servidor 2 vezes; pois a página é recarregada; o state no blade puro é feito recarregando a página, e o autorun é acionado ao carregar a página.
    //clicar em ligar autoserver V
    //put teste 
    //clicar em desligar servidor V
    //teste
    //clicar em ligar servidor (dd)
    //forget teste
    //clicar em desligar servidor X (abre mais 1 cmd)
    //clicar em desligar servidor V

    //

    //clicar em ligar autoserver V
    //clicar em desligar servidor X (Abre o mesmo cmd dnv)



    public function autoRunStart()
    {
        foreach ($this->repositories as $repo) {
            // Cache::forget("repo_started_{$repo['path']}");

            $autoServerStatus = Cache::get("repo_auto_server_status_{$repo['path']}");
            $status = Cache::get("repo_status_{$repo['path']}", 'Desligado');
            $parar = Cache::get("repo_started_{$repo['path']}");

            // dd($status, $parar);

            if ($autoServerStatus === "Ligado" && $status === "Desligado" && $parar === null) {
                $port = $this->startServer($repo['path']);
                $this->storeServerStatus($repo['path'], $port, 'Ligado');
                Cache::put("repo_started_{$repo['path']}", "teste");
            }
        }
    }

    public function serve(Request $request)
    {
        $repoPath = $request->input('repo_path');
        $status = Cache::get("repo_status_{$repoPath}", 'Desligado');

        if ($status === 'Ligado') {
            //Cache::put("repo_manual_stop_{$repoPath}", true); 
            $this->stopServer($repoPath);
        } else {
            //Cache::forget("repo_manual_stop_{$repoPath}"); 
            $this->startServer($repoPath);
        }

        return redirect()->back();
    }

    private function startServer(string $repoPath): int
    {
        $port = $this->getNextAvailablePort();
        $command = "cd {$repoPath} && php artisan serve --port={$port}";
        pclose(popen("start cmd /c \"$command\"", "r"));

        $this->storeServerStatus($repoPath, $port, 'Ligado');
        return $port;
    }

    private function stopServer(string $repoPath)
    {
        $port = Cache::get("repo_port_{$repoPath}");

        if (!$port) {
            $this->addMessage('error', 'Nenhuma porta foi encontrada para parar o servidor do repositório ' . basename($repoPath));
            return;
        }
        Cache::put("repo_status_{$repoPath}", "Desligado");

        exec("FOR /F \"tokens=5\" %a in ('netstat -aon ^| findstr :{$port}') do taskkill /F /PID %a", $output, $result);

        if ($result === 0) {
            $this->clearSession($repoPath);
            $this->addMessage('success', 'Servidor ' . basename($repoPath) . ' parado com sucesso!');
        } else {
            $this->addMessage('error', 'Erro ao parar o servidor do repositório ' . basename($repoPath) . ': ' . implode("\n", $output));
        }
    }

    private function addMessage(string $type, string $message): void
    {
        $cacheKey = "messages_{$type}";
        $messages = Cache::get($cacheKey, []);
        $messages[] = $message;
        Cache::put($cacheKey, $messages);
    }


    private function storeServerStatus(string $repoPath, int $port, string $status): void
    {
        Cache::put("repo_status_{$repoPath}", $status);
        Cache::put("repo_port_{$repoPath}", $port);

        $this->addMessage('success', "Servidor " . basename($repoPath) . " {$status} na porta {$port}!");
    }

    private function executeGitCommand(string $repoPath, string $command): array
    {
        $output = [];
        exec("cd $repoPath && $command 2>&1", $output, $returnVar);
        return compact('output', 'returnVar');
    }

    private function storeMessageBasedOnOutput(array $commandOutput, string $repoPath, string $successMessage, string $errorMessage)
    {
        $messagesKey = $commandOutput['returnVar'] === 0 ? 'success' : 'error';
        $message = $commandOutput['returnVar'] === 0
            ? $successMessage
            : "{$errorMessage} do repositório " . basename($repoPath) . ': ' . implode("\n", $commandOutput['output']);

        $this->addMessage($messagesKey, $message);
    }

    private function toggleStatus(string $repoPath): string
    {
        $currentStatus = Cache::get("repo_auto_server_status_{$repoPath}", 'Desligado');
        $newStatus = $currentStatus === 'Ligado' ? 'Desligado' : 'Ligado';
        Cache::put("repo_auto_server_status_{$repoPath}", $newStatus);
        return $newStatus;
    }

    public function getRepositories(string $baseDir): array
    {
        return array_filter(array_map(fn($dir) => $this->isValidRepository($dir) ? $this->createRepositoryData($dir) : null, glob($baseDir . '/*')), fn($repo) => $repo !== null);
    }

    private function isValidRepository(string $dir): bool
    {
        return is_dir("$dir/.git") && file_exists("$dir/artisan");
    }

    private function createRepositoryData(string $dir): array
    {
        $sshUrl = $this->getRemoteUrl($dir);
        $httpsUrl = $this->convertSshToHttps($sshUrl);

        return [
            'name' => basename($dir),
            'path' => $dir,
            'url' => $httpsUrl,
            'status' => $this->isServerRunning($dir) ? 'Ligado' : 'Desligado',
        ];
    }

    private function getRemoteUrl(string $repoPath): string
    {
        exec("cd $repoPath && git config --get remote.origin.url", $output, $returnVar);
        return $output[0] ?? 'No remote URL found';
    }

    private function convertSshToHttps(string $sshUrl): string
    {
        if (preg_match('/git@github.com:(.*)\/(.*)\.git/', $sshUrl, $matches)) {
            return "https://github.com/{$matches[1]}/{$matches[2]}.git";
        }
        return $sshUrl;
    }

    private function isServerRunning(string $repoPath): bool
    {
        $port = Cache::get("repo_port_{$repoPath}");
        if (!$port) return false;

        exec("netstat -ano | findstr :{$port}", $output);
        return collect($output)->contains(fn($line) => strpos($line, 'LISTENING') !== false);
    }

    private function getNextAvailablePort(int $startPort = 8080, int $endPort = 9000): int
    {
        foreach (range($startPort, $endPort) as $port) {
            if ($this->isPortAvailable($port)) return $port;
        }
        throw new \Exception("Nenhuma porta disponível encontrada entre {$startPort} e {$endPort}");
    }

    private function isPortAvailable(int $port): bool
    {
        exec("netstat -ano | findstr :{$port}", $output);
        return empty($output);
    }

    private function updateRepositoryStatuses(): void
    {
        foreach ($this->repositories as &$repo) {
            $repo['status'] = $this->isServerRunning($repo['path']) ? 'Ligado' : 'Desligado';
        }
    }

    private function clearSession(string $repoPath = null): void
    {
        if ($repoPath) {
            Cache::forget("repo_status_{$repoPath}");
            Cache::forget("repo_port_{$repoPath}");
            Cache::forget("repo_started_{$repoPath}");
        } else {
            Cache::forget('messages_success');
            Cache::forget('messages_error');
        }
    }

    public function clearMessages()
    {
        $this->clearSession();
        return redirect()->back();
    }
}
