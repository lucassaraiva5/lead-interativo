<div>
    <form wire:submit.prevent="cadastrar">
        @csrf
        <div>
            <label for="name">Nome:</label>
            <input type="text" id="name" wire:model="name">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" wire:model="email">
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" wire:model="password">
            @error('password') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="photo">Foto de Perfil:</label>
            <input type="file" id="photo" wire:model="photo">
            @error('photo') <span class="error">{{ $message }}</span> @enderror

            @if ($photo)
                <div>
                    <p>Pré-visualização:</p>
                    <img src="{{ $photo->temporaryUrl() }}" alt="Pré-visualização da foto" width="100">
                </div>
            @endif
        </div>

        <button type="submit">Cadastrar</button>
    </form>

    @if (session()->has('message'))
        <div>{{ session('message') }}</div>
    @endif
</div>