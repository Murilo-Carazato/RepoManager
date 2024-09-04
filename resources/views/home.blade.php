<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerSync - Git Pull Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .messages-container {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .repository {
            margin-bottom: 20px;
        }

        .repository hr {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">ServerSync</a>
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
</body>

</html>
