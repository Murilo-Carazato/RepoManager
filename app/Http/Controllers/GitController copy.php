<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitController extends Controller
{
    private $repositories = [];
    private $baseDir;

    public function __construct()
    {
        // $this->baseDir = 'C:\Users\Joao\Documents\GitHub';
        $this->baseDir = 'C:\Users\Murilo Carazato\Documents\Flutter Projects';
        $this->repositories = $this->getRepositories($this->baseDir);
    }

    public function index()
    {
        foreach ($this->repositories as &$repo) {
            $repo['status'] = $this->isServerRunning($repo['path']) ? 'Ligado' : 'Desligado';
        }

        return view('home', ['repositories' => $this->repositories, 'baseDir' => $this->baseDir]);
    }

    public function pull(Request $request)
    {
        $repoPath = $request->input('repo_path');
        $output = null;
        $returnVar = null;

        exec("cd $repoPath && git pull 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            return redirect()->back()->with('error', 'Erro ao executar git pull: ' . implode("\n", $output));
        }

        return redirect()->back()->with('success', 'Git pull executado com sucesso!');
    }

    private function getRepositories($baseDir)
    {
        $repositories = [];

        $directories = array_filter(glob($baseDir . '/*'), 'is_dir');

        foreach ($directories as $dir) {
            // if (is_dir($dir . '/.git')) {
            if (is_dir($dir . '/.git') && file_exists($dir . '/artisan')) {
                $sshUrl = $this->getRemoteUrl($dir);
                $httpsUrl = $this->convertSshToHttps($sshUrl);

                $repositories[] = [
                    'name' => basename($dir),
                    'path' => $dir,
                    'url' => $httpsUrl,
                ];
            }
        }

        return $repositories;
    }

    private function getRemoteUrl($repoPath)
    {
        $output = null;
        $returnVar = null;

        exec("cd $repoPath && git config --get remote.origin.url", $output, $returnVar);

        return $output ? trim($output[0]) : 'No remote URL found';
    }

    private function convertSshToHttps($sshUrl)
    {
        if (preg_match('/git@github.com:(.*)\/(.*)\.git/', $sshUrl, $matches)) {
            $user = $matches[1];
            $repo = $matches[2];
            return "https://github.com/{$user}/{$repo}.git";
        }

        return $sshUrl;
    }

    public function serve(Request $request)
    {
        $repoPath = $request->input('repo_path');

        if ($this->isServerRunning($repoPath)) {
            $port = session("repo_port_{$repoPath}");
            $this->stopServer($port);
            session()->forget("repo_status_{$repoPath}");
            session()->forget("repo_port_{$repoPath}");
            return redirect()->back()->with('success', 'Servidor parado com sucesso!');
        } else {
            $port = $this->startServer($repoPath);
            session()->put("repo_status_{$repoPath}", "Ligado");
            session()->put("repo_port_{$repoPath}", $port);
            return redirect()->back()->with('success', "Servidor iniciado com sucesso na porta {$port}!");
        }
    }

    private function startServer($repoPath)
    {
        $port = $this->getNextAvailablePort();
        Log::info("Servidor iniciado para o repositório em {$repoPath} na porta {$port}");
        $command = "cd {$repoPath} && php artisan serve --port={$port}";
        $command = "start cmd /c \"$command\"";
        pclose(popen("start /B " . $command, "r"));
        return $port;
    }

    private function stopServer($port)
    {
        $shutdownCommand = "FOR /F \"tokens=5\" %a in ('netstat -aon ^| findstr :{$port}') do taskkill /F /PID %a";
        exec($shutdownCommand, $output, $resultCode);
    }

    private function isServerRunning($repoPath)
    {
        $port = session("repo_port_{$repoPath}");
        if (!$port) return false;

        $output = null;
        $returnVar = null;
        exec("netstat -ano | findstr :{$port}", $output, $returnVar);

        if (!empty($output)) {
            foreach ($output as $line) {
                if (strpos($line, 'LISTENING') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getNextAvailablePort($startPort = 8080, $endPort = 9000)
    {
        for ($port = $startPort; $port <= $endPort; $port++) {
            // Verifica se a porta está disponível
            if ($this->isPortAvailable($port)) {
                return $port;
            }
        }
        throw new \Exception('Nenhuma porta disponível encontrada entre ' . $startPort . ' e ' . $endPort);
    }

    private function isPortAvailable($port)
    {
        $output = null;
        $returnVar = null;
        // Comando para verificar se a porta está em uso (diferente entre Windows e Unix)
        exec("netstat -ano | findstr :{$port}", $output, $returnVar);

        // Se a saída estiver vazia, significa que a porta está disponível
        return empty($output);
    }
}
