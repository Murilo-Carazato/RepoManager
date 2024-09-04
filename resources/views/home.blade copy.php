<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerSync - Git Pull Interface</title>
    <!-- Link para o CSS do Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">ServerSync</a>
    </nav>

    <div class="container mt-5">
        <!-- Mensagens de Sucesso ou Erro -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <p>Caso o repositório não esteja visível, tenha certeza que o repositório esteja no caminho:
            "{{ $baseDir }}" (dado no gitController)</p>
        <p>E que contenha o arquivo .git e o laravel dentro dele </p>

        <div class="card">
            <div class="card-header">
                Lista de Repositórios
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        @foreach ($repositories as $repo)
                            <h3>{{ $repo['name'] }}</h3>
                            <p>Path: {{ $repo['path'] }}</p>
                            <p>URL: <a href="{{ $repo['url'] }}" target="_blank">{{ $repo['url'] }}</a></p>
                            <p>Status do Servidor:
                                <strong>
                                    {{ session("repo_status_{$repo['path']}", $repo['status']) }}
                                </strong>
                            </p>

                            <form action="{{ route('git.pull') }}" method="POST">
                                @csrf
                                <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                <button type="submit">Git Pull</button>
                            </form>

                            <form action="{{ route('git.serve') }}" method="POST">
                                @csrf
                                <input type="hidden" name="repo_path" value="{{ $repo['path'] }}">
                                <button
                                    type="submit">{{ session("repo_status_{$repo['path']}", $repo['status']) == 'Ligado' ? 'Desligar' : 'Ligar' }}</button>
                            </form>

                            <hr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
