<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Meu Aplicativo' }}</title>
    @livewireStyles
    <!-- Adicione outros estilos e links CSS aqui -->
</head>
<body>
    <header>
        <!-- Cabeçalho da página -->
    </header>

    <main>
    

        @guest
            @livewire('cadastro-usuario')
        @else
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Logout</button>
            </form>

            @livewire('teste-vocacional')
        @endguest
    </main>

    <footer>
        <!-- Rodapé da página -->
    </footer>

    @livewireScripts
    <!-- Adicione outros scripts aqui -->
</body>
</html>