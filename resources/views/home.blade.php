<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerSync - Git Pull Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">ServerSync</a>
    </nav>

    <div class="container mt-5">
        <!-- Mensagens de Sucesso ou Erro -->
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <p>Caso o repositório não esteja visível, certifique-se de que ele esteja no caminho: "{{ $baseDir }}" e que contenha o arquivo `.git` e o Laravel.</p>

        <div class="card">
            <div class="card-header">
                Lista de Repositórios
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        @forelse ($repositories as $repo)
                            <tr>
                                <td>
                                    <h3>{{ $repo['name'] }}</h3>
                                    <p><strong>Caminho:</strong> {{ $repo['path'] }}</p>
                                    <p><strong>URL:</strong> <a href="{{ $repo['url'] }}" target="_blank">{{ $repo['url'] }}</a></p>
                                    <p><strong>Status do Servidor:</strong> {{ session("repo_status_{$repo['path']}", $repo['status']) }}</p>
                                </td>
                                <td>
                                    <form action="{{ route('git.pull') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                        <button type="submit" class="btn btn-primary">Git Pull</button>
                                    </form>

                                    <form action="{{ route('git.serve') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                        <button type="submit" class="btn btn-secondary">
                                            {{ session("repo_status_{$repo['path']}", $repo['status']) == 'Ligado' ? 'Desligar' : 'Ligar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr><td colspan="2"><hr></td></tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Nenhum repositório encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
