<div class="max-w-2xl mx-auto bg-gray-800 text-white p-6 rounded-lg shadow-lg">
    @if (!$resultado)
        <form wire:submit.prevent="proximaPergunta" class="space-y-6">
            <div>
                <p class="text-lg font-semibold mb-4">{{ $perguntas[$indiceAtual]->conteudo }}</p>
                <div class="space-y-2">
                    @foreach($perguntas[$indiceAtual]->responseOptions as $opcao)
                        <label class="block">
                            <input type="radio" wire:model="respostas.{{ $perguntas[$indiceAtual]->id }}" value="{{ $opcao->id }}" class="mr-2">
                            {{ $opcao->conteudo }}
                        </label>
                    @endforeach
                </div>
                @error('respostas.' . $perguntas[$indiceAtual]->id) 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">
                Próxima
            </button>
        </form>
    @else
        <div class="text-center">
            <h2 class="text-2xl font-bold mb-4">Seu resultado é: {{ $resultado->nome }}</h2>
            <p class="mb-6">{{ $resultado->descricao }}</p>
            <img src="{{ $avatar }}" alt="Imagem do resultado" class="mb-4 rounded-lg">

            <button onclick="compartilharImagem('{{ $avatar }}')" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">
                Compartilhar Imagem
            </button>
            <button wire:click="reiniciarTeste" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg">
                Refazer Teste
            </button>
        </div>
    @endif

    <script>
        function compartilharImagem(url) {
            if (navigator.share) {
                navigator.share({
                    title: 'Confira esta imagem!',
                    text: 'Veja esta imagem incrível!',
                    url: url
                })
                .then(() => console.log('Compartilhamento bem-sucedido'))
                .catch((error) => console.error('Erro ao compartilhar:', error));
            } else {
                alert('A API de compartilhamento da Web não é suportada neste navegador.');
            }
        }
    </script>
</div>
