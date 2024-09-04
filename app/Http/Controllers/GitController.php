<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitController extends Controller
{
    private array $repositories = [];
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = 'C:\Users\Murilo Carazato\Documents\Flutter Projects';
        $this->repositories = $this->getRepositories($this->baseDir);
    }

    public function index()
    {
        $this->updateRepositoryStatus();
        return view('home', ['repositories' => $this->repositories, 'baseDir' => $this->baseDir]);
    }

    public function pull(Request $request)
    {
        $repoPath = $request->input('repo_path');
        $output = [];
        $returnVar = null;

        exec("cd $repoPath && git pull 2>&1", $output, $returnVar);

        // $message = $returnVar === 0 ? 'Git pull executado com sucesso!' : 'Erro ao executar git pull: ' . implode("\n", $output);
        // return redirect()->back()->with($returnVar === 0 ? 'success' : 'error', $message);

        $message = $returnVar === 0 ? 'Git pull no repositório ' . basename($repoPath) . ' executado com sucesso!' : 'Erro ao executar git pull do repositório ' . basename($repoPath) . ': ' . implode("\n", $output);

        $messagesKey = $returnVar === 0 ? 'success' : 'error';
        session()->push($messagesKey, $message);

        return redirect()->back();
    }

    private function getRepositories(string $baseDir): array
    {
        return array_filter(array_map(function ($dir) {
            return $this->isValidRepository($dir) ? $this->createRepositoryData($dir) : null;
        }, glob($baseDir . '/*')), fn($repo) => $repo !== null);
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

    public function serve(Request $request)
    {
        $repoPath = $request->input('repo_path');
        $port = session("repo_port_{$repoPath}");

        if ($this->isServerRunning($repoPath)) {
            $this->stopServer($port);
            $this->clearSession($repoPath);
            // return redirect()->back()->with('success', 'Servidor parado com sucesso!');
            session()->push('success', 'Servidor ' . basename($repoPath) . " parado com sucesso!");
        } else {
            $port = $this->startServer($repoPath);
            session()->put("repo_status_{$repoPath}", 'Ligado');
            session()->put("repo_port_{$repoPath}", $port);
            // return redirect()->back()->with('success', "Servidor iniciado com sucesso na porta {$port}!");
            session()->push('success', 'Servidor ' . basename($repoPath) . " iniciado com sucesso na porta {$port}!");
        }
        return redirect()->back();
    }

    private function startServer(string $repoPath): int
    {
        $port = $this->getNextAvailablePort();
        $command = "cd {$repoPath} && php artisan serve --port={$port}";
        pclose(popen("start /B cmd /c \"$command\"", "r"));
        return $port;
    }

    private function stopServer(int $port)
    {
        $command = "FOR /F \"tokens=5\" %a in ('netstat -aon ^| findstr :{$port}') do taskkill /F /PID %a";
        exec($command);
    }

    private function isServerRunning(string $repoPath): bool
    {
        $port = session("repo_port_{$repoPath}");
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

    private function updateRepositoryStatus(): void
    {
        foreach ($this->repositories as &$repo) {
            $repo['status'] = $this->isServerRunning($repo['path']) ? 'Ligado' : 'Desligado';
        }
    }

    private function clearSession(string $repoPath): void
    {
        session()->forget("repo_status_{$repoPath}");
        session()->forget("repo_port_{$repoPath}");
    }

    public function clearMessages()
    {
        session()->forget('success');
        session()->forget('error');

        return redirect()->back()->with('success', 'Mensagens limpas com sucesso!');
    }
}
