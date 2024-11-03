<div>
    @if (!$resultado)
        <form wire:submit.prevent="proximaPergunta">
            <div>
                <p>{{ $perguntas[$indiceAtual]->conteudo }}</p>
                @foreach($perguntas[$indiceAtual]->responseOptions as $opcao)
                    <label>
                        <input type="radio" wire:model="respostas.{{ $perguntas[$indiceAtual]->id }}" value="{{ $opcao->id }}">
                        {{ $opcao->conteudo }}
                    </label><br>
                @endforeach
                @error('respostas.' . $perguntas[$indiceAtual]->id) <span class="error">{{ $message }}</span> @enderror
            </div>
            <button type="submit">Próxima</button>
        </form>
    @else
        <h2>Seu resultado é: {{ $resultado->nome }}</h2>
        <p>{{ $resultado->descricao }}</p>
        <button wire:click="reiniciarTeste">Refazer Teste</button>
    @endif
</div>