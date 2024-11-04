<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\AIServiceIntegration;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class CadastroUsuario extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $password;
    public $photo;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'photo' => 'nullable|image|max:5120', // Limite de 1MB para o arquivo de foto
    ];

    public function cadastrar()
    {
        $this->validate();

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('photos', 'public'); // Salva a foto na pasta `storage/app/public/photos`
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'photo' => $photoPath,
        ]);

        $imageLink = AIServiceIntegration::generateImage();
        

        session()->flash('message', 'Cadastro realizado com sucesso! Você pode iniciar o teste.');
        Auth::login($user);

        echo($imageLink);

        return redirect()->route('home'); // Redireciona para o teste
    }

    public function render()
    {
        return view('livewire.cadastro-usuario');
    }

    public static function runNodeScript()
    {
        $output = shell_exec('node ' . escapeshellarg(base_path('node-scripts/generateImage.js')) . ' 2>&1');
        $result = json_decode($output, true);

        return $result;
    }
}
