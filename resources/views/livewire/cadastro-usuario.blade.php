<div class="max-w-md mx-auto bg-white shadow-md rounded-md p-6">
    <form wire:submit.prevent="cadastrar" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-gray-700 font-semibold mb-1">Nome:</label>
            <input type="text" id="name" wire:model="name" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-gray-700 font-semibold mb-1">E-mail:</label>
            <input type="email" id="email" wire:model="email" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-gray-700 font-semibold mb-1">Senha:</label>
            <input type="password" id="password" wire:model="password" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="photo" class="block text-gray-700 font-semibold mb-1">Foto de Perfil:</label>
            <input type="file" id="photo" wire:model="photo" class="w-full border border-gray-300 rounded-md p-2">
            @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            @if ($photo)
                <div class="mt-4">
                    <p class="text-gray-600">Pré-visualização:</p>
                    <img src="{{ $photo->temporaryUrl() }}" alt="Pré-visualização da foto" class="mt-2 rounded-md border border-gray-300 w-24">
                </div>
            @endif
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white font-semibold py-2 rounded-md hover:bg-blue-600">Cadastrar</button>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Overlay de carregamento -->
    <div wire:loading wire:target="cadastrar" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
        <div class="spinner border-4 border-blue-500 border-t-transparent border-solid rounded-full w-12 h-12 animate-spin"></div>
    </div>
    <style>
    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-top-color: #3490dc; /* Cor azul */
        border-radius: 50%;
        width: 48px;
        height: 48px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
</style>
</div>
