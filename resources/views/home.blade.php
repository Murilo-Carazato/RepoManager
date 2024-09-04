<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerSync - Git Pull Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        body.dark-mode {
            background-color: #343a40;
            color: #ffffff;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand {
            font-weight: bold;
            color: #ffffff;
        }

        .dark-mode .navbar {
            background-color: #1c1e21;
        }

        .messages-container {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
        }

        .dark-mode .card-header {
            background-color: #0056b3;
        }

        .card-body {
            background-color: #ffffff;
            border: 1px solid #007bff;
            border-top: none;
            border-radius: 0 0 0.25rem 0.25rem;
        }

        .dark-mode .card-body {
            background-color: #1c1e21;
            border-color: #0056b3;
        }

        .repository h3 {
            font-size: 1.5rem;
            color: #343a40;
            margin-bottom: 10px;
        }

        .dark-mode .repository h3 {
            color: #ffffff;
        }

        .repository p {
            margin-bottom: 5px;
            color: #495057;
        }

        .dark-mode .repository p {
            color: #d3d3d3;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #343a40;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .alert {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">ServerSync</a>
        <button id="darkModeToggle" class="btn btn-outline-light ml-auto">Dark Mode</button>
    </nav>

    <div class="container mt-4">
        <!-- Mensagens de sucesso e erro -->
        <div class="messages-container">
            @if (session('success') && is_array(session('success')))
                @foreach (session('success') as $success)
                    <div class="alert alert-success">{{ $success }}</div>
                @endforeach
            @endif

            @if (session('error') && is_array(session('error')))
                @foreach (session('error') as $error)
                    <div class="alert alert-danger">{{ $error }}</div>
                @endforeach
            @endif
        </div>

        @if (is_array(session('success')) || is_array(session('error')))
            <form action="{{ route('git.clearMessages') }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-warning">Limpar Mensagens</button>
            </form>
        @endif

        <p>
            Caso o repositório não esteja visível, certifique-se de que ele esteja no caminho:
            "{{ $baseDir }}" e que contenha o arquivo `.git` e o Laravel.
        </p>

        <div class="card">
            <div class="card-header">Lista de Repositórios</div>
            <div class="card-body">
                @if (empty($repositories))
                    <p class="text-center">Nenhum repositório encontrado.</p>
                @else
                    @foreach ($repositories as $repo)
                        <div class="repository">
                            <h3>{{ $repo['name'] }}</h3>
                            <p><strong>Caminho:</strong> {{ $repo['path'] }}</p>
                            <p>
                                <strong>URL:</strong>
                                <a href="{{ $repo['url'] }}" target="_blank">{{ $repo['url'] }}</a>
                            </p>
                            <p>
                                <strong>Status do Servidor:</strong>
                                {{ session("repo_status_{$repo['path']}", $repo['status']) }}
                            </p>

                            <div class="d-flex justify-content-between">
                                <form action="{{ route('git.pull') }}" method="POST" class="mr-2">
                                    @csrf
                                    <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                    <button type="submit" class="btn btn-primary">Git Pull</button>
                                </form>

                                <form action="{{ route('git.serve') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                    <button type="submit" class="btn btn-secondary">
                                        {{ session("repo_status_{$repo['path']}", $repo['status']) == 'Ligado' ? 'Desligar' : 'Ligar' }}
                                    </button>
                                </form>
                            </div>
                            <hr>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('darkModeToggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
        });
    </script>
</body>

</html>
